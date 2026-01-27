@extends('layouts.app')

@section('header', 'إضافة طلب عمل')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
        <form action="{{ route('lab-orders.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">المريض <span class="text-red-500">*</span></label>
                    <select name="patient_id" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">-- اختر المريض --</option>
                        @foreach($patients as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">اسم المعمل <span class="text-red-500">*</span></label>
                    <input type="text" name="lab_name" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required placeholder="مثال: معمل الأمل">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">نوع العمل <span class="text-red-500">*</span></label>
                    <input type="text" name="work_type" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required placeholder="مثال: تلبيسة زيركون">
                </div>

                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">تفاصيل / تعليمات (اللون، الملاحظات)</label>
                    <textarea name="details" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="3"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ الإرسال <span class="text-red-500">*</span></label>
                    <input type="date" name="sent_date" value="{{ date('Y-m-d') }}" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ الاستلام المتوقع</label>
                    <input type="date" name="due_date" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">التكلفة المتوقعة (SR)</label>
                    <input type="number" step="0.01" name="cost" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" min="0">
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <a href="{{ route('lab-orders.index') }}" class="px-6 py-2.5 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">حفظ الطلب</button>
            </div>
        </form>
    </div>
</div>
@endsection
