<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role field - defaults to 'viewer' for new users
            $table->string('role', 50)->default('viewer')->after('is_admin');

            // JSON fields for row-level access control
            $table->json('allowed_employees')->nullable()->after('role');
            $table->json('allowed_external_refs')->nullable()->after('allowed_employees');

            // Permission flags
            $table->boolean('can_add_comments')->default(true)->after('allowed_external_refs');
            $table->boolean('can_sync')->default(false)->after('can_add_comments');
            $table->boolean('can_send_reminders')->default(false)->after('can_sync');

            // Active status - for blocking/unblocking users
            $table->boolean('is_active')->default(true)->after('can_send_reminders');
        });

        // Migrate existing users: set admins to 'admin' role, non-admins to 'manager' role
        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
        DB::table('users')->where('is_admin', false)->orWhereNull('is_admin')->update(['role' => 'manager']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'allowed_employees',
                'allowed_external_refs',
                'can_add_comments',
                'can_sync',
                'can_send_reminders',
                'is_active',
            ]);
        });
    }
};
