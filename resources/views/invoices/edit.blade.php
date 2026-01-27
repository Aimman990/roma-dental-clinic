@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">تعديل الفاتورة #{{ $invoice->invoice_number }}</h2>
        <a href="{{ route('invoices.index') }}" class="px-4 py-2 border rounded hover:bg-slate-50">عودة للقائمة</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6" x-data="invoiceEditForm()">
        <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" @submit.prevent="submitForm">
            @csrf
            @method('PUT')
            
            <!-- Patient & Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">المريض</label>
                    <div class="p-2 bg-slate-100 rounded border border-slate-200 text-slate-600">
                        {{ $invoice->patient->name }}
                        @if($invoice->patient->phone) <span class="text-xs text-slate-400 block">{{ $invoice->patient->phone }}</span> @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">الطبيب المعالج</label>
                    <select name="doctor_id" x-model="form.doctor_id" class="w-full border-slate-300 rounded-lg">
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
                <div class="text-sm text-slate-500">
                    <p>المبلغ المدفوع سابقاً: <strong>{{ number_format($invoice->payments->sum('amount'), 2) }}</strong></p>
                    <p class="mt-1">الحالة: <span class="px-2 py-0.5 rounded text-xs bg-slate-100">{{ $invoice->status }}</span></p>
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
                        <span>الإجمالي التصحيحي:</span>
                        <span x-text="totals.final.toFixed(2)"></span>
                        <input type="hidden" name="total" :value="totals.final">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>

<script>
function invoiceEditForm() {
    return {
        form: {
            doctor_id: '{{ $invoice->doctor_id }}',
            items: @json($invoice->items->map(fn($i) => ['description'=>$i->description, 'quantity'=>$i->quantity, 'unit_price'=>$i->unit_price])),
            discount: {{ $invoice->discount ?? 0 }},
            tax: {{ $invoice->tax ?? 0 }}
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
        async submitForm(e) {
            let data = {
                doctor_id: this.form.doctor_id,
                items: this.form.items,
                discount: this.form.discount,
                tax: this.form.tax,
                total: this.totals.final
            };

            try {
                let res = await fetch("{{ route('invoices.update', $invoice->id) }}", {
                    method: 'PUT',
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
                    alert('خطأ: ' + (err.message || 'فشل التحديث'));
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
@endsection
