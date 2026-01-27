@extends('layouts.app')

@section('content')
<div x-data="expensesReport()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">تقارير المصروفات</h2>
            <p class="text-sm text-slate-500">عرض إجمالي المصروفات وتفصيلها حسب الفئة.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" x-model="from" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <span class="text-gray-400 font-bold">-</span>
            <input type="date" x-model="to" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <button @click="fetch" class="bg-indigo-600 text-white px-4 py-1 rounded">تحديث</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border overflow-hidden p-4">
        <div class="mb-4">
            <div class="font-bold">إجمالي المصروفات: <span x-text="formatMoney(total)"></span></div>
        </div>

        <div>
            <div class="font-bold mb-2">تفصيل المصروفات</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="p-2">الفئة</th>
                            <th class="p-2">المجموع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="row in breakdown" :key="row.category">
                            <tr>
                                <td class="p-2" x-text="row.category"></td>
                                <td class="p-2" x-text="formatMoney(row.total)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script>
function expensesReport(){
    return {
        from: '{{ now()->startOfMonth()->format('Y-m-d') }}',
        to: '{{ now()->format('Y-m-d') }}',
        total: 0,
        breakdown: [],

        async init(){ this.fetch(); },

        async fetch(){
            const params = new URLSearchParams();
            if(this.from) params.append('from', this.from);
            if(this.to) params.append('to', this.to);
            const res = await fetch('/api/reports/expenses?'+params.toString(), {credentials: 'same-origin'});
            const data = await res.json();
            this.total = data.total_expenses || 0;
            this.breakdown = data.breakdown || [];
        },

        formatMoney(v){ return (Number(v)||0).toFixed(2) + ' ر.ي'; }
    }
}
</script>
@endsection