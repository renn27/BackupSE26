<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\User;
use App\Services\GoogleDriveService;
use App\Services\UserDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class AdminUserDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_permanently_deletes_user_and_owned_data(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $user = User::create([
            'name' => 'Petugas',
            'email' => 'petugas@example.test',
            'role' => 'petugas',
            'status' => 'active',
        ]);

        $activeFile = File::create([
            'user_id' => $user->id,
            'drive_file_id' => 'drive-active',
            'drive_folder_id' => 'folder-1',
            'original_name' => 'foto.jpg',
            'stored_name' => 'foto.jpg',
            'type' => 'photo',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 123,
            'status' => 'uploaded',
        ]);

        $trashedFile = File::create([
            'user_id' => $user->id,
            'drive_file_id' => '',
            'drive_folder_id' => '',
            'original_name' => 'gagal.zip',
            'stored_name' => 'gagal.zip',
            'type' => 'backup',
            'mime_type' => 'application/zip',
            'size_bytes' => 456,
            'status' => 'failed',
        ]);
        $trashedFile->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'upload_photo',
            'subject_type' => File::class,
            'subject_id' => $activeFile->id,
        ]);

        DB::table('sessions')->insert([
            'id' => 'session-for-user',
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $villageId = DB::table('villages')->insertGetId([
            'kdkec' => 1,
            'nmkec' => 'Kecamatan',
            'kddesa' => 1,
            'nmdesa' => 'Desa',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_village_assignments')->insert([
            'user_id' => $user->id,
            'village_id' => $villageId,
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $driveService = Mockery::mock(GoogleDriveService::class);
        $driveService
            ->shouldReceive('deleteFile')
            ->once()
            ->with(Mockery::on(fn (User $owner) => $owner->id === $user->id), 'drive-active')
            ->andReturnTrue();

        (new UserDeletionService($driveService))->deletePermanently($user);

        $this->assertNull(User::withTrashed()->find($user->id));
        $this->assertNull(File::withTrashed()->find($activeFile->id));
        $this->assertNull(File::withTrashed()->find($trashedFile->id));
        $this->assertDatabaseMissing('activity_logs', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('user_village_assignments', ['user_id' => $user->id]);
    }
}
