@extends('layouts.app')

@section('content')
  <h2 class="text-xl font-bold mb-4">سجل العمليات</h2>
  <div class="bg-white rounded shadow p-4">
    <table class="w-full text-right">
      <thead>
        <tr class="text-sm text-gray-500 border-b"><th class="p-2">الوقت</th><th class="p-2">المستخدم</th><th class="p-2">الإجراء</th><th class="p-2">الطريق</th></tr>
      </thead>
      <tbody>
        @foreach($logs as $l)
        <tr class="border-b hover:bg-gray-50"><td class="p-2">{{ $l->created_at }}</td><td class="p-2">{{ $l->user?->name ?? 'نظام' }}</td><td class="p-2">{{ $l->action }}</td><td class="p-2">{{ $l->route }}</td></tr>
        @endforeach
      </tbody>
    </table>
  </div>
@endsection
