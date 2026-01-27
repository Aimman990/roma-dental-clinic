@extends('layouts.app')

@section('content')
<div x-data="inventoryReport()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">تقارير المخزون</h2>
            <p class="text-sm text-slate-500">قيمة المخزون، العناصر منخفضة المخزون، وتفاصيل كل عنصر.</p>
        </div>
        <div class="flex items-center gap-2">
            <input x-model="q" @input.debounce.400ms="fetch" placeholder="بحث باسم الصنف" class="px-3 py-2 border rounded w-56">
            <select x-model.number="per_page" @change="fetch" class="border rounded px-2 py-1 text-sm">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button @click="fetch" class="bg-indigo-600 text-white px-4 py-1 rounded">بحث</button>
        </div>
    </div>
    <div x-show="!disabled" class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <div class="font-bold">معلومات المخزون</div>
            <div class="flex gap-2">
                <button @click="exportCSV" class="px-3 py-1 bg-green-600 text-white rounded">تصدير CSV/XLSX</button>
                <button @click="print" class="px-3 py-1 bg-gray-800 text-white rounded">طباعة</button>
            </div>
        </div>
        <div class="p-4 border-b">
            <div class="flex gap-4">
                <div>إجمالي بنود: <strong x-text="summary.total_items || 0"></strong></div>
                <div>قيمة المخزون: <strong x-text="formatMoney(summary.total_value)"></strong></div>
                <div>عناصر منخفضة المخزون: <strong x-text="summary.low_stock_count || 0"></strong></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th class="p-3">الصنف</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">المخزون</th>
                        <th class="p-3">تكلفة الوحدة</th>
                        <th class="p-3">القيمة</th>
                        <th class="p-3">حالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="it in items" :key="it.id">
                        <tr>
                            <td class="p-3 font-medium" x-text="it.name"></td>
                            <td class="p-3 font-mono" x-text="it.sku"></td>
                            <td class="p-3 font-mono" x-text="it.current_stock + ' ' + it.unit"></td>
                            <td class="p-3 font-mono" x-text="formatMoney(it.cost_per_unit)"></td>
                            <td class="p-3 font-mono" x-text="formatMoney(it.value)"></td>
                            <td class="p-3" x-text="it.is_low ? 'منخفض' : 'مناسب'"></td>
                        </tr>
                    </template>
                    <tr x-show="items.length === 0">
                        <td colspan="6" class="p-6 text-center text-slate-400">لا توجد بيانات</td>
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
    <div x-show="disabled" class="bg-white rounded-xl shadow-sm border p-6 text-center text-slate-500">
        ميزة المخزون معطلة حالياً من قبل المدير. لا يمكن عرض بيانات المخزون.
    </div>
</div>

<script>
function inventoryReport(){
    return {
        q: '',
        items: [],
        page:1,
        per_page:20,
        meta:{},
        summary: {},
        disabled: false,

        async init(){ this.fetch(); },

        async fetch(page=null){
            const params = new URLSearchParams();
            if(this.q) params.append('q', this.q);
            params.append('per_page', this.per_page || 20);
            params.append('page', page || this.page || 1);
            const res = await fetch('/api/reports/inventory?'+params.toString(), {credentials: 'same-origin'});
            const data = await res.json();
            this.items = data.data || [];
            this.meta = data.meta || {};
            this.summary = data.summary || {};
            this.disabled = !!(this.summary && this.summary.disabled);
        },

        formatMoney(v){ return (Number(v)||0).toFixed(2) + ' ر.ي'; },
        exportCSV(){ const params = new URLSearchParams(); if(this.q) params.append('q', this.q); window.location = '/api/reports/inventory/export?'+params.toString(); },
        print(){ window.print(); },
        prevPage(){ if((this.meta.page||this.page) <= 1) return; this.fetch((this.meta.page||this.page)-1); },
        nextPage(){ const total = Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); if((this.meta.page||this.page) >= total) return; this.fetch((this.meta.page||this.page)+1); },
        get totalPages(){ return Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); }
    }
}
</script>
@endsection