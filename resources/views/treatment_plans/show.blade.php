@extends('layouts.app')

@section('header', 'تفاصيل خطة العلاج: #' . $treatmentPlan->id)

@section('content')
<style>
    /* Print only the .printable container to avoid printing navigation/other UI */
    @media print {
        body * { visibility: hidden !important; }
        .printable, .printable * { visibility: visible !important; }
        .printable { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>

<div class="max-w-4xl mx-auto print:max-w-none printable">
    <div class="flex justify-between items-center mb-6 print:hidden">
        <a href="{{ route('treatment-plans.index') }}" class="text-slate-500 hover:text-indigo-600 flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            عودة
        </a>
        <div class="flex gap-2">
            <button onclick="window.print()" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-lg hover:bg-slate-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>طباعة</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 print:shadow-none print:border-none">
        <!-- Header for Print -->
        <div class="text-center mb-8 hidden print:block">
            <h1 class="text-2xl font-bold">عيادة روما لطب وتجميل الأسنان</h1>
        </div>

        <div class="flex flex-col md:flex-row justify-between mb-8 gap-4 border-b border-slate-100 pb-6">
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">المريض</p>
                <h2 class="text-xl font-bold text-slate-800">{{ $treatmentPlan->patient->name }}</h2>
                <p class="text-sm text-slate-600">{{ $treatmentPlan->patient->phone_number ?? '' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">الطبيب المعالج</p>
                <h3 class="font-semibold text-slate-800">{{ $treatmentPlan->doctor->name ?? 'غير محدد' }}</h3>
                <p class="text-sm text-slate-500">التاريخ: {{ $treatmentPlan->created_at->format('Y-m-d') }}</p>
            </div>
            <div class="print:hidden">
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">الحالة</p>
                <form action="{{ route('treatment-plans.update', $treatmentPlan) }}" method="POST" class="inline-block">
                    @csrf @method('PUT')
                    <input type="hidden" name="notes" value="{{ $treatmentPlan->notes }}">
                    <select name="status" onchange="this.form.submit()" class="text-sm border-slate-300 rounded-lg focus:ring-indigo-500">
                        <option value="proposed" {{ $treatmentPlan->status == 'proposed' ? 'selected' : '' }}>مقترح</option>
                        <option value="accepted" {{ $treatmentPlan->status == 'accepted' ? 'selected' : '' }}>مقبول</option>
                        <option value="rejected" {{ $treatmentPlan->status == 'rejected' ? 'selected' : '' }}>مرفوض</option>
                        <option value="completed" {{ $treatmentPlan->status == 'completed' ? 'selected' : '' }}>مكتمل</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="font-bold text-lg mb-4 text-slate-800">تفاصيل الإجراءات</h3>
            <table class="w-full text-sm text-right border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600">
                        <th class="p-3 border-b border-slate-200">الجلسة</th>
                        <th class="p-3 border-b border-slate-200">السن</th>
                        <th class="p-3 border-b border-slate-200">الإجراء</th>
                        <th class="p-3 border-b border-slate-200">التكلفة التقديرية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($treatmentPlan->procedures as $proc)
                        <tr>
                            <td class="p-3 border-b border-slate-100">{{ $proc->session_number }}</td>
                            <td class="p-3 border-b border-slate-100 font-mono">{{ $proc->tooth_number ?? '-' }}</td>
                            <td class="p-3 border-b border-slate-100">{{ $proc->procedure_name }}</td>
                            <td class="p-3 border-b border-slate-100 font-medium">{{ number_format($proc->estimated_cost, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-800">
                        <td colspan="3" class="p-4 text-left">الإجمالي:</td>
                        <td class="p-4">{{ number_format($treatmentPlan->total_estimated_cost, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($treatmentPlan->notes)
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-100 mb-8">
                <p class="text-xs text-yellow-600 font-bold uppercase mb-1">ملاحظات</p>
                <p class="text-sm text-slate-700">{{ $treatmentPlan->notes }}</p>
            </div>
        @endif

        <div class="grid grid-cols-2 gap-8 mt-16 pt-8 border-t border-slate-200 print:mt-12">
            <div class="text-center">
                <p class="text-sm font-medium text-slate-500 mb-12">توقيع الطبيب</p>
                <div class="h-px bg-slate-300 w-2/3 mx-auto"></div>
            </div>
            <div class="text-center">
                <p class="text-sm font-medium text-slate-500 mb-12">توقيع المريض بالموافقة</p>
                <div class="h-px bg-slate-300 w-2/3 mx-auto"></div>
            </div>
        </div>
    </div>
</div>
@endsection
