<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'monthly_salary')) {
                $table->decimal('monthly_salary', 12, 2)->default(0)->after('commission_pct');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'monthly_salary')) {
                $table->dropColumn('monthly_salary');
            }
        });
    }
};
