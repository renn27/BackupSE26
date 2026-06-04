<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserDeletionService
{
    public function __construct(
        private GoogleDriveService $driveService,
    ) {}

    public function deletePermanently(User $user): void
    {
        $files = $user->files()
            ->withTrashed()
            ->select(['id', 'user_id', 'drive_file_id'])
            ->get();

        $this->deleteDriveFiles($user, $files);

        DB::transaction(function () use ($user, $files) {
            $fileIds = $files->pluck('id')->all();

            if ($fileIds !== []) {
                DB::table('activity_logs')
                    ->where(function ($query) use ($fileIds) {
                        $query->where('subject_type', File::class)
                            ->whereIn('subject_id', $fileIds);
                    })
                    ->delete();

                $user->files()->withTrashed()->forceDelete();
            }

            DB::table('activity_logs')->where('user_id', $user->id)->delete();
            DB::table('sessions')->where('user_id', $user->id)->delete();
            DB::table('user_village_assignments')
                ->where('user_id', $user->id)
                ->orWhere('assigned_by', $user->id)
                ->delete();
            DB::table('excel_upload_logs')->where('uploaded_by', $user->id)->delete();

            $user->forceDelete();
        });
    }

    private function deleteDriveFiles(User $user, $files): void
    {
        foreach ($files as $file) {
            if (! filled($file->drive_file_id)) {
                continue;
            }

            $deleted = $this->driveService->deleteFile($user, $file->drive_file_id);

            if (! $deleted) {
                throw new RuntimeException("Gagal menghapus file Drive {$file->drive_file_id} milik {$user->email}.");
            }
        }
    }
}
