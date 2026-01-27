@extends('layouts.app')

@section('content')
  <div x-data="paymentsApp()" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">الدفعات</h2>
      <div class="flex items-center gap-2">
        <input x-model="searchQuery" @input.debounce="fetch()" placeholder="بحث عن رقم فاتورة أو مريض..." class="px-3 py-2 border rounded" />
        <select x-model="filterMethod" @change="fetch()" class="px-3 py-2 border rounded">
          <option value="">جميع طرق الدفع</option>
          <option value="cash">نقدي</option>
          <option value="card">بطاقة</option>
          <option value="bank_transfer">حوالة بنكية</option>
          <option value="other">أخرى</option>
        </select>
        <button @click="openNew()" class="px-3 py-2 bg-indigo-600 text-white rounded">إضافة دفعة</button>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-4 gap-4">
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">إجمالي الدفعات</p>
        <p class="text-2xl font-bold text-green-600" x-text="totalPayments.toFixed(2) + ' ر.ع'">0.00 ر.ع</p>
      </div>
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">عدد الدفعات</p>
        <p class="text-2xl font-bold text-blue-600" x-text="payments.length">0</p>
      </div>
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">متوسط الدفعة</p>
        <p class="text-2xl font-bold text-purple-600" x-text="(payments.length > 0 ? totalPayments / payments.length : 0).toFixed(2) + ' ر.ع'">0.00 ر.ع</p>
      </div>
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">آخر دفعة</p>
        <p class="text-lg font-bold" x-text="lastPaymentDate">-</p>
      </div>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full text-right min-w-[900px]">
        <thead>
          <tr class="text-sm text-gray-500 border-b bg-gray-50">
            <th class="p-3">رقم الإيصال</th>
            <th class="p-3">رقم الفاتورة</th>
            <th class="p-3">المريض</th>
            <th class="p-3">المبلغ</th>
            <th class="p-3">الطريقة</th>
            <th class="p-3">المرجع</th>
            <th class="p-3">التاريخ</th>
            <th class="p-3">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="payment in payments" :key="payment.id">
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3 font-mono text-sm" x-text="payment.receipt_number || '-'">-</td>
              <td class="p-3 font-medium" x-text="payment.invoice?.invoice_number || '-'">-</td>
              <td class="p-3" x-text="payment.patient?.first_name + ' ' + (payment.patient?.last_name || '')">-</td>
              <td class="p-3 font-bold text-green-600" x-text="payment.amount.toFixed(2) + ' ر.ع'">-</td>
              <td class="p-3">
                <span :class="methodColor(payment.method)" class="px-2 py-1 rounded text-xs font-medium" x-text="getMethodName(payment.method)"></span>
              </td>
              <td class="p-3 text-sm" x-text="payment.reference || '-'">-</td>
              <td class="p-3 text-sm" x-text="new Date(payment.created_at).toLocaleDateString('ar-SA')">-</td>
              <td class="p-3">
                <div class="flex gap-2 justify-end">
                  <button @click="view(payment)" class="px-2 py-1 text-sm border rounded hover:bg-indigo-50">عرض</button>
                  <button @click="remove(payment.id)" class="px-2 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50">حذف</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <template x-if="payments.length === 0">
        <div class="p-6 text-center text-gray-500">لا توجد دفعات. <a href="#" @click.prevent="openNew()" class="text-indigo-600">سجل واحدة</a></div>
      </template>
    </div>

    <!-- Create payment modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div @click.away="closeModal" class="bg-white w-full max-w-md p-6 rounded-lg shadow max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-bold mb-4">تسجيل دفعة جديدة</h3>
        <form @submit.prevent="save" class="space-y-3">
          <div>
            <label class="text-sm font-medium text-gray-700">الفاتورة *</label>
            <select x-model="form.invoice_id" @change="onInvoiceChange" class="px-3 py-2 border rounded w-full" required>
              <option value="">-- اختر فاتورة --</option>
              <template x-for="inv in unpaidInvoices" :key="inv.id">
                <option :value="inv.id" x-text="inv.invoice_number + ' - ' + inv.patient.first_name + ' (' + inv.remaining.toFixed(2) + ' ر.ع)'"></option>
              </template>
            </select>
          </div>
          
          <template x-if="form.invoice_id">
            <div class="bg-blue-50 p-3 rounded text-sm space-y-1">
              <div class="flex justify-between">
                <span>الإجمالي:</span>
                <span class="font-bold" x-text="selectedInvoice?.total.toFixed(2) + ' ر.ع'">-</span>
              </div>
              <div class="flex justify-between">
                <span>المدفوع:</span>
                <span class="font-bold" x-text="(selectedInvoice?.total - selectedInvoice?.remaining).toFixed(2) + ' ر.ع'">-</span>
              </div>
              <div class="flex justify-between border-t border-blue-100 pt-1">
                <span>المتبقي:</span>
                <span class="font-bold text-blue-600" x-text="selectedInvoice?.remaining.toFixed(2) + ' ر.ع'">-</span>
              </div>
            </div>
          </template>

          <div>
            <label class="text-sm font-medium text-gray-700">المبلغ *</label>
            <input x-model.number="form.amount" type="number" step="0.01" min="0" placeholder="0.00" class="px-3 py-2 border rounded w-full" required />
            <p class="text-xs text-gray-500 mt-1" x-show="form.amount > (selectedInvoice?.remaining || 0)" x-cloak>تحذير: المبلغ أكبر من المتبقي</p>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">طريقة الدفع *</label>
            <select x-model="form.method" class="px-3 py-2 border rounded w-full" required>
              <option value="">-- اختر طريقة --</option>
              <option value="cash">نقدي</option>
              <option value="card">بطاقة</option>
              <option value="bank_transfer">حوالة بنكية</option>
              <option value="other">أخرى</option>
            </select>
          </div>

          <div>
            <label class="text-sm font-medium text-gray-700">المرجع / الرقم</label>
            <input x-model="form.reference" placeholder="مثل: رقم البطاقة أو الشيك" class="px-3 py-2 border rounded w-full" />
          </div>

          <div class="flex justify-between gap-2 pt-4">
            <button type="button" @click="closeModal" class="flex-1 px-3 py-2 border rounded">إلغاء</button>
            <button type="submit" class="flex-1 px-3 py-2 bg-indigo-600 text-white rounded">حفظ الدفعة</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View payment modal -->
    <div x-show="viewModalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div @click.away="viewModalOpen = false" class="bg-white w-full max-w-md p-6 rounded-lg shadow">
        <h3 class="text-lg font-bold mb-4">تفاصيل الدفعة</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">رقم الإيصال:</span>
            <span class="font-mono" x-text="selectedPayment?.receipt_number"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">الفاتورة:</span>
            <span x-text="selectedPayment?.invoice?.invoice_number"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">المريض:</span>
            <span x-text="selectedPayment?.patient?.first_name"></span>
          </div>
          <div class="flex justify-between border-t pt-2">
            <span class="text-gray-600">المبلغ:</span>
            <span class="font-bold text-green-600" x-text="selectedPayment?.amount.toFixed(2) + ' ر.ي'"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">الطريقة:</span>
            <span x-text="getMethodName(selectedPayment?.method)"></span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">التاريخ:</span>
            <span x-text="new Date(selectedPayment?.created_at).toLocaleDateString('ar-SA')"></span>
          </div>
        </div>
        <button @click="viewModalOpen = false" class="w-full mt-6 px-3 py-2 bg-gray-200 rounded">إغلاق</button>
      </div>
    </div>

    <!-- Success message -->
    <template x-if="message">
      <div class="fixed bottom-4 right-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded shadow" @click="message = null" style="cursor: pointer;">
        <p x-text="message"></p>
      </div>
    </template>
  </div>

  <script>
    function paymentsApp(){
      return {
        payments: [],
        unpaidInvoices: [],
        modalOpen: false,
        viewModalOpen: false,
        searchQuery: '',
        filterMethod: '',
        message: '',
        selectedPayment: null,
        form: { invoice_id: null, amount: 0, method: '', reference: '' },
        
        get totalPayments(){
          return this.payments.reduce((sum, p) => sum + (p.amount || 0), 0);
        },

        get lastPaymentDate(){
          if (this.payments.length === 0) return '-';
          return new Date(this.payments[0].created_at).toLocaleDateString('ar-SA');
        },

        get selectedInvoice(){
          return this.unpaidInvoices.find(inv => inv.id == this.form.invoice_id);
        },

        getMethodName(method){
          const names = { 'cash': 'نقدي', 'card': 'بطاقة', 'bank_transfer': 'حوالة بنكية', 'other': 'أخرى' };
          return names[method] || method;
        },

        methodColor(method){
          const colors = {
            'cash': 'bg-green-100 text-green-800',
            'card': 'bg-blue-100 text-blue-800',
            'bank_transfer': 'bg-purple-100 text-purple-800',
            'other': 'bg-yellow-100 text-yellow-800'
          };
          return colors[method] || 'bg-gray-100 text-gray-800';
        },

        async fetch(){
          try {
            let url = '/api/payments';
            if (this.searchQuery || this.filterMethod) {
              const params = new URLSearchParams();
              if (this.searchQuery) params.append('q', this.searchQuery);
              if (this.filterMethod) params.append('method', this.filterMethod);
              url += '?' + params.toString();
            }
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            this.payments = (data.data || data).sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
          } catch (e) {
            console.error('Fetch error:', e);
          }
        },

        async fetchUnpaidInvoices(){
          try {
            const res = await fetch('/api/invoices', { credentials: 'same-origin' });
            const data = await res.json();
            const invoices = data.data || data;
            this.unpaidInvoices = invoices
              .filter(inv => inv.status !== 'paid')
              .map(inv => {
                const paid = inv.payments?.reduce((sum, p) => sum + p.amount, 0) || 0;
                return { ...inv, remaining: inv.total - paid };
              });
          } catch (e) {
            console.error('Fetch invoices error:', e);
          }
        },

        openNew(){
          this.form = { invoice_id: null, amount: 0, method: '', reference: '' };
          this.modalOpen = true;
          this.fetchUnpaidInvoices();
        },

        closeModal(){
          this.modalOpen = false;
          this.form = { invoice_id: null, amount: 0, method: '', reference: '' };
        },

        onInvoiceChange(){
          if (this.selectedInvoice && this.form.amount === 0) {
            this.form.amount = this.selectedInvoice.remaining;
          }
        },

        async save(){
          try {
            const res = await fetch('/api/payments', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                invoice_id: this.form.invoice_id,
                patient_id: this.selectedInvoice?.patient_id,
                amount: this.form.amount,
                method: this.form.method,
                reference: this.form.reference
              })
            });

            if (!res.ok) {
              const error = await res.json();
              alert('خطأ: ' + (error.message || 'حدث خطأ عند الحفظ'));
              return;
            }

            this.message = 'تم تسجيل الدفعة بنجاح';
            this.closeModal();
            this.fetch();
            setTimeout(() => this.message = '', 3000);
          } catch (e) {
            alert('خطأ: ' + e.message);
          }
        },

        view(payment){
          this.selectedPayment = payment;
          this.viewModalOpen = true;
        },

        async remove(id){
          if (!await confirmAction('هل تريد حذف هذه الدفعة؟')) return;
          try {
            const res = await fetch(`/api/payments/${id}`, {
              method: 'DELETE',
              credentials: 'same-origin',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            });

            if (!res.ok) {
              alert('خطأ عند حذف الدفعة');
              return;
            }

            this.message = 'تم حذف الدفعة بنجاح';
            this.fetch();
            setTimeout(() => this.message = '', 3000);
          } catch (e) {
            alert('خطأ: ' + e.message);
          }
        },

        init(){
          this.fetch();
        }
      }
    }
  </script>
@endsection
