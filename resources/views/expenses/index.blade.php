@extends('layouts.app')

@section('content')
  <div x-data="expensesApp()" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">المصاريف</h2>
      <div class="flex items-center gap-2">
        <input x-model="searchQuery" @input.debounce="fetch()" placeholder="بحث عن مصروف..."
          class="px-3 py-2 border rounded" />
        <select x-model="filterCategory" @change="fetch()" class="px-3 py-2 border rounded">
          <option value="">جميع الفئات</option>
          <option value="salary">الرواتب</option>
          <option value="supplies">مواد وأدوات</option>
          <option value="rent">الإيجار</option>
          <option value="utilities">فواتير ومرافق</option>
          <option value="other">أخرى</option>
        </select>
        <button @click="openNew()" class="px-3 py-2 bg-indigo-600 text-white rounded">إضافة مصروف</button>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="grid grid-cols-3 gap-4">
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">إجمالي المصاريف هذا الشهر</p>
        <p class="text-2xl font-bold text-indigo-600" x-text="totalExpenses.toFixed(2) + ' ر.ع'">0.00 ر.ع</p>
      </div>
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">عدد المصاريف</p>
        <p class="text-2xl font-bold text-blue-600" x-text="expenses.length">0</p>
      </div>
      <div class="bg-white rounded shadow p-4">
        <p class="text-sm text-gray-600">المعدل اليومي</p>
        <p class="text-2xl font-bold text-green-600" x-text="(totalExpenses / 30).toFixed(2) + ' ر.ي'">0.00 ر.ي</p>
      </div>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full text-right min-w-[800px]">
        <thead>
          <tr class="text-sm text-gray-500 border-b bg-gray-50">
            <th class="p-3">التاريخ</th>
            <th class="p-3">الفئة</th>
            <th class="p-3">العنوان</th>
            <th class="p-3">المبلغ</th>
            <th class="p-3">المسجل بواسطة</th>
            <th class="p-3">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="expense in expenses" :key="expense.id">
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3 text-sm" x-text="new Date(expense.incurred_on).toLocaleDateString('ar-SA')">-</td>
              <td class="p-3">
                <span :class="categoryColor(expense.category)" class="px-2 py-1 rounded text-xs font-medium"
                  x-text="getCategoryName(expense.category)"></span>
              </td>
              <td class="p-3 font-medium" x-text="expense.title">-</td>
              <td class="p-3 font-bold text-red-600" x-text="Number(expense.amount).toFixed(2) + ' ر.ع'">-</td>
              <td class="p-3 text-sm" x-text="expense.recorder?.name || 'نظام'">-</td>
              <td class="p-3">
                <div class="flex gap-2 justify-end">
                  <button @click="edit(expense)"
                    class="px-2 py-1 text-sm border rounded hover:bg-indigo-50">تعديل</button>
                  <button @click="remove(expense.id)"
                    class="px-2 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50">حذف</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <template x-if="expenses.length === 0">
        <div class="p-6 text-center text-gray-500">لا توجد مصاريف. <a href="#" @click.prevent="openNew()"
            class="text-indigo-600">أضف واحدة</a></div>
      </template>
    </div>

    <!-- Create/Edit modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div @click.away="closeModal" class="bg-white w-full max-w-md p-6 rounded-lg shadow-xl relative">
        <h3 class="text-lg font-bold mb-4" x-text="form.id ? 'تعديل المصروف' : 'إضافة مصروف جديد'"></h3>
        <form @submit.prevent="save" class="space-y-3">
          <div>
            <label class="text-sm font-medium text-gray-700">التاريخ *</label>
            <input x-model="form.incurred_on" type="date" class="px-3 py-2 border rounded w-full" required />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">الفئة *</label>
            <select x-model="form.category" class="px-3 py-2 border rounded w-full" required>
              <option value="">-- اختر فئة --</option>
              <option value="salary">الرواتب</option>
              <option value="supplies">مواد وأدوات</option>
              <option value="rent">الإيجار</option>
              <option value="utilities">فواتير ومرافق</option>
              <option value="other">أخرى</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">العنوان *</label>
            <input x-model="form.title" placeholder="مثل: فاتورة الماء" class="px-3 py-2 border rounded w-full"
              required />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">الوصف (اختياري)</label>
            <textarea x-model="form.description" placeholder="تفاصيل إضافية" class="px-3 py-2 border rounded w-full"
              rows="2"></textarea>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">المبلغ *</label>
            <input x-model.number="form.amount" type="number" step="0.01" min="0" placeholder="0.00"
              class="px-3 py-2 border rounded w-full" required />
          </div>
          <div class="flex justify-between gap-2 pt-4">
            <button type="button" @click="closeModal"
              class="flex-1 px-3 py-2 border rounded hover:bg-gray-50">إلغاء</button>
            <button type="submit"
              class="flex-1 px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function expensesApp() {
      return {
        expenses: [],
        modalOpen: false,
        searchQuery: '',
        filterCategory: '',
        form: { id: null, category: '', title: '', description: '', amount: 0, incurred_on: new Date().toISOString().split('T')[0] },

        getCategoryName(cat) {
          const names = { 'salary': 'الرواتب', 'supplies': 'مواد وأدوات', 'rent': 'الإيجار', 'utilities': 'فواتير ومرافق', 'other': 'أخرى' };
          return names[cat] || cat;
        },

        categoryColor(cat) {
          const colors = {
            'salary': 'bg-orange-100 text-orange-800',
            'supplies': 'bg-blue-100 text-blue-800',
            'rent': 'bg-purple-100 text-purple-800',
            'utilities': 'bg-red-100 text-red-800',
            'other': 'bg-gray-100 text-gray-800'
          };
          return colors[cat] || 'bg-gray-100 text-gray-800';
        },

        get totalExpenses() {
          return this.expenses.reduce((sum, exp) => sum + (Number(exp.amount) || 0), 0);
        },

        async fetch() {
          try {
            let url = '/api/expenses';
            if (this.searchQuery || this.filterCategory) {
              const params = new URLSearchParams();
              if (this.searchQuery) params.append('q', this.searchQuery);
              if (this.filterCategory) params.append('category', this.filterCategory);
              url += '?' + params.toString();
            }
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            this.expenses = (data.data || data).sort((a, b) => new Date(b.incurred_on) - new Date(a.incurred_on));
          } catch (e) {
            console.error('Fetch error:', e);
            Swal.fire('خطأ', 'فشل تحميل المصاريف', 'error');
          }
        },

        openNew() {
          this.form = { id: null, category: '', title: '', description: '', amount: 0, incurred_on: new Date().toISOString().split('T')[0] };
          this.modalOpen = true;
        },

        edit(expense) {
          this.form = { ...expense };
          this.modalOpen = true;
        },

        closeModal() {
          this.modalOpen = false;
        },

        async save() {
          try {
            const method = this.form.id ? 'PUT' : 'POST';
            const url = this.form.id ? `/api/expenses/${this.form.id}` : '/api/expenses';
            const res = await fetch(url, {
              method,
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify(this.form)
            });

            if (!res.ok) {
              const error = await res.json();
              Swal.fire('خطأ', error.message || 'حدث خطأ عند الحفظ', 'error');
              return;
            }

            Swal.fire({
              icon: 'success',
              title: 'تم الحفظ',
              text: this.form.id ? 'تم تحديث المصروف بنجاح' : 'تم إضافة المصروف بنجاح',
              timer: 2000,
              showConfirmButton: false
            });

            this.closeModal();
            this.fetch();
          } catch (e) {
            Swal.fire('خطأ', e.message, 'error');
          }
        },

        async remove(id) {
          const result = await Swal.fire({
            title: 'هل أنت متأكد؟',
            text: "هل تريد حذف هذا المصروف؟ لا يمكن التراجع عن هذا الإجراء.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'نعم، احذف',
            cancelButtonText: 'إلغاء'
          });

          if (!result.isConfirmed) return;

          try {
            const res = await fetch(`/api/expenses/${id}`, {
              method: 'DELETE',
              credentials: 'same-origin',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            });

            if (!res.ok) {
              Swal.fire('خطأ', 'فشل حذف المصروف', 'error');
              return;
            }

            Swal.fire('تم الحذف!', 'تم حذف المصروف بنجاح.', 'success');
            this.fetch();
          } catch (e) {
            Swal.fire('خطأ', e.message, 'error');
          }
        },

        init() {
          this.fetch();
        }
      }
    }
  </script>
@endsection