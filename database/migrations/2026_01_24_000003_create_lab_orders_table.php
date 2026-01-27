<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('lab_name'); // e.g. "Al-Amal Lab"
            $table->string('work_type'); // e.g. "Zircon Crown", "Denture"
            $table->text('details')->nullable(); // description, shade, instructions
            $table->date('sent_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('received_date')->nullable();
            $table->string('status')->default('sent'); // sent, received, delivered
            $table->decimal('cost', 10, 2)->default(0); // cost to the clinic
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_orders');
    }
};
