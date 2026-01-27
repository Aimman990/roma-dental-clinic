@extends('layouts.app')

@section('header', 'طلبات المعمل')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div class="flex gap-4">
        <!-- Stats -->
    </div>
    <a href="{{ route('lab-orders.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        <span>طلب جديد</span>
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <table class="w-full text-sm text-right">
        <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
            <tr>
                <th class="px-6 py-4">المريض</th>
                <th class="px-6 py-4">المعمل</th>
                <th class="px-6 py-4">العمل المطلوب</th>
                <th class="px-6 py-4">تاريخ الإرسال</th>
                <th class="px-6 py-4">تاريخ الاستلام المتوقع</th>
                <th class="px-6 py-4">الحالة</th>
                <th class="px-6 py-4">التكلفة</th>
                <th class="px-6 py-4">إجراءات</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800">{{ $order->patient->name }}</div>
                        <div class="text-xs text-slate-500">{{ $order->doctor->name ?? 'غير محدد' }}</div>
                    </td>
                    <td class="px-6 py-4">{{ $order->lab_name }}</td>
                    <td class="px-6 py-4">{{ $order->work_type }}</td>
                    <td class="px-6 py-4 text-slate-500">{{ $order->sent_date?->format('Y-m-d') }}</td>
                    <td class="px-6 py-4">
                        <span class="{{ $order->due_date && $order->due_date < now() && $order->status !== 'delivered' ? 'text-red-600 font-bold' : 'text-slate-500' }}">
                            {{ $order->due_date?->format('Y-m-d') ?? '--' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $colors = [
                                'sent' => 'bg-orange-100 text-orange-700',
                                'received' => 'bg-blue-100 text-blue-700',
                                'delivered' => 'bg-green-100 text-green-700',
                            ];
                            $labels = [
                                'sent' => 'تم الإرسال',
                                'received' => 'تم الاستلام',
                                'delivered' => 'تم التسليم للمريض',
                            ];
                        @endphp
                        <span class="{{ $colors[$order->status] }} px-2 py-1 rounded text-xs">
                            {{ $labels[$order->status] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-medium">{{ number_format($order->cost, 2) }}</td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        <a href="{{ route('lab-orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-800 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </a>
                        <form action="{{ route('lab-orders.destroy', $order) }}" method="POST" data-confirm="حذف الطلب؟" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-8 text-center text-slate-500">لا توجد طلبات معمل حالياً.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
