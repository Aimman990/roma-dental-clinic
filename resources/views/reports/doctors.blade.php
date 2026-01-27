@extends('layouts.app')

@section('content')
    <div x-data="doctorsReport()" class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">تقارير الأطباء</h2>
                <p class="text-sm text-slate-500">عرض إحصاءات ومخرجات لكل طبيب مع إمكانية التصفية والتصدير.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="date" x-model="from" class="border rounded px-2 py-1 text-sm bg-slate-50">
                <span class="text-gray-400 font-bold">-</span>
                <input type="date" x-model="to" class="border rounded px-2 py-1 text-sm bg-slate-50">
                <input x-model="q" @input.debounce.500ms="fetch" placeholder="بحث باسم الطبيب"
                    class="px-3 py-2 border rounded w-56">
                <select x-model.number="per_page" @change="fetch" class="border rounded px-2 py-1 text-sm">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
                <button @click="fetch" class="bg-indigo-600 text-white px-4 py-1 rounded">بحث</button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center">
                <div class="font-bold">قائمة الأطباء</div>
                <div class="flex gap-2">
                    <button @click="exportXlsx" class="px-3 py-1 bg-green-600 text-white rounded">تصدير (XLSX/CSV)</button>
                    <button @click="print" class="px-3 py-1 bg-gray-800 text-white rounded">طباعة</button>
                </div>
            </div>
            <div class="p-3 flex items-center justify-between border-t bg-slate-50">
                <div class="text-sm text-slate-600">إجمالي: <span x-text="meta.total ?? 0"></span></div>
                <div class="flex items-center gap-2">
                    <button @click="prevPage" :disabled="meta.page <= 1" class="px-2 py-1 border rounded">السابق</button>
                    <div class="px-2">صفحة <span x-text="meta.page ?? 1"></span> / <span x-text="totalPages"></span></div>
                    <button @click="nextPage" :disabled="meta.page >= totalPages"
                        class="px-2 py-1 border rounded">التالي</button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-slate-50 text-slate-600 font-medium">
                        <tr>
                            <th class="p-3">الطبيب</th>
                            <th class="p-3">مواعيد</th>
                            <th class="p-3">فواتير</th>
                            <th class="p-3">إجمالي فواتير</th>
                            <th class="p-3">المحصل</th>
                            <th class="p-3">العمولة المكتسبة</th>
                            <th class="p-3">المسحوبات</th>
                            <th class="p-3">الرصيد</th>
                            <th class="p-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="d in doctors" :key="d.id">
                            <tr>
                                <td class="p-3 font-medium" x-text="d.name"></td>
                                <td class="p-3 font-mono" x-text="d.appointments_count"></td>
                                <td class="p-3 font-mono" x-text="d.invoices_count"></td>
                                <td class="p-3 font-mono" x-text="formatMoney(d.total_invoiced)"></td>
                                <td class="p-3 font-mono" x-text="formatMoney(d.total_collected)"></td>
                                <td class="p-3 font-mono text-green-600" x-text="formatMoney(d.commission_earned)"></td>
                                <td class="p-3 font-mono text-red-600" x-text="formatMoney(d.total_withdrawn)"></td>
                                <td class="p-3 font-mono" x-text="formatMoney(d.balance)"></td>
                                <td class="p-3">
                                    <a :href="`/reports/doctor/${d.id}`"
                                        class="px-2 py-1 border rounded text-xs text-indigo-600">تفاصيل</a>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="doctors.length === 0">
                            <td colspan="9" class="p-6 text-center text-slate-400">لا توجد بيانات</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function doctorsReport() {
            return {
                from: '{{ now()->startOfYear()->format('Y-m-d') }}',
                to: '{{ now()->addMonth()->endOfMonth()->format('Y-m-d') }}',
                q: '',
                doctors: [],
                page: 1,
                per_page: 20,
                meta: {},
                loading: false,
                error: null,

                async init() { this.fetch(); },

                async fetch(page = null) {
                    this.loading = true; this.error = null;
                    try {
                        const params = new URLSearchParams();
                        if (this.from) params.append('from', this.from);
                        if (this.to) params.append('to', this.to);
                        if (this.q) params.append('q', this.q);
                        params.append('per_page', this.per_page || 20);
                        params.append('page', page || this.page || 1);
                        const res = await fetch('/api/reports/doctors?' + params.toString(), { credentials: 'same-origin' });
                        if (!res.ok) throw new Error('فشل استدعاء البيانات');
                        const data = await res.json();
                        this.doctors = data.data || [];
                        this.meta = data.meta || {};
                        this.page = this.meta.page || 1;
                    } catch (e) {
                        console.error(e); this.error = e.message || 'خطأ غير معروف';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('خطأ', this.error, 'error');
                        }
                    } finally {
                        this.loading = false;
                    }
                },

                formatMoney(v) { return (Number(v) || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ر.ي'; },

                exportXlsx() {
                    const params = new URLSearchParams();
                    if (this.from) params.append('from', this.from);
                    if (this.to) params.append('to', this.to);
                    if (this.q) params.append('q', this.q);
                    window.location = '/api/reports/doctors/export?' + params.toString();
                },

                viewDoctor(id) { window.location = '/reports/doctor/' + id; },

                prevPage() { if ((this.meta.page || this.page) <= 1) return; this.fetch((this.meta.page || this.page) - 1); },
                nextPage() { const total = Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); if ((this.meta.page || this.page) >= total) return; this.fetch((this.meta.page || this.page) + 1); },
                get totalPages() { return Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); },

                print() {
                    var tableContent = document.querySelector('.overflow-x-auto').outerHTML;
                    var printWindow = window.open('', '_blank', 'width=900,height=700');
                    printWindow.document.write('<html dir="rtl"><head><title>تقارير الأطباء</title>');
                    printWindow.document.write('<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">');
                    printWindow.document.write('<style>');
                    printWindow.document.write('body { font-family: Cairo, sans-serif; direction: rtl; padding: 20px; background: white; }');
                    printWindow.document.write('table { width: 100%; border-collapse: collapse; text-align: right; }');
                    printWindow.document.write('th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }');
                    printWindow.document.write('th { background-color: #f8fafc; font-weight: 600; }');
                    printWindow.document.write('h2 { margin-bottom: 15px; font-size: 18px; }');
                    printWindow.document.write('.print-date { font-size: 11px; color: #666; margin-bottom: 10px; }');
                    printWindow.document.write('</style></head><body>');
                    printWindow.document.write('<h2>تقارير الأطباء</h2>');
                    printWindow.document.write('<div class="print-date">تاريخ الطباعة: ' + new Date().toLocaleString('ar-SA') + '</div>');
                    printWindow.document.write('<div class="print-date">الفترة: ' + this.from + ' إلى ' + this.to + '</div>');
                    printWindow.document.write(tableContent);
                    printWindow.document.write('<script>setTimeout(function(){ window.print(); window.close(); }, 300);<\/script>');
                    printWindow.document.write('</body></html>');
                    printWindow.document.close();
                }
            }
        }
    </script>
@endsection