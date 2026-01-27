<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'doctor_id')) {
                $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete()->after('appointment_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'doctor_id')) {
                $table->dropConstrainedForeignId('doctor_id');
            }
        });
    }
};
