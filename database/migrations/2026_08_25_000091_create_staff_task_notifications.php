<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('task_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('task_type');
            $table->unsignedBigInteger('task_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('event_key', 160);
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['task_type','task_id','user_id','event_key'], 'task_notify_unique');
            $table->index(['user_id','sent_at']);
        });

        foreach (['maintenance_requests','guest_service_requests','room_service_requests','guest_complaints'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'due_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dateTime('due_at')->nullable()->after('assigned_to');
                    $table->index(['assigned_to','due_at']);
                });
            }
        }

        if (Schema::hasTable('laundry_orders') && ! Schema::hasColumn('laundry_orders', 'assigned_to')) {
            Schema::table('laundry_orders', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('received_by')->constrained('users')->nullOnDelete();
                $table->index(['assigned_to','due_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('laundry_orders') && Schema::hasColumn('laundry_orders', 'assigned_to')) {
            Schema::table('laundry_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('assigned_to');
            });
        }
        foreach (['maintenance_requests','guest_service_requests','room_service_requests','guest_complaints'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'due_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropColumn('due_at'));
            }
        }
        Schema::dropIfExists('task_notification_logs');
    }
};
