<?php

namespace App\Services;

use App\Exceptions\GoogleDriveException;
use App\Models\User;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    private GoogleClient $client;

    public function __construct()
    {
        $this->client = new GoogleClient();

        $httpClientOptions = [
            'connect_timeout' => 15,
            'timeout' => 180,
        ];

        // Bypass SSL di environment lokal (Windows) untuk mencegah cURL error 60
        if (app()->environment('local')) {
            $httpClientOptions['verify'] = false;
        }

        $this->client->setHttpClient(new \GuzzleHttp\Client($httpClientOptions));

        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri'));
        $this->client->setScopes(config('google.scopes'));
        $this->client->setAccessType('offline');
    }

    /**
     * Inisialisasi client dengan token user tertentu
     */
    public function forUser(User $user): static
    {
        $refreshToken = $user->google_refresh_token;

        if (!$refreshToken) {
            throw new \Exception("User {$user->id} tidak memiliki refresh token.");
        }

        // Set refresh token dan minta access token baru
        $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

        // Jika refresh token di-rotate oleh Google, simpan yang baru
        $newRefreshToken = $this->client->getRefreshToken();
        if ($newRefreshToken && $newRefreshToken !== $refreshToken) {
            $user->update(['google_refresh_token' => $newRefreshToken]);
            Log::info("Refresh token diperbarui untuk user {$user->id}");
        }

        return $this;
    }

    private function isInsufficientScopeException(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return $e instanceof \Google\Service\Exception
            && $e->getCode() === 403
            && (
                str_contains($message, 'ACCESS_TOKEN_SCOPE_INSUFFICIENT')
                || str_contains($message, 'insufficientPermissions')
                || str_contains($message, 'insufficient authentication scopes')
            );
    }

    private function rethrowWithScopeMessage(\Throwable $e): void
    {
        if ($this->isInsufficientScopeException($e)) {
            throw new \Exception('Izin Google Drive belum lengkap. Silakan logout lalu login ulang dan setujui akses Google Drive.', 403, $e);
        }

        throw $e;
    }

    /**
     * Buat folder utama aplikasi di Drive user
     * Struktur: [AppName] > Photos + Backups
     */
    public function createAppFolders(User $user): string
    {
        $this->forUser($user);
        $drive = new GoogleDrive($this->client);

        try {
            // Buat folder utama
            $mainFolder = $this->createFolder(
                $drive,
                config('google.drive.folder_name'),
                'root'
            );

            // Buat subfolder
            $this->createFolder($drive, config('google.drive.photos_subfolder'), $mainFolder);
            $this->createFolder($drive, config('google.drive.backups_subfolder'), $mainFolder);
        } catch (\Throwable $e) {
            $this->rethrowWithScopeMessage($e);
        }

        // Simpan ID folder utama ke user
        $user->update(['google_drive_folder_id' => $mainFolder]);

        return $mainFolder;
    }

    /**
     * Upload file ke Google Drive milik user
     * Kembalikan array berisi drive_file_id dan web_view_link
     */
    public function uploadFile(User $user, string $localPath, string $fileName, string $mimeType, string $type): array
    {
        $this->forUser($user);
        $drive = new GoogleDrive($this->client);

        // Tentukan folder tujuan
        $parentFolderId = $this->getOrCreateSubfolder($drive, $user, $type);

        $fileMetadata = new DriveFile([
            'name'    => $fileName,
            'parents' => [$parentFolderId],
        ]);

        $content = file_get_contents($localPath);

        try {
            $uploadedFile = $drive->files->create($fileMetadata, [
                'data'       => $content,
                'mimeType'   => $mimeType,
                'uploadType' => 'multipart',
                'fields'     => 'id,name,webViewLink,size',
            ]);
        } catch (\Throwable $e) {
            $this->rethrowWithScopeMessage($e);
        }

        return [
            'drive_file_id'    => $uploadedFile->getId(),
            'drive_folder_id'  => $parentFolderId,
            'web_view_link'    => $uploadedFile->getWebViewLink(),
        ];
    }

    /**
     * Ambil URL download temporary (1 jam) — digunakan oleh superadmin
     * Mengembalikan stream content file
     */
    public function getFileStream(User $owner, string $driveFileId)
    {
        $this->forUser($owner);
        $drive = new GoogleDrive($this->client);

        try {
            $response = $drive->files->get($driveFileId, ['alt' => 'media']);
            return $response->getBody();
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 404) {
                throw new \Exception("File tidak ditemukan di Drive: {$driveFileId}");
            }
            throw new \Exception("Gagal mengakses file: " . $e->getMessage());
        }
    }

    /**
     * Hapus file dari Drive user
     */
    public function deleteFile(User $user, string $driveFileId): bool
    {
        $this->forUser($user);
        $drive = new GoogleDrive($this->client);

        try {
            $drive->files->delete($driveFileId);
            return true;
        } catch (\Google\Service\Exception $e) {
            if ($e->getCode() === 404) {
                Log::info("File {$driveFileId} sudah tidak ada di Drive user {$user->id}");
                return true;
            }

            Log::error("Gagal hapus file {$driveFileId} dari Drive user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifikasi apakah file masih ada di Drive
     */
    public function fileExists(User $user, string $driveFileId): bool
    {
        try {
            $this->forUser($user);
            $drive = new GoogleDrive($this->client);
            $drive->files->get($driveFileId, ['fields' => 'id']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function createFolder(GoogleDrive $drive, string $name, string $parentId): string
    {
        $meta = new DriveFile([
            'name'     => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents'  => [$parentId],
        ]);
        $folder = $drive->files->create($meta, ['fields' => 'id']);
        return $folder->getId();
    }

    private function getOrCreateSubfolder(GoogleDrive $drive, User $user, string $type): string
    {
        $mainFolderId = $user->google_drive_folder_id;
        if (!$mainFolderId) {
            $mainFolderId = $this->createAppFolders($user);
        }

        $subfolderName = $type === 'photo'
            ? config('google.drive.photos_subfolder')
            : config('google.drive.backups_subfolder');

        // Cari subfolder yang sudah ada
        $this->forUser($user);
        $query = "name='{$subfolderName}' and '{$mainFolderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false";
        $results = $drive->files->listFiles(['q' => $query, 'fields' => 'files(id)']);

        if (count($results->getFiles()) > 0) {
            return $results->getFiles()[0]->getId();
        }

        // Buat jika belum ada
        return $this->createFolder($drive, $subfolderName, $mainFolderId);
    }
}
