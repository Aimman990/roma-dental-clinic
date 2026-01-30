<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\SalarySheet;
use App\Models\SalaryPayment;

class DatabaseSeeder extends Seeder
{
    // public function run(): void
    // {
    //     // Create users (admin + doctors + staff)
    //     // core users with known passwords for demo: password
    //     // Admin: no monthly salary by default; receptionist has fixed monthly salary
    //     User::factory()->create(['name' => 'Aiman', 'email' => 'aiman@gmail.com', 'role' => 'admin', 'password' => bcrypt('password'), 'monthly_salary' => 0]);
    //     User::factory()->create(['name' => 'Receptionist', 'email' => 'reception@example.test', 'role' => 'user', 'password' => bcrypt('password'), 'monthly_salary' => 1500]);
    //     // doctors with known password
    //     // Doctors are paid by commission — assign random commission% for demo
    //     $doctors = User::factory(3)->create(['role'=>'user','commission_pct' => 30])->each(function($u){ $u->password = bcrypt('password'); $u->monthly_salary = 0; $u->save(); });
    //     // Staff have a fixed monthly salary
    //     $staff = User::factory(2)->create(['role'=>'user'])->each(function($u){ $u->password = bcrypt('password'); $u->monthly_salary = rand(800,2500); $u->save(); });

    //     // Patients
    //     $patients = Patient::factory(20)->create();

    //     // Services — some linked to doctors
    //     $services = collect();
    //     foreach ($doctors as $doctor) {
    //         $services = $services->merge(Service::factory(4)->create(['doctor_id'=>$doctor->id]));
    //     }

    //     // Appointments and medical records
    //     Appointment::factory(30)->create();
    //     // Create invoices/payments
    //     Invoice::factory(15)->create()->each(function($invoice){
    //         InvoiceItem::factory(1)->create(['invoice_id'=>$invoice->id]);
    //         // partial/random payments
    //         if (rand(0,2) > 0) {
    //             Payment::factory()->create(['invoice_id'=>$invoice->id, 'patient_id'=>$invoice->patient_id]);
    //         }
    //     });

    //     // Expenses
    //     Expense::factory(10)->create();

    //     // Salaries and payments
    //     $sheet = SalarySheet::factory()->create(['period' => now()->format('Y-m')]);
    //     foreach (User::factory(1)->create(['role'=>'user']) as $u) {
    //         SalaryPayment::factory()->create(['salary_sheet_id' => $sheet->id, 'user_id' => $u->id]);
    //     }

    // }
public function run(): void
{
    // 1. إضافة أو تحديث المسؤول (Admin)
    User::updateOrCreate(
        ['email' => 'aiman@gmail.com'], // شرط البحث
        [
            'name' => 'Aiman',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'monthly_salary' => 0,
            'commission_pct' => 0,
        ]
    );

    // 2. إضافة أو تحديث موظف الاستقبال
    User::updateOrCreate(
        ['email' => 'reception@example.test'],
        [
            'name' => 'Receptionist',
            'role' => 'user',
            'password' => bcrypt('password'),
            'monthly_salary' => 1500,
            'commission_pct' => 0,
        ]
    );

    // 3. إضافة الأطباء والخدمات (بشكل بسيط)
    for ($i = 1; $i <= 2; $i++) {
        $doctor = User::updateOrCreate(
            ['email' => "doctor$i@example.com"],
            [
                'name' => "Doctor $i",
                'role' => 'user',
                'password' => bcrypt('password'),
                'commission_pct' => 30,
                'monthly_salary' => 0,
            ]
        );

        // إضافة خدمات للطبيب إذا لم يكن لديه خدمات
        if ($doctor->wasRecentlyCreated) {
            Service::factory(2)->create(['doctor_id' => $doctor->id]);
        }
    }

    // 4. إضافة بيانات عشوائية فقط إذا كانت الجداول فارغة
    if (Patient::count() == 0) {
        Patient::factory(10)->create();
        Appointment::factory(10)->create();
    }
}

}
