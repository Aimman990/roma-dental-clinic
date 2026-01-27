<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_sheet_id')->constrained('salary_sheets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0); // for doctors
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('total_paid', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_payments');
    }
};
