@extends('layouts.app')

@section('header', 'تعديل طلب المعمل')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
        <form action="{{ route('lab-orders.update', $labOrder) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">المريض</label>
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-slate-600 font-medium">
                        {{ $labOrder->patient->name }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">اسم المعمل <span class="text-red-500">*</span></label>
                    <input type="text" name="lab_name" value="{{ old('lab_name', $labOrder->lab_name) }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">نوع العمل <span class="text-red-500">*</span></label>
                    <input type="text" name="work_type" value="{{ old('work_type', $labOrder->work_type) }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">تفاصيل / تعليمات</label>
                    <textarea name="details" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="3">{{ old('details', $labOrder->details) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ الاستلام المتوقع</label>
                    <input type="date" name="due_date" value="{{ old('due_date', $labOrder->due_date?->format('Y-m-d')) }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">الحالة <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="sent" {{ $labOrder->status == 'sent' ? 'selected' : '' }}>تم الإرسال</option>
                        <option value="received" {{ $labOrder->status == 'received' ? 'selected' : '' }}>تم الاستلام من المعمل</option>
                        <option value="delivered" {{ $labOrder->status == 'delivered' ? 'selected' : '' }}>تم التسليم للمريض</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ الاستلام الفعلي</label>
                    <input type="date" name="received_date" value="{{ old('received_date', $labOrder->received_date?->format('Y-m-d')) }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">التكلفة (SR)</label>
                    <input type="number" step="0.01" name="cost" value="{{ old('cost', $labOrder->cost) }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" min="0">
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <a href="{{ route('lab-orders.index') }}" class="px-6 py-2.5 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">حفظ التغييرات</button>
            </div>
        </form>
    </div>
</div>
@endsection
