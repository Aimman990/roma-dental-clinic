@extends('layouts.app')

@section('header', 'إضافة صنف جديد')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('inventory.index') }}" class="text-slate-500 hover:text-indigo-600 flex items-center gap-1 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            عودة للمخزون
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
        <form action="{{ route('inventory.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">اسم الصنف <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required placeholder="مثال: إبر تخدير 2 مل">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">رمز SKU (اختياري)</label>
                    <input type="text" name="sku" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">الوحدة <span class="text-red-500">*</span></label>
                    <select name="unit" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pcs">قطعة (pcs)</option>
                        <option value="box">علبة (box)</option>
                        <option value="bottle">زجاجة (bottle)</option>
                        <option value="ml">مل (ml)</option>
                        <option value="kg">كجم (kg)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">الكمية الحالية <span class="text-red-500">*</span></label>
                    <input type="number" name="current_stock" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required min="0" value="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">حد إعادة الطلب (Min Stock) <span class="text-red-500">*</span></label>
                    <input type="number" name="min_stock_level" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required min="0" value="5">
                    <p class="text-xs text-slate-400 mt-1">سيتم التنبيه عند الوصول لهذا العدد</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">التكلفة للوحدة <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" name="cost_per_unit" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required min="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">تاريخ الانتهاء (اختياري)</label>
                    <input type="date" name="expiry_date" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
                <button type="reset" class="px-6 py-2.5 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">مسح</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">حفظ الصنف</button>
            </div>
        </form>
    </div>
</div>
@endsection
