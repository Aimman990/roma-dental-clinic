@extends('layouts.app')

@section('content')
<div x-data="appointmentsReport()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold">تقارير المواعيد</h2>
            <p class="text-sm text-slate-500">تحليل حالة المواعيد وعرض أحدث المواعيد.</p>
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
            <div class="font-bold">حالة المواعيد</div>
            <div class="grid grid-cols-4 gap-4 mt-2">
                <template x-for="s in breakdown" :key="s.status">
                    <div class="p-3 bg-slate-50 rounded text-center">
                        <div class="text-sm text-slate-600" x-text="s.status"></div>
                        <div class="font-bold text-lg" x-text="s.total"></div>
                    </div>
                </template>
            </div>
        </div>

        <div>
            <div class="font-bold mb-2">أحدث المواعيد</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="p-2">تاريخ</th>
                            <th class="p-2">الطبيب</th>
                            <th class="p-2">المريض</th>
                            <th class="p-2">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-for="a in recent" :key="a.id">
                            <tr>
                                <td class="p-2" x-text="a.start_at"></td>
                                <td class="p-2" x-text="a.doctor_name"></td>
                                <td class="p-2" x-text="a.patient_name"></td>
                                <td class="p-2" x-text="a.status"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function appointmentsReport(){
    return {
        from: '{{ now()->startOfMonth()->format('Y-m-d') }}',
        to: '{{ now()->format('Y-m-d') }}',
        breakdown: [],
        recent: [],

        async init(){ this.fetch(); },

        async fetch(){
            const params = new URLSearchParams();
            if(this.from) params.append('from', this.from);
            if(this.to) params.append('to', this.to);
            const res = await fetch('/api/reports/ops/operations?'+params.toString(), {credentials: 'same-origin'});
            const data = await res.json();
            this.breakdown = (data.appointments_breakdown || []).map(x=>({status:x.status, total:x.total}));

            const r = await fetch('/api/appointments?per_page=20', {credentials: 'same-origin'});
            const rd = await r.json();
            this.recent = (rd.data || rd).map(a=>({id:a.id, start_at:a.start_at||a.appointment_date, doctor_name: a.doctor_name || (a.doctor? a.doctor.name : ''), patient_name: a.patient_name || (a.patient? (a.patient.first_name+' '+(a.patient.last_name||'')) : ''), status:a.status}));
        },

        print(){ window.print(); }
    }
}
</script>
@endsection