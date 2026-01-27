@extends('layouts.app')

@section('header', 'خطط العلاج')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div class="flex gap-4">
        <!-- Stats if needed -->
    </div>
    <a href="{{ route('treatment-plans.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span>إنشاء خطة علاج</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm text-right">
        <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
            <tr>
                <th class="px-6 py-4">رقم الخطة</th>
                <th class="px-6 py-4">المريض</th>
                <th class="px-6 py-4">الطبيب</th>
                <th class="px-6 py-4">التكلفة التقديرية</th>
                <th class="px-6 py-4">الحالة</th>
                <th class="px-6 py-4">التاريخ</th>
                <th class="px-6 py-4">إجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($plans as $plan)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 font-bold text-slate-700">#{{ $plan->id }}</td>
                    <td class="px-6 py-4">{{ $plan->patient->name }}</td>
                    <td class="px-6 py-4">{{ $plan->doctor->name ?? '--' }}</td>
                    <td class="px-6 py-4 font-bold text-slate-800">{{ number_format($plan->total_estimated_cost, 2) }}</td>
                    <td class="px-6 py-4">
                        @php
                            $colors = [
                                'proposed' => 'bg-blue-100 text-blue-700',
                                'accepted' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                'completed' => 'bg-slate-100 text-slate-700',
                            ];
                            $labels = [
                                'proposed' => 'مقترح',
                                'accepted' => 'مقبول',
                                'rejected' => 'مرفوض',
                                'completed' => 'مكتمل',
                            ];
                        @endphp
                        <span class="{{ $colors[$plan->status] ?? 'bg-gray-100' }} px-2 py-1 rounded text-xs">
                            {{ $labels[$plan->status] ?? $plan->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-500">{{ $plan->created_at->format('Y-m-d') }}</td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        <a href="{{ route('treatment-plans.show', $plan) }}" class="text-indigo-600 hover:text-indigo-800 p-1" title="عرض">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </a>
                        <a href="{{ route('treatment-plans.edit', $plan) }}" class="text-slate-500 hover:text-indigo-600 p-1" title="تعديل">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="{{ route('treatment-plans.destroy', $plan) }}" method="POST" data-confirm="حذف الخطة؟" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-slate-500">لا توجد خطط علاج مضافة.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
