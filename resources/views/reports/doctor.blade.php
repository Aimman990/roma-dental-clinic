@extends('layouts.app')

@section('content')
    <div x-data="doctorDetail({{ $doctor_id }})" x-init="init()" class="space-y-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="history.back()"
                    class="flex items-center gap-2 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg border transition-colors">
                    <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>رجوع</span>
                </button>
                <div>
                    <h2 class="text-2xl font-bold">تفاصيل الطبيب</h2>
                    <p class="text-sm text-slate-500">عرض بيانات الطبيب، المواعيد، الفواتير، والمسحوبات.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 print:hidden">
                <button @click="exportSummary" class="px-3 py-1 bg-green-600 text-white rounded">تصدير ملخص CSV</button>
                <button @click="printReport" class="px-3 py-1 bg-gray-800 text-white rounded">طباعة</button>
            </div>
        </div>

        <div id="printable-content" class="bg-white rounded-lg shadow p-4">
            <div class="flex gap-4 border-b mb-4">
                <button :class="tab === 'overview' ? 'border-b-2 border-indigo-600' : ''" @click="tab='overview'"
                    class="px-3 py-2">ملخص</button>
                <button :class="tab === 'appointments' ? 'border-b-2 border-indigo-600' : ''" @click="tab='appointments'"
                    class="px-3 py-2">مواعيد</button>
                <button :class="tab === 'invoices' ? 'border-b-2 border-indigo-600' : ''" @click="tab='invoices'"
                    class="px-3 py-2">فواتير</button>
                <button :class="tab === 'withdrawals' ? 'border-b-2 border-indigo-600' : ''" @click="tab='withdrawals'"
                    class="px-3 py-2">مسحوبات</button>
            </div>

            <div x-show="tab === 'overview'">
                <div class="grid grid-cols-4 gap-4">
                    <div class="p-4 bg-slate-50 rounded">
                        <div class="text-sm text-slate-600">إجمالي مفوتر</div>
                        <div class="font-bold text-lg" x-text="formatMoney(summary.total_invoiced)"></div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded">
                        <div class="text-sm text-slate-600">المحصّل</div>
                        <div class="font-bold text-lg" x-text="formatMoney(summary.total_collected)"></div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded">
                        <div class="text-sm text-slate-600">العمولة المكتسبة</div>
                        <div class="font-bold text-lg text-green-600" x-text="formatMoney(summary.commission_earned)"></div>
                    </div>
                    <div class="p-4 bg-slate-50 rounded">
                        <div class="text-sm text-slate-600">المسحوبات</div>
                        <div class="font-bold text-lg text-red-600" x-text="formatMoney(summary.total_withdrawn)"></div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'appointments'">
                <div x-show="appointments.length === 0" class="p-6 text-center text-slate-400">لا توجد مواعيد</div>
                <div class="overflow-x-auto" x-show="appointments.length > 0">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="p-2">تاريخ</th>
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

            <div x-show="tab === 'invoices'">
                <div x-show="invoices.length === 0" class="p-6 text-center text-slate-400">لا توجد فواتير</div>
                <div class="overflow-x-auto" x-show="invoices.length > 0">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="p-2">رقم الفاتورة</th>
                                <th class="p-2">المريض</th>
                                <th class="p-2">الإجمالي</th>
                                <th class="p-2">المدفوع</th>
                                <th class="p-2">المتبقي</th>
                                <th class="p-2">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="inv in invoices" :key="inv.id">
                                <tr>
                                    <td class="p-2" x-text="inv.invoice_number"></td>
                                    <td class="p-2" x-text="inv.patient_name"></td>
                                    <td class="p-2" x-text="formatMoney(inv.total)"></td>
                                    <td class="p-2 text-green-600" x-text="formatMoney(inv.paid)"></td>
                                    <td class="p-2 text-red-600" x-text="formatMoney(inv.remaining)"></td>
                                    <td class="p-2" x-text="inv.status"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="tab === 'withdrawals'">
                <div x-show="withdrawals.length === 0" class="p-6 text-center text-slate-400">لا توجد مسحوبات</div>
                <div class="overflow-x-auto" x-show="withdrawals.length > 0">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="p-2">تاريخ</th>
                                <th class="p-2">المبلغ</th>
                                <th class="p-2">ملاحظة</th>
                                <th class="p-2">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="w in withdrawals" :key="w.id">
                                <tr>
                                    <td class="p-2" x-text="w.created_at"></td>
                                    <td class="p-2" x-text="formatMoney(w.amount)"></td>
                                    <td class="p-2" x-text="w.note || w.notes || '-' "></td>
                                    <td class="p-2">
                                        <button @click="openEdit(w)" class="px-2 py-1 border rounded text-xs">تعديل</button>
                                        <button @click="deleteWithdraw(w)"
                                            class="px-2 py-1 border rounded text-xs text-red-600">حذف</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
        function doctorDetail(id) {
            return {
                doctor_id: id,
                tab: 'overview',
                summary: {},
                appointments: [],
                invoices: [],
                withdrawals: [],

                init() {
                    this.fetchSummary();
                    this.fetchAppointments();
                    this.fetchInvoices();
                    this.fetchWithdrawals();
                },

                async fetchSummary() {
                    try {
                        const res = await fetch('/api/reports/doctor/' + this.doctor_id, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        if (!res.ok) {
                            const text = await res.text();
                            Swal.fire('خطأ', 'تعذر تحميل ملخص الطبيب: ' + (res.statusText || res.status), 'error');
                            this.summary = {};
                            return;
                        }
                        const data = await res.json();
                        this.summary = data.summary || {};
                    } catch (e) {
                        console.error(e);
                        Swal.fire('خطأ', 'خطأ في الاتصال عند جلب ملخص الطبيب', 'error');
                        this.summary = {};
                    }
                },

                async fetchAppointments() {
                    try {
                        const res = await fetch('/api/appointments?doctor_id=' + this.doctor_id, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
                        if (!res.ok) {
                            Swal.fire('خطأ', 'تعذر جلب المواعيد: ' + (res.statusText || res.status), 'error');
                            this.appointments = [];
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
                        Swal.fire('خطأ', 'خطأ في الاتصال عند جلب المواعيد', 'error');
                        this.appointments = [];
                    }
                },

                async fetchInvoices() {
                    try {
                        const res = await fetch('/api/invoices?doctor_id=' + this.doctor_id, {
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) {
                            Swal.fire('خطأ', 'تعذر جلب الفواتير: ' + (res.statusText || res.status), 'error');
                            this.invoices = [];
                            return;
                        }
                        const data = await res.json();
                        this.invoices = (Array.isArray(data) ? data : data.data || []).map(function (i) {
                            var paymentsSum = 0;
                            if (i.payments && Array.isArray(i.payments)) {
                                paymentsSum = i.payments.reduce(function (sum, p) { return sum + parseFloat(p.amount || 0); }, 0);
                            }
                            var remaining = parseFloat(i.total || 0) - paymentsSum;
                            return {
                                id: i.id,
                                invoice_number: i.invoice_number,
                                patient_name: i.patient_name || (i.patient ? (i.patient.first_name + ' ' + (i.patient.last_name || '')) : ''),
                                total: i.total,
                                paid: paymentsSum,
                                remaining: remaining > 0 ? remaining : 0,
                                status: i.status
                            };
                        });
                    } catch (e) {
                        console.error(e);
                        Swal.fire('خطأ', 'خطأ في الاتصال عند جلب الفواتير', 'error');
                        this.invoices = [];
                    }
                },

                async fetchWithdrawals() {
                    try {
                        const res = await fetch('/api/salaries/withdrawals?user_id=' + this.doctor_id, { credentials: 'same-origin' });
                        if (!res.ok) {
                            Swal.fire('خطأ', 'تعذر جلب المسحوبات', 'error');
                            this.withdrawals = [];
                            return;
                        }
                        const data = await res.json();
                        this.withdrawals = data.data || data || [];
                    } catch (e) {
                        console.error(e);
                        Swal.fire('خطأ', 'خطأ في الاتصال عند جلب المسحوبات', 'error');
                        this.withdrawals = [];
                    }
                },

                async openEdit(w) {
                    const result = await Swal.fire({
                        title: 'تعديل السحب',
                        html:
                            '<input id="swal-amount" class="swal2-input" placeholder="المبلغ" value="' + w.amount + '">' +
                            '<input id="swal-note" class="swal2-input" placeholder="ملاحظة" value="' + (w.note || w.notes || '') + '">',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'حفظ',
                        preConfirm: function () {
                            var amount = parseFloat(document.getElementById('swal-amount').value || 0);
                            var note = document.getElementById('swal-note').value || '';
                            if (!amount || amount <= 0) { Swal.showValidationMessage('أدخل مبلغ صالح'); return false; }
                            return { amount: amount, note: note };
                        }
                    });
                    if (!result.value) return;
                    var formValues = result.value;
                    try {
                        const res = await fetch('/api/salaries/withdrawals/' + w.id, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            credentials: 'same-origin',
                            body: JSON.stringify({ amount: formValues.amount, note: formValues.note })
                        });
                        if (!res.ok) throw new Error('فشل التحديث');
                        Swal.fire({ icon: 'success', title: 'تم', text: 'تم تحديث السحب', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                        this.fetchWithdrawals();
                        this.fetchSummary();
                    } catch (e) {
                        console.error(e);
                        Swal.fire('خطأ', 'تعذر تحديث السحب', 'error');
                    }
                },

                async deleteWithdraw(w) {
                    const ok = await Swal.fire({ title: 'تأكيد', text: 'هل تريد حذف هذا المسحوب؟', icon: 'warning', showCancelButton: true, confirmButtonText: 'نعم', cancelButtonText: 'إلغاء' });
                    if (!ok.isConfirmed) return;
                    try {
                        const res = await fetch('/api/salaries/withdrawals/' + w.id, {
                            method: 'DELETE',
                            credentials: 'same-origin',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                        });
                        if (!res.ok) throw new Error('فشل الحذف');
                        Swal.fire({ icon: 'success', title: 'تم', text: 'تم حذف السحب', toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                        this.fetchWithdrawals();
                        this.fetchSummary();
                    } catch (e) {
                        console.error(e);
                        Swal.fire('خطأ', 'تعذر حذف السحب', 'error');
                    }
                },

                exportSummary() {
                    window.location = '/api/reports/doctor/' + this.doctor_id + '/export';
                },

                printReport() {
                    var printContent = document.getElementById('printable-content');
                    if (!printContent) {
                        Swal.fire('خطأ', 'لا يوجد محتوى للطباعة', 'error');
                        return;
                    }
                    var content = printContent.innerHTML;
                    var printWindow = window.open('', '_blank', 'width=800,height=900');
                    printWindow.document.write('<html dir="rtl"><head><title>طباعة تفاصيل الطبيب</title>');
                    printWindow.document.write('<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">');
                    printWindow.document.write('<script src="https://cdn.tailwindcss.com"><\/script>');
                    printWindow.document.write('<style>body { font-family: Cairo, sans-serif; direction: rtl; padding: 20px; } .print-hidden { display: none !important; }</style>');
                    printWindow.document.write('</head><body class="bg-white">');
                    printWindow.document.write('<div class="space-y-6">' + content + '</div>');
                    printWindow.document.write('<script>setTimeout(function(){ window.print(); window.close(); }, 500);<\/script>');
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                },

                formatMoney(v) {
                    return Number(v || 0).toFixed(2) + ' ر.ي';
                }
            }
        }
    </script>
@endsection