<?php

namespace Tests\Feature;

use App\Models\BackupRestoreRun;
use App\Models\BackupRun;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RestoreSafetyTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_restore_of_full_backup_is_rejected_until_file_restore_is_supported(): void
    {
        Queue::fake();
        $property = Property::factory()->create();
        $user = User::factory()->create([
            'property_id' => $property->id,
            'role' => 'hotel_owner',
            'is_active' => true,
        ]);
        $backup = BackupRun::create([
            'property_id' => $property->id,
            'type' => 'full',
            'status' => 'successful',
            'disk' => 'local',
            'path' => 'hotel-backups/full.tar.gz',
        ]);

        $this->actingAs($user)
            ->from(route('admin.restore'))
            ->post(route('admin.restore.store'), [
                'backup_run_id' => $backup->id,
                'confirmation' => 'RESTORE',
                'dry_run' => '0',
            ])
            ->assertRedirect(route('admin.restore'))
            ->assertSessionHasErrors('backup_run_id');

        $this->assertSame(0, BackupRestoreRun::count());
        Queue::assertNothingPushed();
    }
}
