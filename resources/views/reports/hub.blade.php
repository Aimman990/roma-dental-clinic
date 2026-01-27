@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">مركز التقارير</h1>
            <p class="text-sm text-slate-500">نظرة عامة سريعة على تقارير النظام وروابط للوصول إلى تقارير الكيانات.</p>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <a href="/reports/doctors" class="block p-4 bg-white rounded-lg shadow hover:shadow-md">
            <div class="font-semibold">تقارير الأطباء</div>
            <div class="text-sm text-slate-500">معلومات وملخصات لكل طبيب، مع تصفية وتصدير.</div>
        </a>

        <a href="/reports/income" class="block p-4 bg-white rounded-lg shadow hover:shadow-md">
            <div class="font-semibold">التقارير المالية</div>
            <div class="text-sm text-slate-500">ملخص الدخل والمصروفات والتحصيل.</div>
        </a>

        <a href="/reports/patients" class="block p-4 bg-white rounded-lg shadow hover:shadow-md">
            <div class="font-semibold">تقارير المرضى</div>
            <div class="text-sm text-slate-500">قوائم المرضى والتفاصيل والسجل الطبي.</div>
        </a>

        <div class="block p-4 bg-white rounded-lg shadow opacity-50 cursor-not-allowed">
            <div class="font-semibold">المخزون (معطّل)</div>
            <div class="text-sm text-slate-400">خاصية المخزون معطلة حالياً</div>
        </div>

        <a href="/appointments" class="block p-4 bg-white rounded-lg shadow hover:shadow-md">
            <div class="font-semibold">المواعيد</div>
            <div class="text-sm text-slate-500">عرض المواعيد وتصفيتها وطباعة القوائم.</div>
        </a>

        <a href="/expenses" class="block p-4 bg-white rounded-lg shadow hover:shadow-md">
            <div class="font-semibold">المصروفات</div>
            <div class="text-sm text-slate-500">قوائم المصروفات والتقارير الشهرية.</div>
        </a>
    </div>
</div>
@endsection
