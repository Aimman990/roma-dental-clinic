<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('reference');
            }
            if (! Schema::hasColumn('payments', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('receipt_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
            if (Schema::hasColumn('payments', 'receipt_number')) {
                $table->dropColumn('receipt_number');
            }
        });
    }
};
