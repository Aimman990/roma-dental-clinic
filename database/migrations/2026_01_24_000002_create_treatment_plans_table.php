<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('total_estimated_cost', 10, 2)->default(0);
            $table->string('status')->default('proposed'); // proposed, accepted, rejected, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('treatment_plan_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('treatment_plan_id')->constrained('treatment_plans')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete(); // optional link to services catalog
            $table->string('tooth_number')->nullable();
            $table->string('procedure_name');
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->integer('session_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plan_procedures');
        Schema::dropIfExists('treatment_plans');
    }
};
