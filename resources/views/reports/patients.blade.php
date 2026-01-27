@extends('layouts.app')

@section('content')
<div x-data="patientsReport()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">تقارير المرضى</h2>
            <p class="text-sm text-slate-500">عرض ملخص المرضى، الإيرادات حسب المريض مع إمكانية التصفية والتصدير.</p>
        </div>
        <div class="flex items-center gap-2">
            <input type="date" x-model="from" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <span class="text-gray-400 font-bold">-</span>
            <input type="date" x-model="to" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <input x-model="q" @input.debounce.500ms="fetch" placeholder="بحث باسم المريض" class="px-3 py-2 border rounded w-56">
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
            <div class="font-bold">قائمة المرضى</div>
            <div class="flex gap-2">
                <button @click="exportCSV" class="px-3 py-1 bg-green-600 text-white rounded">تصدير CSV</button>
                <button @click="print" class="px-3 py-1 bg-gray-800 text-white rounded">طباعة</button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-50 text-slate-600 font-medium">
                    <tr>
                        <th class="p-3">المريض</th>
                        <th class="p-3">إجمالي المدفوع</th>
                        <th class="p-3">الفواتير</th>
                        <th class="p-3">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="p in patients" :key="p.id">
                        <tr>
                            <td class="p-3 font-medium" x-text="p.name"></td>
                            <td class="p-3 font-mono" x-text="formatMoney(p.total_paid)"></td>
                            <td class="p-3 font-mono" x-text="p.invoices_count || '-'"></td>
                            <td class="p-3">
                                <a :href="`/patients/${p.id}`" class="px-2 py-1 border rounded text-xs text-indigo-600">تفاصيل</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="patients.length === 0">
                        <td colspan="4" class="p-6 text-center text-slate-400">لا توجد بيانات</td>
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
function patientsReport(){
    return {
        from: '{{ now()->startOfMonth()->format('Y-m-d') }}',
        to: '{{ now()->format('Y-m-d') }}',
        q: '',
        patients: [],
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
            const res = await fetch('/api/reports/ops/patients?'+params.toString(), {credentials: 'same-origin'});
            const data = await res.json();
            // OperationalReportsController::getPatientsReport returns either 'general' or a set; normalize
            if(Array.isArray(data)){
                this.patients = data;
                this.meta = { total: data.length, page:1, per_page: data.length };
            } else if(data.top_patients){
                this.patients = data.top_patients.map(p=>({id:p.id, name: p.first_name + ' ' + (p.last_name||''), total_paid: p.total_paid}));
                this.meta = { total: this.patients.length, page:1, per_page: this.patients.length };
            } else if(data.data){
                this.patients = data.data;
                this.meta = data.meta || { total: this.patients.length, page:1, per_page:this.per_page };
            } else {
                this.patients = [];
                this.meta = {};
            }
        },

        formatMoney(v){ return (Number(v)||0).toFixed(2) + ' ر.ي'; },

        exportCSV(){
            let csv = ['"المريض","إجمالي المدفوع","الفواتير"'];
            this.patients.forEach(p=>{
                csv.push(`"${p.name}","${p.total_paid || 0}","${p.invoices_count || ''}"`);
            });
            let blob = new Blob(["\uFEFF" + csv.join('\n')], {type:'text/csv'});
            let a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = 'patients_report.csv'; a.click();
        },

        print(){ window.print(); },

        prevPage(){ if((this.meta.page || this.page) <= 1) return; this.fetch((this.meta.page || this.page) - 1); },
        nextPage(){ const total = Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); if((this.meta.page || this.page) >= total) return; this.fetch((this.meta.page || this.page) + 1); },
        get totalPages(){ return Math.max(1, Math.ceil((this.meta.total || 0) / (this.meta.per_page || this.per_page || 20))); }
    }
}
</script>
@endsection
