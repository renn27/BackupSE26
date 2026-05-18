<?php
namespace App\Jobs;

use App\Models\User;
use App\Services\GoogleDriveService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateUserDriveFolder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        private User $user
    ) {}

    public function handle(GoogleDriveService $driveService): void
    {
        try {
            $driveService->createAppFolders($this->user);
            Log::info("Drive folders created for user {$this->user->id}");
        } catch (\Exception $e) {
            Log::error("Failed to create Drive folders for user {$this->user->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
