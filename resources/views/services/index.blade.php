@extends('layouts.app')

@section('content')
  <div x-data="servicesApp()" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">الخدمات الطبية</h2>
      <div class="flex items-center gap-2">
        <input x-model="searchQuery" @input.debounce="fetch()" placeholder="بحث عن خدمة..." class="px-3 py-2 border rounded" />
        <button @click="openNew()" class="px-3 py-2 bg-indigo-600 text-white rounded">إضافة خدمة</button>
      </div>
    </div>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full text-right min-w-[700px]">
        <thead>
          <tr class="text-sm text-gray-500 border-b bg-gray-50">
            <th class="p-3">الكود</th>
            <th class="p-3">اسم الخدمة</th>
            <th class="p-3">السعر</th>
            <th class="p-3">الطبيب</th>
            <th class="p-3">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="service in services" :key="service.id">
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3" x-text="service.code || '-'">-</td>
              <td class="p-3 font-medium" x-text="service.name">-</td>
              <td class="p-3" x-text="service.price.toFixed(2) + ' ر.ي'">-</td>
              <td class="p-3" x-text="service.doctor?.name || 'عام'">-</td>
              <td class="p-3">
                <div class="flex gap-2 justify-end">
                  <button @click="edit(service)" class="px-2 py-1 text-sm border rounded hover:bg-indigo-50">تعديل</button>
                  <button @click="remove(service.id)" class="px-2 py-1 text-sm border border-red-300 text-red-600 rounded hover:bg-red-50">حذف</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <template x-if="services.length === 0">
        <div class="p-6 text-center text-gray-500">لا توجد خدمات. <a href="#" @click.prevent="openNew()" class="text-indigo-600">أضف واحدة</a></div>
      </template>
    </div>

    <!-- Create/Edit modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div @click.away="closeModal" class="bg-white w-full max-w-md p-6 rounded-lg shadow">
        <h3 class="text-lg font-bold mb-4" x-text="form.id ? 'تعديل الخدمة' : 'إضافة خدمة جديدة'"></h3>
        <form @submit.prevent="save" class="space-y-3">
          <div>
            <label class="text-sm font-medium text-gray-700">الكود (اختياري)</label>
            <input x-model="form.code" placeholder="مثل: ROOT_CANAL_001" class="px-3 py-2 border rounded w-full" />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">اسم الخدمة *</label>
            <input x-model="form.name" placeholder="مثل: تنظيف الأسنان" class="px-3 py-2 border rounded w-full" required />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">الوصف (اختياري)</label>
            <textarea x-model="form.description" placeholder="وصف تفصيلي للخدمة" class="px-3 py-2 border rounded w-full" rows="2"></textarea>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">السعر *</label>
            <input x-model.number="form.price" type="number" step="0.01" min="0" placeholder="0.00" class="px-3 py-2 border rounded w-full" required />
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">الطبيب (اختياري)</label>
            <select x-model="form.doctor_id" class="px-3 py-2 border rounded w-full">
              <option value="">-- لا يوجد طبيب محدد --</option>
              <template x-for="doctor in doctors" :key="doctor.id">
                <option :value="doctor.id" x-text="doctor.name"></option>
              </template>
            </select>
          </div>
          <div class="flex justify-between gap-2 pt-4">
            <button type="button" @click="closeModal" class="flex-1 px-3 py-2 border rounded">إلغاء</button>
            <button type="submit" class="flex-1 px-3 py-2 bg-indigo-600 text-white rounded">حفظ</button>
          </div>
        </form>
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
    function servicesApp(){
      return {
        services: [],
        doctors: [],
        modalOpen: false,
        searchQuery: '',
        message: '',
        form: { id: null, code: '', name: '', description: '', price: 0, doctor_id: null },
        
        async fetch(){
          try {
            const res = await fetch('/api/services' + (this.searchQuery ? '?q=' + encodeURIComponent(this.searchQuery) : ''), { credentials: 'same-origin' });
            const data = await res.json();
            this.services = data.data || data;
          } catch (e) {
            console.error('Fetch error:', e);
          }
        },

        async fetchDoctors(){
          try {
            const res = await fetch('/api/users', { credentials: 'same-origin' });
            const data = await res.json();
            this.doctors = (data.data || data).filter(u => u.role === 'admin' || u.role === 'user');
          } catch (e) {
            console.error('Fetch doctors error:', e);
          }
        },

        openNew(){
          this.form = { id: null, code: '', name: '', description: '', price: 0, doctor_id: null };
          this.modalOpen = true;
        },

        edit(service){
          this.form = { ...service };
          this.modalOpen = true;
        },

        closeModal(){
          this.modalOpen = false;
          this.form = { id: null, code: '', name: '', description: '', price: 0, doctor_id: null };
        },

        async save(){
          try {
            const method = this.form.id ? 'PUT' : 'POST';
            const url = this.form.id ? `/api/services/${this.form.id}` : '/api/services';
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
              alert('خطأ: ' + (error.message || 'حدث خطأ عند الحفظ'));
              return;
            }

            this.message = this.form.id ? 'تم تحديث الخدمة بنجاح' : 'تم إضافة الخدمة بنجاح';
            this.closeModal();
            this.fetch();
            setTimeout(() => this.message = '', 3000);
          } catch (e) {
            alert('خطأ: ' + e.message);
          }
        },

        async remove(id){
          if (!await confirmAction('هل تريد حذف هذه الخدمة؟')) return;
          try {
            const res = await fetch(`/api/services/${id}`, {
              method: 'DELETE',
              credentials: 'same-origin',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              }
            });

            if (!res.ok) {
              alert('خطأ عند حذف الخدمة');
              return;
            }

            this.message = 'تم حذف الخدمة بنجاح';
            this.fetch();
            setTimeout(() => this.message = '', 3000);
          } catch (e) {
            alert('خطأ: ' + e.message);
          }
        },

        init(){
          this.fetch();
          this.fetchDoctors();
        }
      }
    }
  </script>
@endsection
