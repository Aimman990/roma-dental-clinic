@extends('layouts.app')

@section('header', 'تعديل خطة العلاج #' . $treatmentPlan->id)

@section('content')
<div class="max-w-4xl mx-auto" x-data="treatmentPlanForm()">
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 md:p-8">
        <form action="{{ route('treatment-plans.update', $treatmentPlan) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">المريض <span class="text-red-500">*</span></label>
                <div class="relative" x-data="{ search: '{{ $treatmentPlan->patient->name }}', show: false, selected: {{ $treatmentPlan->patient->id }}, patients: {{ $patients->map(fn($p)=>['id'=>$p->id, 'name'=>$p->name, 'phone'=>$p->phone])->toJson() }} }">
                    <input type="hidden" name="patient_id" x-model="selected">
                    <input 
                        type="text" 
                        x-model="search" 
                        @input="selected = null; show = true" 
                        @click="show = true"
                        @click.away="show = false"
                        placeholder="ابحث عن مريض..." 
                        class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        required
                    >
                    <div x-show="show" class="absolute z-10 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-60 overflow-y-auto mt-1" style="display: none;">
                        <template x-for="p in patients.filter(p => p.name.toLowerCase().includes(search.toLowerCase()) || (p.phone && p.phone.includes(search)))" :key="p.id">
                            <div @click="selected = p.id; search = p.name; show = false" class="p-2 hover:bg-indigo-50 cursor-pointer border-b last:border-0">
                                <div class="font-bold text-sm" x-text="p.name"></div>
                                <div class="text-xs text-slate-500" x-text="p.phone"></div>
                            </div>
                        </template>
                        <div x-show="patients.filter(p => p.name.toLowerCase().includes(search.toLowerCase()) || (p.phone && p.phone.includes(search))).length === 0" class="p-2 text-sm text-slate-500">لا توجد نتائج</div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">ملاحظات عامة</label>
                <textarea name="notes" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" rows="2">{{ $treatmentPlan->notes }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-slate-700 mb-2">الطبيب المعالج</label>
                <select name="doctor_id" class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="">(اختيار) الطبيب الافتراضي سيكون المسجل الحالي</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ $treatmentPlan->doctor_id == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                    @endforeach
                </select>
            </div>

            <hr class="my-6 border-slate-100">

            <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-800">الإجراءات المقترحة</h3>
                <button type="button" @click="addProcedure()" class="text-sm bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 font-medium">
                    + إضافة إجراء
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(row, index) in rows" :key="index">
                    <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 relative grid grid-cols-1 md:grid-cols-12 gap-4">
                        
                        <div class="md:col-span-1">
                            <label class="block text-xs font-medium text-slate-500 mb-1">الجلسة</label>
                            <input type="number" :name="'procedures['+index+'][session_number]'" x-model="row.session" class="w-full text-sm border-slate-300 rounded focus:ring-indigo-500" min="1" required>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-500 mb-1">الإجراء</label>
                            <input type="text" :name="'procedures['+index+'][procedure_name]'" x-model="row.name" class="w-full text-sm border-slate-300 rounded focus:ring-indigo-500" placeholder="مثال: حشو عصب" required>
                            <input type="hidden" :name="'procedures['+index+'][service_id]'">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-slate-500 mb-1">السن</label>
                            <input type="text" :name="'procedures['+index+'][tooth_number]'" x-model="row.tooth" class="w-full text-sm border-slate-300 rounded focus:ring-indigo-500" placeholder="#12">
                        </div>

                        <div class="md:col-span-3">
                            <label class="block text-xs font-medium text-slate-500 mb-1">التكلفة التقديرية</label>
                            <input type="number" :name="'procedures['+index+'][estimated_cost]'" x-model="row.cost" class="w-full text-sm border-slate-300 rounded focus:ring-indigo-500" min="0" step="0.01" required>
                        </div>

                        <div class="md:col-span-2 flex items-end justify-end">
                            <button type="button" @click="removeProcedure(index)" class="text-red-500 hover:text-red-700 text-sm p-2 bg-white rounded border border-red-100 hover:bg-red-50 w-full md:w-auto">
                                حذف
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-6 flex justify-end gap-2 text-lg font-bold text-slate-800">
                <span>الإجمالي التقديري:</span>
                <span x-text="calculateTotal()"></span>
            </div>

            <div class="flex items-center justify-end gap-4 pt-6 mt-6 border-t border-slate-100">
                <a href="{{ route('treatment-plans.index') }}" class="px-6 py-2.5 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">إلغاء</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
    function treatmentPlanForm() {
        return {
            rows: [
                @foreach($treatmentPlan->procedures as $p)
                    { session: {{ $p->session_number }}, name: '{{ addslashes($p->procedure_name) }}', tooth: '{{ $p->tooth_number }}', cost: {{ $p->estimated_cost }} },
                @endforeach
            ],
            addProcedure() {
                this.rows.push({ session: 1, name: '', tooth: '', cost: 0 });
            },
            removeProcedure(index) {
                if(this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            calculateTotal() {
                let total = this.rows.reduce((sum, row) => sum + Number(row.cost), 0);
                return total.toFixed(2);
            }
        }
    }
</script>
@endsection
