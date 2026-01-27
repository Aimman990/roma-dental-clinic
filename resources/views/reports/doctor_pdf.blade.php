@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded">
    <h1 class="text-xl font-bold">ملف ملخص الطبيب</h1>
    <div class="mt-4">
        <div><strong>الطبيب:</strong> {{ $doctor->name }}</div>
        <div><strong>إجمالي مفوتر:</strong> {{ $summary['total_invoiced'] ?? 0 }}</div>
        <div><strong>المحصّل:</strong> {{ $summary['total_collected'] ?? 0 }}</div>
        <div><strong>العمولة المكتسبة:</strong> {{ $summary['commission_earned'] ?? 0 }}</div>
        <div><strong>المسحوبات:</strong> {{ $summary['total_withdrawn'] ?? 0 }}</div>
        <div><strong>الرصيد:</strong> {{ $summary['balance'] ?? 0 }}</div>
    </div>
</div>
@endsection
