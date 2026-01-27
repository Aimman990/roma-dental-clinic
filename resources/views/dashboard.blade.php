@extends('layouts.app')

@section('header', 'لوحة التحكم')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Stat Card 1: Patients -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500">إجمالي المرضى</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ \App\Models\Patient::count() ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-green-600 gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            <span>نشط الآن</span>
        </div>
    </div>

    <!-- Stat Card 2: Appointments Today - clickable to view details -->
    <div x-data="todayAppts()" class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start cursor-pointer" @click="openModal()">
            <div>
                <p class="text-sm font-medium text-slate-500">مواعيد اليوم</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ \App\Models\Appointment::whereDate('start_at', today())->count() ?? 0 }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-slate-400 gap-1">
            <span>مجدولة لليوم</span>
        </div>

        <!-- Modal -->
        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-black/40" @click="close()"></div>
            <div class="relative bg-white rounded-2xl max-w-3xl w-full shadow-lg p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-bold text-lg">مواعيد اليوم</h4>
                    <button @click="close()" class="text-slate-500">إغلاق</button>
                </div>

                <div x-show="loading" class="py-8 text-center text-slate-500">جاري التحميل...</div>

                <div x-show="!loading">
                    <div x-show="appointments.length === 0" class="p-6 text-center text-slate-400">لا توجد مواعيد لليوم</div>
                    <div class="overflow-x-auto" x-show="appointments.length > 0">
                        <table class="w-full text-sm text-right">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="p-2">الوقت</th>
                                    <th class="p-2">المريض</th>
                                    <th class="p-2">الخدمة</th>
                                    <th class="p-2">الحالة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <template x-for="a in appointments" :key="a.id">
                                    <tr>
                                        <td class="p-2" x-text="a.start_at"></td>
                                        <td class="p-2" x-text="a.patient_name"></td>
                                        <td class="p-2" x-text="a.service_name"></td>
                                        <td class="p-2" x-text="a.status"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Card 3: Lab Orders (New) -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500">طلبات المعمل</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-2">0</h3> <!-- Placeholder until model linked -->
            </div>
            <div class="p-3 bg-orange-50 rounded-xl text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-orange-600 gap-1">
            <span>قيد الانتظار</span>
        </div>
    </div>

    <!-- Stat Card 4: Revenue -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-sm font-medium text-slate-500">دخل الشهر</p>
                <h3 class="text-3xl font-bold text-slate-800 mt-2">SR 0</h3>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm text-emerald-600 gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            <span>+12% عن الشهر الماضي</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Quick Actions -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">وصول سريع</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('patients.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 transition-colors group cursor-pointer">
                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                </div>
                <span class="text-sm font-medium">مريض جديد</span>
            </a>
            
            <a href="{{ route('appointments.index') }}" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 transition-colors group cursor-pointer">
                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <span class="text-sm font-medium">حجز موعد</span>
            </a>

            <a href="#" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 transition-colors group cursor-pointer">
                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <span class="text-sm font-medium">فاتورة جديدة</span>
            </a>

            <a href="#" class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 transition-colors group cursor-pointer">
                <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <span class="text-sm font-medium">طلب معمل</span>
            </a>
        </div>
    </div>

    <!-- Notification or Calendar placeholder -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">التحديثات الأخيرة</h3>
        <div class="space-y-4">
            <!-- Item -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">تم إضافة مريض جديد</p>
                    <p class="text-xs text-slate-400">عبدالله محمد - منذ 10 دقائق</p>
                </div>
            </div>
            <!-- Item -->
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-800">اكتمال موعد</p>
                    <p class="text-xs text-slate-400">سارة أحمد - منذ 30 دقيقة</p>
                </div>
            </div>
            
            <div class="pt-4 mt-2 border-t border-slate-50 text-center">
                <a href="#" class="text-indigo-600 text-sm font-medium hover:underline">عرض كل النشاطات</a>
            </div>
        </div>
    </div>
</div>
<script>
    function todayAppts() {
        return {
            open: false,
            loading: false,
            appointments: [],

            openModal() {
                this.open = true;
                this.fetchAppointments();
            },

            close() {
                this.open = false;
                this.appointments = [];
            },

            async fetchAppointments() {
                this.loading = true;
                try {
                    const serverDate = '{{ today()->toDateString() }}';
                    const res = await fetch('/api/appointments?date=' + serverDate, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                    if (!res.ok) {
                        const txt = await res.text();
                        try { Swal.fire('خطأ', 'تعذر جلب مواعيد اليوم: ' + (res.statusText || res.status), 'error'); } catch(e){ /* ignore Swal missing */ }
                        this.appointments = [];
                        this.loading = false;
                        return;
                    }
                    const data = await res.json();
                    this.appointments = (Array.isArray(data) ? data : data.data || []).map(function (a) {
                        return {
                            id: a.id,
                            start_at: a.start_at || a.appointment_date || a.start,
                            patient_name: a.patient_name || (a.patient ? (a.patient.first_name + ' ' + (a.patient.last_name || '')) : ''),
                            service_name: a.service_name || (a.service ? a.service.name : ''),
                            status: a.status
                        };
                    });
                } catch (e) {
                    console.error(e);
                    try { Swal.fire('خطأ', 'خطأ في الاتصال عند جلب مواعيد اليوم', 'error'); } catch (ee) { alert('خطأ في جلب المواعيد'); }
                    this.appointments = [];
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>

@endsection
