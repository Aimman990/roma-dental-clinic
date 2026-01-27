@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto" x-data="invoiceShowApp()">
    <!-- Action Bar -->
    <div class="flex justify-between items-center mb-6 print:hidden">
        <h2 class="text-2xl font-bold text-gray-800">تفاصيل الفاتورة #{{ $invoice->invoice_number }}</h2>
        <div class="flex gap-2">
            <button @click="printInvoice" class="px-4 py-2 bg-slate-800 text-white rounded hover:bg-slate-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                طباعة
            </button>
            @if($invoice->remaining > 0)
            <button @click="openPaymentModal" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-2 shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                تسجيل دفعة
            </button>
            @endif
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded hover:bg-gray-50">عودة</a>
        </div>
    </div>

    <!-- Invoice Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden print:shadow-none print:rounded-none" id="print-area">
        <!-- Header -->
        <div class="p-8 bg-slate-50 border-b border-slate-200 print:bg-white print:border-none">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black text-indigo-900 mb-2 print:text-black">عيادة روما</h1>
                    <p class="text-sm text-gray-500 print:text-gray-700">لطب وتجميل الأسنان<br>صنعاء_الستين الغربي_جوار مطعم النخلة_مدخل سوق السنينية_فوق صيدلية المدني الدور الثالث<br>هاتف: 778999441/736741475<br>البريد: info@Roma.com</p>
                </div>
                <div class="text-left font-mono">
                    <div class="text-sm text-gray-500 mb-1">رقم الفاتورة</div>
                    <div class="text-xl font-bold text-gray-800">{{ $invoice->invoice_number }}</div>
                    <div class="text-sm text-gray-500 mt-2 mb-1">تاريخ الإصدار</div>
                    <div class="text-lg font-bold text-gray-800">{{ $invoice->created_at->format('Y-m-d') }}</div>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="p-8 grid grid-cols-2 gap-12 print:py-4">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase mb-2">بيانات المريض</h3>
                <div class="text-lg font-bold text-gray-800">{{ $invoice->patient->name ?? 'غير محدد' }}</div>
                <div class="text-sm text-gray-500">{{ $invoice->patient->phone ?? '' }}</div>
            </div>
            <div class="text-left">
                <h3 class="text-xs font-bold text-slate-400 uppercase mb-2">الطبيب المعالج</h3>
                <div class="text-lg font-bold text-gray-800">{{ $invoice->doctor->name ?? 'غير محدد' }}</div>
            </div>
        </div>

        <!-- Items -->
        <div class="px-8 py-4">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-100">
                        <th class="py-3 text-sm font-semibold text-slate-600">الخدمة / الوصف</th>
                        <th class="py-3 text-sm font-semibold text-slate-600 text-center w-24">الكمية</th>
                        <th class="py-3 text-sm font-semibold text-slate-600 text-center w-32">السعر</th>
                        <th class="py-3 text-sm font-semibold text-slate-600 text-left w-32">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-4 text-gray-800">{{ $item->service->name ?? $item->description }}</td>
                        <td class="py-4 text-center text-gray-600 font-mono">{{ $item->quantity }}</td>
                        <td class="py-4 text-center text-gray-600 font-mono">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-4 text-left text-gray-800 font-bold font-mono">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 print:bg-white print:border-none">
            <div class="flex justify-end">
                <div class="w-full max-w-xs space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>المجموع الفرعي:</span>
                        <span class="font-mono">{{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                    <div class="flex justify-between text-sm text-red-500">
                        <span>خصم:</span>
                        <span class="font-mono">-{{ number_format($invoice->discount, 2) }}</span>
                    </div>
                    @endif
                    @if($invoice->tax > 0)
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>ضريبة (15%):</span>
                        <span class="font-mono">{{ number_format($invoice->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-xl font-black text-indigo-900 pt-3 border-t border-slate-200 print:text-black print:border-black">
                        <span>الإجمالي الكلي:</span>
                        <span class="font-mono">{{ number_format($invoice->total, 2) }} ر.ي</span>
                    </div>
                    
                    <div class="pt-4 space-y-1">
                        <div class="flex justify-between text-sm text-green-600 font-bold">
                            <span>المدفوع:</span>
                            <span class="font-mono">{{ number_format($invoice->payments->sum('amount'), 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-red-600 font-bold">
                            <span>المتبقي:</span>
                            <span class="font-mono">{{ number_format($invoice->remaining, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Print Only -->
        <div class="hidden print:block text-center text-xs text-gray-400 mt-12 mb-8">
            تم إصدار هذه الفاتورة إلكترونياً من نظام عيادة روما لطب وتجميل الأسنان.
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
        <div @click.away="showModal = false" class="bg-white w-full max-w-sm p-6 rounded-xl shadow-2xl">
            <h3 class="font-bold text-lg mb-4 text-slate-800">تسجيل دفعة جديدة</h3>
            
            <div class="bg-indigo-50 p-3 rounded mb-4 text-sm border border-indigo-100">
                <div class="flex justify-between">
                    <span class="text-indigo-600">المبلغ المتبقي:</span>
                    <span class="font-mono font-bold text-indigo-900">{{ number_format($invoice->remaining, 2) }} ر.ي</span>
                </div>
            </div>

            <form @submit.prevent="submitPayment">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-slate-700">المبلغ المدفوع</label>
                    <div class="relative">
                        <input type="number" x-model="form.amount" step="0.01" min="1" class="w-full border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500 pr-8 text-lg font-bold" required>
                        <span class="absolute left-3 top-3 text-slate-400 text-sm">ر.ي</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-slate-700">طريقة الدفع</label>
                    <select x-model="form.method" class="w-full border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="cash">نقدي (Cash)</option>
                        <option value="card">بطاقة (Card)</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1 text-slate-700">ملاحظات / مرجع</label>
                    <input type="text" x-model="form.reference" class="w-full border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" placeholder="اختياري">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded font-medium">إلغاء</button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded font-medium hover:bg-green-700 shadow-sm" :disabled="loading">
                        <span x-show="!loading">حفظ الدفعة</span>
                        <span x-show="loading">جاري الحفظ...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function invoiceShowApp() {
    return {
        showModal: false,
        loading: false,
        form: {
            amount: '{{ $invoice->remaining }}',
            method: 'cash',
            reference: ''
        },
        
        printInvoice() {
            window.print();
        },

        openPaymentModal() {
            this.form.amount = '{{ $invoice->remaining }}';
            this.showModal = true;
        },

        async submitPayment() {
            if (this.loading) return;
            this.loading = true;

            try {
                let res = await fetch('/api/payments', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        invoice_id: {{ $invoice->id }},
                        patient_id: {{ $invoice->patient_id }},
                        amount: this.form.amount,
                        method: this.form.method,
                        reference: this.form.reference
                    })
                });

                if (res.ok) {
                    alert('تم تسجيل الدفعة بنجاح ✅');
                    location.reload(); // Reload to update totals without complex reactivity
                } else {
                    let err = await res.json();
                    alert('خطأ: ' + (err.message || 'حدث خطأ غير متوقع'));
                }
            } catch (e) {
                console.error(e);
                alert('خطأ في الاتصال');
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style>
    @media print {
        @page { margin: 0; size: auto; }
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; margin: 0; box-shadow: none; border-radius: 0; }
        .print\:hidden { display: none !important; }
        .print\:block { display: block !important; }
        .print\:text-black { color: black !important; }
        .print\:bg-white { background-color: white !important; }
        .print\:border-none { border: none !important; }
    }
</style>
@endsection
