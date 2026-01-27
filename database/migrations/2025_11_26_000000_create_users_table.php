<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Be defensive: this migration may run on top of an earlier migration
        // that already created the `users` table. If the table exists, only
        // add the `role` column when it is missing to avoid duplicate-create
        // errors when running migrate:fresh or running multiple migration files.
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // add the role column only if it doesn't already exist
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('staff'); // admin, doctor, receptionist, staff
                }
            });
            return;
        }

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role')->default('staff'); // admin, doctor, receptionist, staff
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // If the users table has the `role` column added by this migration,
        // remove the column. If the table doesn't have the `role` column and
        // the table exists, drop the table.
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            return;
        }

        Schema::dropIfExists('users');
    }
};
