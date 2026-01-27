<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'remaining')) {
                $table->decimal('remaining', 12, 2)->default(0)->after('total');
            }
            if (! Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('remaining');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('invoices', 'remaining')) {
                $table->dropColumn('remaining');
            }
        });
    }
};
