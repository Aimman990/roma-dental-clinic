@extends('layouts.app')

@section('content')
@php
    $initProcedures = null;
    if(request()->query('procedures')){
        try {
            $initProcedures = json_decode(urldecode(request()->query('procedures')), true);
        } catch (\Throwable $e) {
            $initProcedures = null;
        }
    }
@endphp

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">إنشاء فاتورة جديدة</h2>
        <a href="{{ route('invoices.index') }}" class="px-4 py-2 border rounded hover:bg-slate-50">عودة للقائمة</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="invoiceForm()" x-init="init()">
        <form action="{{ route('invoices.store') }}" method="POST" @submit.prevent="submitForm">
            @csrf
            
            <!-- Patient & Doctor Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">المريض <span class="text-red-500">*</span></label>
                    <input type="text" list="patientsList" x-model="patientSearch" @input="onPatientInput" class="w-full border-slate-300 rounded-lg" placeholder="ابحث عن مريض..." required>
                    <datalist id="patientsList">
                        @foreach($patients as $patient)
                            <option value="{{ $patient->first_name }} {{ $patient->last_name }}" data-id="{{ $patient->id }}">{{ $patient->phone }}</option>
                        @endforeach
                    </datalist>
                    <input type="hidden" name="patient_id" x-model="form.patient_id">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">الطبيب المعالج</label>
                    <select name="doctor_id" class="w-full border-slate-300 rounded-lg">
                        <option value="">-- اختر طبيب --</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="mb-6">
                <h3 class="font-bold text-sm text-slate-700 mb-2">بنود الفاتورة</h3>
                <div class="space-y-3">
                    <template x-for="(item, index) in form.items" :key="index">
                        <div class="flex gap-2 items-start bg-slate-50 p-3 rounded border">
                            <div class="flex-1">
                                <input type="text" x-model="item.description" class="w-full text-sm border-slate-300 rounded" placeholder="الخدمة / الإجراء" required>
                            </div>
                            <div class="w-20">
                                <input type="number" x-model="item.quantity" class="w-full text-sm border-slate-300 rounded px-1" min="1" placeholder="العدد" required>
                            </div>
                            <div class="w-32">
                                <input type="number" x-model="item.unit_price" class="w-full text-sm border-slate-300 rounded px-1" step="0.01" min="0" placeholder="السعر" required>
                            </div>
                            <div class="pt-1">
                                <button type="button" @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                <button type="button" @click="addItem" class="mt-3 text-sm text-indigo-600 font-medium hover:underline">+ إضافة بند آخر</button>
            </div>

            <!-- Totals & Payments -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t pt-6">
                <!-- Payment Info -->
                <div class="space-y-4">
                    <h3 class="font-bold text-sm text-slate-700">الدفعة المقدمة (اختياري)</h3>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">المبلغ المدفوع</label>
                        <input type="number" name="initial_payment" x-model="form.initial_payment" class="w-full border-slate-300 rounded text-sm" step="0.01" min="0">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">طريقة الدفع</label>
                            <select name="initial_payment_method" class="w-full border-slate-300 rounded text-sm">
                                <option value="cash">نقدي</option>
                                <option value="card">بطاقة</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">رقم المرجع</label>
                            <input type="text" name="initial_payment_reference" class="w-full border-slate-300 rounded text-sm">
                        </div>
                    </div>
                </div>

                <!-- Calculations -->
                <div class="bg-slate-50 p-4 rounded text-sm space-y-2">
                    <div class="flex justify-between">
                        <span>المجموع الفرعي:</span>
                        <span class="font-medium" x-text="totals.subtotal.toFixed(2)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>الخصم:</span>
                        <input type="number" name="discount" x-model="form.discount" class="w-24 border-slate-300 rounded text-right p-1 h-8" min="0" step="0.01">
                    </div>
                    <div class="flex justify-between items-center">
                        <span>الضريبة:</span>
                        <input type="number" name="tax" x-model="form.tax" class="w-24 border-slate-300 rounded text-right p-1 h-8" min="0" step="0.01">
                    </div>
                    <div class="border-t pt-2 mt-2 flex justify-between text-lg font-bold text-slate-800">
                        <span>الإجمالي النهائي:</span>
                        <span x-text="totals.final.toFixed(2)"></span>
                        <input type="hidden" name="total" :value="totals.final">
                    </div>
                    <div class="flex justify-between text-indigo-600 font-medium">
                        <span>المتبقي:</span>
                        <span x-text="(totals.final - (Number(form.initial_payment)||0)).toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">حفظ الفاتورة</button>
            </div>
        </form>
    </div>
</div>

<script>
function invoiceForm() {
    return {
        patientSearch: '',
        patients: @json($patients),
        initPatientId: '{{ request()->get('patient_id', '') }}',
        form: {
            patient_id: '{{ request()->get('patient_id', '') }}',
            items: [{ description: '', quantity: 1, unit_price: 0 }],
            discount: 0,
            tax: 0,
            initial_payment: 0
        },
        init() {
            // if we have an initial patient id try to set a readable patientSearch for UX
            if (this.form.patient_id) {
                const p = this.patients.find(x => String(x.id) === String(this.form.patient_id));
                if (p) this.patientSearch = (p.first_name || '') + ' ' + (p.last_name || '');
            }
            // if procedures passed via query param, prefill items
            const initProcs = @json($initProcedures);
            if (Array.isArray(initProcs) && initProcs.length > 0) {
                this.form.items = initProcs.map(function(it){
                    return { description: it.description || it.name || '', quantity: it.quantity || 1, unit_price: it.unit_price || it.price || 0 };
                });
            }
        },
        get totals() {
            let sub = this.form.items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
            let final = Math.max(0, sub - Number(this.form.discount) + Number(this.form.tax));
            return { subtotal: sub, final: final };
        },
        addItem() {
            this.form.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.form.items.length > 1) {
                this.form.items.splice(index, 1);
            }
        },
        onPatientInput(e) {
            // Simple datalist match
            let val = e.target.value;
            // Note: In a real app we might want to ensure a valid selection by ID
            // For now we rely on the backend finding the patient or validation
            // But let's try to map back to ID from our preloaded list just in case
            let match = this.patients.find(p => (p.first_name + ' ' + p.last_name) === val || p.name === val); // Adjusted for potential accessor
            // Better: find by exact name match from datalist options
            // Actually, HTML5 datalist logic usually requires us to pick the ID manually or use a hidden input logic
            // Let's iterate options to find the data-id
            let options = document.querySelectorAll('#patientsList option');
            for(let opt of options) {
                if(opt.value === val) {
                    this.form.patient_id = opt.getAttribute('data-id');
                    break;
                }
            }
        },
        async submitForm(e) {
            // Prepare payload to match strictly what backend expects if we use fetch, 
            // OR just let the form submit naturally. 
            // Let's use fetch to handle errors gracefully without page reload if validation fails.
            
            let formData = new FormData(e.target);
            // We need to handle the array of items manually if we use FormData with complex nesting or just use JSON
            
            let data = {
                patient_id: this.form.patient_id,
                doctor_id: formData.get('doctor_id'),
                items: this.form.items,
                discount: this.form.discount,
                tax: this.form.tax,
                total: this.totals.final,
                initial_payment: this.form.initial_payment,
                initial_payment_method: formData.get('initial_payment_method'),
                initial_payment_reference: formData.get('initial_payment_reference')
            };

            if(!data.patient_id) { alert('الرجاء اختيار المريض من القائمة'); return; }

            try {
                let res = await fetch("{{ route('invoices.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(data)
                });
                
                if(res.ok) {
                    window.location.href = '{{ route('invoices.index') }}';
                } else {
                    let err = await res.json();
                    alert('خطأ: ' + (err.message || 'تأكد من إدخال جميع البيانات المطلوبة'));
                    console.error(err);
                }
            } catch(error) {
                alert('حدث خطأ في الاتصال');
                console.error(error);
            }
        }
    }
}
</script>
<script>
    // Initialize preselected patient if provided in query string
    document.addEventListener('alpine:init', () => {
        // nothing here; invoiceForm uses blade-injected initPatientId
    });
</script>
@endsection
