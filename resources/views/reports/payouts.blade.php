@extends('layouts.app')

@section('header', 'المستحقات المالية والسحوبات')

@section('content')
    <div x-data="payoutsApp()" class="space-y-6">
        <!-- Filters -->
        <div class="bg-white p-4 rounded-lg shadow border border-slate-200 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
                <input type="date" x-model="filters.from" class="px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
                <input type="date" x-model="filters.to" class="px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الطبيب</label>
                <select x-model="filters.doctor_id" class="px-3 py-2 border rounded-lg min-w-[200px]">
                    <option value="">كل الأطباء</option>
                    <template x-for="doc in doctors" :key="doc.id">
                        <option :value="doc.id" x-text="doc.name"></option>
                    </template>
                </select>
            </div>
            <button @click="fetchData"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow">
                عرض التقرير
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl shadow border-r-4 border-indigo-500">
                <h3 class="text-sm font-bold text-gray-500 mb-2">إجمالي المستحقات (العمولات)</h3>
                <p class="text-3xl font-extrabold text-indigo-700" x-text="formatMoney(summary.total_commission)"></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-r-4 border-red-500">
                <h3 class="text-sm font-bold text-gray-500 mb-2">إجمالي المسحوبات</h3>
                <p class="text-3xl font-extrabold text-red-700" x-text="formatMoney(summary.total_withdrawals)"></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-r-4 border-green-500">
                <h3 class="text-sm font-bold text-gray-500 mb-2">صافي المبلغ المتبقي</h3>
                <p class="text-3xl font-extrabold text-green-700" x-text="formatMoney(summary.net_balance)"></p>
            </div>
        </div>

        <!-- Details Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">تفاصيل الحركات المالية</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-medium">
                        <tr>
                            <th class="p-4">التاريخ</th>
                            <th class="p-4">الطبيب</th>
                            <th class="p-4">نوع الحركة</th>
                            <th class="p-4">التفاصيل</th>
                            <th class="p-4">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="row in reportData" :key="row.id + row.type">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-4" x-text="row.date"></td>
                                <td class="p-4 font-bold text-gray-800" x-text="row.doctor_name"></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold"
                                        :class="row.type === 'commission' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                        x-text="row.type === 'commission' ? 'استحقاق عمولة' : 'سحب نقدي'">
                                    </span>
                                </td>
                                <td class="p-4 text-gray-600" x-text="row.details"></td>
                                <td class="p-4 font-bold"
                                    :class="row.type === 'commission' ? 'text-green-600' : 'text-red-600'"
                                    x-text="(row.type==='withdrawal' ? '-' : '+') + formatMoney(row.amount)"></td>
                            </tr>
                        </template>
                        <tr x-show="reportData.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">لا توجد بيانات للعرض</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('payoutsApp', () => ({
                filters: {
                    from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                    to: new Date().toISOString().split('T')[0],
                    doctor_id: ''
                },
                doctors: [],
                reportData: [],
                summary: { total_commission: 0, total_withdrawals: 0, net_balance: 0 },

                async init() {
                    await this.fetchDoctors();
                    this.fetchData();
                },

                async fetchDoctors() {
                    try {
                        const res = await fetch('/api/users?role=doctor'); // We need to ensure API supports filtering or filter client side
                        // Assuming /api/users returns all, we filter client side if needed or backend supports it
                        // Actually standard /api/users might return paginated. Let's use specific ops/doctors endpoint or similar if avail
                        // Or just verify /api/users response.
                        const data = await res.json();
                        let allUsers = data.data || data;
                        this.doctors = allUsers.filter(u => u.role === 'doctor');
                    } catch (e) { console.error(e); }
                },

                async fetchData() {
                    try {
                        const params = new URLSearchParams(this.filters);
                        params.append('mode', 'financial'); // Request detailed financial data
                        const res = await fetch(`/api/reports/ops/doctors?${params.toString()}`);
                        const data = await res.json();

                        this.reportData = data.details || [];
                        this.summary = data.summary || { total_commission: 0, total_withdrawals: 0, net_balance: 0 };

                    } catch (e) {
                        console.error('Report fetch error', e);
                        Swal.fire('خطأ', 'تعذر تحميل التقرير', 'error');
                    }
                },

                formatMoney(val) {
                    return Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ر.ع';
                }
            }));
        });
    </script>
@endsection