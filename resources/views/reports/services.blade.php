@extends('layouts.app')

@section('content')
<div x-data="servicesReport()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">تقارير الخدمات</h2>
            <p class="text-sm text-slate-500">إيرادات وعدد مرات احتساب كل خدمة.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" x-model="from" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <span class="text-gray-400 font-bold">-</span>
            <input type="date" x-model="to" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <input x-model="q" @input.debounce.500ms="fetch" placeholder="بحث باسم الخدمة" class="px-3 py-2 border rounded w-56">
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
            <div class="font-bold">قائمة الخدمات</div>
                <div class="flex gap-2">
                <button @click="exportCSV" class="px-3 py-1 bg-green-600 text-white rounded">تصدير CSV/XLSX</button>
                <button @click="print" class="px-3 py-1 bg-gray-800 text-white rounded">طباعة</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th class="p-3">الخدمة</th>
                        <th class="p-3">السعر</th>
                        <th class="p-3">مرات احتساب</th>
                        <th class="p-3">إجمالي الإيرادات</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="s in services" :key="s.id">
                        <tr>
                            <td class="p-3 font-medium" x-text="s.name"></td>
                            <td class="p-3 font-mono" x-text="formatMoney(s.price)"></td>
                            <td class="p-3 font-mono" x-text="s.invoices_count"></td>
                            <td class="p-3 font-mono" x-text="formatMoney(s.total_revenue)"></td>
                            <td class="p-3">
                                <a :href="`/services/${s.id}`" class="px-2 py-1 border rounded text-xs text-indigo-600">تفاصيل</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="services.length === 0">
                        <td colspan="5" class="p-6 text-center text-slate-400">لا توجد بيانات</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="p-3 flex items-center justify-between border-t bg-slate-50">
            <div class="text-sm text-slate-600">إجمالي: <span x-text="meta.total ?? 0"></span></div>
            <div class="flex items-center gap-2">
                <button @click="prevPage" :disabled="meta.page <= 1" class="px-2 py-1 border rounded">السابق</button>
                <div class="px-2">صفحة <span x-text="meta.page ?? 1"></span> / <span x-text="totalPages"></span></div>
                <button @click="nextPage" :disabled="meta.page >= totalPages" class="px-2 py-1 border rounded">التالي</button>
            </div>
        </div>
    </div>
</div>

<script>
function servicesReport(){
    return {
        from: '{{ now()->startOfMonth()->format('Y-m-d') }}',
        to: '{{ now()->format('Y-m-d') }}',
        q: '',
        services: [],
        page: 1,
        per_page: 20,
        meta: {},

        async init(){ this.fetch(); },

        async fetch(page = null){
            const params = new URLSearchParams();
            if(this.from) params.append('from', this.from);
            if(this.to) params.append('to', this.to);
            if(this.q) params.append('q', this.q);
            params.append('per_page', this.per_page || 20);
            params.append('page', page || this.page || 1);
            const res = await fetch('/api/reports/services?'+params.toString(), {credentials: 'same-origin'});
            const data = await res.json();
            this.services = data.data || [];
            this.meta = data.meta || {};
        },

        formatMoney(v){ return (Number(v)||0).toFixed(2) + ' ر.ي'; },

        exportCSV(){
            const params = new URLSearchParams();
            if(this.from) params.append('from', this.from);
            if(this.to) params.append('to', this.to);
            if(this.q) params.append('q', this.q);
            window.location = '/api/reports/services/export?'+params.toString();
        },

        print(){ window.print(); },

        prevPage(){ if((this.meta.page || this.page) <= 1) return; this.fetch((this.meta.page || this.page) - 1); },
        nextPage(){ const total = Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); if((this.meta.page || this.page) >= total) return; this.fetch((this.meta.page || this.page) + 1); },
        get totalPages(){ return Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); }
    }
}
</script>
@endsection