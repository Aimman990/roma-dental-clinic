@extends('layouts.app')

@section('content')
  <div x-data="patientsApp()" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h2 class="text-xl font-bold">المرضى</h2>
        <input x-model="query" @input.debounce="fetchPatients" placeholder="ابحث بالاسم/الهاتف" class="px-3 py-2 border rounded w-64" />
      </div>
      <div class="flex items-center gap-2">
        <button @click="openNew()" class="px-3 py-2 bg-indigo-600 text-white rounded">مريض جديد</button>
      </div>
    </div>

    <template x-if="patients.length === 0">
      <div class="text-gray-500">لا يوجد مرضى بعد.</div>
    </template>

    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full text-right min-w-[700px]">
        <thead>
          <tr class="text-sm text-gray-500 border-b">
            <th class="p-3 cursor-pointer hover:bg-slate-100" @click="sortBy('first_name')">الاسم <span x-show="sort==='first_name'" x-text="order==='asc'?'↑':'↓'"></span></th>
            <th class="p-3 cursor-pointer hover:bg-slate-100" @click="sortBy('phone')">الهاتف <span x-show="sort==='phone'" x-text="order==='asc'?'↑':'↓'"></span></th>
            <th class="p-3">البريد</th>
            <th class="p-3">أنشأ بواسطة</th>
            <th class="p-3 cursor-pointer hover:bg-slate-100" @click="sortBy('created_at')">تاريخ الإضافة <span x-show="sort==='created_at'" x-text="order==='asc'?'↑':'↓'"></span></th>
            <th class="p-3">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          <template x-for="p in patients" :key="p.id">
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3">
                <div class="font-medium" x-text="p.first_name + ' ' + (p.last_name ?? '')"></div>
              </td>
              <td class="p-3" x-text="p.phone ?? '-'">-</td>
              <td class="p-3" x-text="p.email ?? '-'">-</td>
              <td class="p-3" x-text="p.creator ? p.creator.name : '-'">-</td>
              <td class="p-3" x-text="formatDate(p.created_at)">-</td>
              <td class="p-3">
                <div class="flex gap-2 justify-end">
                  <button @click="openDetails(p)" title="عرض التفاصيل" class="px-2 py-1 text-sm border rounded">عرض</button>
                  <button @click="openEdit(p)" class="px-2 py-1 text-sm border rounded">تعديل</button>
                  <button @click="deletePatient(p.id)" class="px-2 py-1 text-sm bg-red-50 text-red-600 rounded">حذف</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      </table>
      
      <!-- Pagination -->
      <div class="p-4 border-t flex justify-between items-center bg-slate-50" x-show="pagination.last_page > 1">
          <button @click="fetchPatients(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" class="px-3 py-1 border rounded disabled:opacity-50">السابق</button>
          <span class="text-sm">صفحة <span x-text="pagination.current_page"></span> من <span x-text="pagination.last_page"></span></span>
          <button @click="fetchPatients(pagination.current_page + 1)" :disabled="!pagination.next_page_url" class="px-3 py-1 border rounded disabled:opacity-50">التالي</button>
      </div>
    </div>

    <!-- Modal -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
      <div @click.away="closeModal" class="bg-white w-full max-w-2xl p-6 rounded-lg shadow" role="document">
        <h3 id="modalTitle" class="text-lg font-bold mb-4" x-text="editing ? 'تعديل مريض' : 'مريض جديد'"></h3>
        <form @submit.prevent="savePatient" class="space-y-3" aria-label="patient-form">
          <div class="grid grid-cols-2 gap-3">
            <input x-model="form.first_name" placeholder="الاسم" required aria-required="true" aria-label="الاسم" class="px-3 py-2 border rounded" />
            <input x-model="form.last_name" placeholder="اللقب" class="px-3 py-2 border rounded" />
            <input x-model="form.phone" placeholder="الهاتف" aria-label="الهاتف" class="px-3 py-2 border rounded" />
            <input x-model="form.email" type="email" placeholder="البريد" aria-label="البريد" class="px-3 py-2 border rounded" />
          </div>
          <div>
            <textarea x-model="form.notes" rows="3" class="w-full border p-2 rounded" placeholder="ملاحظات" aria-label="ملاحظات"></textarea>
          </div>

          <div class="flex justify-between items-center">
              <div class="text-sm text-gray-500">{{ '\u200F' }} سيتم حفظ البيانات على الخادم</div>
            <div class="flex gap-2">
              <button type="button" @click="closeModal" class="px-3 py-2 border rounded">إلغاء</button>
              <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">حفظ</button>
            </div>
          </div>
        </form>
      </div>
    </div>

          <!-- Details Modal -->
          <div x-show="detailsOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center" role="dialog">
            <div @click.away="closeDetails" class="bg-white w-full max-w-3xl p-6 rounded-lg shadow" role="document">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold" x-text="detailsPatient ? (detailsPatient.first_name + ' ' + (detailsPatient.last_name||'')) : 'تفاصيل المريض'"></h3>
                <div class="flex gap-2">
                  @if(auth()->user() && auth()->user()->role === 'admin')
                    <a :href="`/invoices/create?patient_id=${detailsPatient ? detailsPatient.id : ''}`" class="px-3 py-1 bg-green-600 text-white rounded text-sm">إنشاء فاتورة</a>
                  @endif
                  <a :href="`/treatment-plans/create?patient_id=${detailsPatient ? detailsPatient.id : ''}`" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">إضافة إجراءات</a>
                  <button @click="closeDetails" class="px-3 py-1 border rounded">إغلاق</button>
                </div>
              </div>

              <div x-show="!detailsLoading">
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <div class="text-sm text-slate-600">الهاتف</div>
                    <div class="font-medium" x-text="detailsPatient.phone || '-'">-</div>
                  </div>
                  <div>
                    <div class="text-sm text-slate-600">البريد</div>
                    <div class="font-medium" x-text="detailsPatient.email || '-'">-</div>
                  </div>
                </div>

                <div class="mb-4">
                  <div class="text-sm text-slate-600">ملاحظات</div>
                  <div class="whitespace-pre-line" x-text="detailsPatient.notes || '-'">-</div>
                </div>

                <div class="mb-4">
                  <h4 class="font-bold mb-2">خطة العلاج والإجراءات</h4>
                  <template x-if="detailsPatient && detailsPatient.treatment_plans && detailsPatient.treatment_plans.length === 0">
                    <div class="text-slate-400">لا توجد خطط علاج</div>
                  </template>
                  <div class="space-y-3">
                    <template x-for="plan in detailsPatient.treatment_plans" :key="plan.id">
                      <div class="p-3 border rounded">
                        <div class="flex justify-between items-center mb-2">
                          <div>
                            <div class="font-medium" x-text="'خطة: ' + (plan.id)"></div>
                            <div class="text-xs text-slate-500" x-text="plan.notes || ''"></div>
                          </div>
                        </div>

                        <div class="space-y-2">
                          <template x-for="proc in plan.procedures" :key="proc.id">
                            <div class="flex items-center justify-between">
                              <div>
                                <div class="text-sm font-medium" x-text="proc.procedure_name"></div>
                                <div class="text-xs text-slate-500">سعر تقديري: <span x-text="proc.estimated_cost"></span></div>
                              </div>
                              <div>
                                <input type="checkbox" :value="JSON.stringify({id: proc.id, name: proc.procedure_name, price: proc.estimated_cost})" x-model="selectedProcedures">
                              </div>
                            </div>
                          </template>
                        </div>
                      </div>
                    </template>

                    <div class="mt-3 flex gap-2">
                      <button @click="createInvoiceFromProcedures()" class="px-3 py-1 bg-green-600 text-white rounded text-sm">إنشاء فاتورة من الإجراءات المحددة</button>
                      <button @click="selectAllProcedures()" class="px-3 py-1 border rounded text-sm">تحديد الكل</button>
                      <button @click="clearSelectedProcedures()" class="px-3 py-1 border rounded text-sm">مسح التحديد</button>
                    </div>
                  </div>
                </div>
              </div>

              <div x-show="detailsLoading" class="text-center py-6">جاري التحميل...</div>
            </div>
          </div>

  </div>

  <script>
    function patientsApp(){
      return {
        detailsOpen: false,
        detailsLoading: false,
        detailsPatient: null,
          selectedProcedures: [],
        patients: [],
        pagination: {},
        query: '',
        sort: 'created_at',
        order: 'desc',
        modalOpen: false,
        editing: false,
        form: { id: null, first_name: '', last_name: '', phone: '', email: '', notes: '' },
        async fetchPatients(page = 1){
          const q = this.query ? `&q=${encodeURIComponent(this.query)}` : '';
          const s = `&sort=${this.sort}&order=${this.order}`;
          const res = await fetch(`/api/patients?page=${page}${q}${s}`, { credentials: 'same-origin' });
          if (! res.ok) {
            console.error('Failed to fetch patients', await res.text());
            this.patients = [];
            return;
          }
          const data = await res.json();
          this.patients = data.data;
          this.pagination = { 
              current_page: data.current_page, 
              last_page: data.last_page, 
              next_page_url: data.next_page_url, 
              prev_page_url: data.prev_page_url 
          };
        },
        async openDetails(p){
          this.detailsOpen = true;
          this.detailsLoading = true;
          this.detailsPatient = null;
          try {
            const res = await fetch(`/api/patients/${p.id}`, { credentials: 'same-origin' });
            if(!res.ok) {
              console.error('Failed to fetch patient details', await res.text());
              this.detailsPatient = null;
              this.detailsLoading = false;
              return;
            }
            const data = await res.json();
            this.detailsPatient = data;
          } catch(e) {
            console.error(e);
            this.detailsPatient = null;
          } finally {
            this.detailsLoading = false;
          }
        },
        closeDetails(){ this.detailsOpen = false; this.detailsPatient = null; },
        selectAllProcedures(){
          this.selectedProcedures = [];
          if(!this.detailsPatient || !this.detailsPatient.treatment_plans) return;
          for(let plan of this.detailsPatient.treatment_plans){
            for(let proc of plan.procedures){
              this.selectedProcedures.push(JSON.stringify({id: proc.id, name: proc.procedure_name, price: proc.estimated_cost}));
            }
          }
        },
        clearSelectedProcedures(){ this.selectedProcedures = []; },
        createInvoiceFromProcedures(){
          if(!this.detailsPatient) return;
          if(this.selectedProcedures.length === 0){ alert('الرجاء اختيار إجراء واحد على الأقل'); return; }
          // build procedures array
          const procs = this.selectedProcedures.map(s => JSON.parse(s));
          // map to invoice items: description, quantity=1, unit_price
          const items = procs.map(p => ({ description: p.name, quantity: 1, unit_price: Number(p.price) || 0 }));
          const encoded = encodeURIComponent(JSON.stringify(items));
          window.location.href = `/invoices/create?patient_id=${this.detailsPatient.id}&procedures=${encoded}`;
        },
        sortBy(field){
            if(this.sort === field) {
                this.order = this.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = field;
                this.order = 'asc';
            }
            this.fetchPatients();
        },
        openNew(){ this.editing = false; this.form = { id:null, first_name:'', last_name:'', phone:'', email:'', notes:'' }; this.modalOpen = true; },
        openEdit(p){ this.editing = true; this.form = Object.assign({}, p); this.modalOpen = true; },
        closeModal(){ this.modalOpen = false; this.editing = false; this.form = { id:null, first_name:'', last_name:'', phone:'', email:'', notes:'' }; },

        async savePatient(){
          if(this.editing && this.form.id){
            const res = await fetch(`/api/patients/${this.form.id}`, {
              method:'PUT',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify(this.form)
            });

            if (! res.ok) {
              console.error('Update patient failed', await res.text());
              alert('فشل تحديث المريض — تفقد Console للمزيد');
              return;
            }

            this.closeModal();
            this.fetchPatients();
          } else {
            const res = await fetch('/api/patients', {
              method:'POST',
              credentials: 'same-origin',
              headers: {
                  'Content-Type':'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify(this.form)
            });

            if (! res.ok) {
              console.error('Create patient failed', await res.text());
              alert('فشل إنشاء المريض — تفقد Console للمزيد');
              return;
            }

            this.closeModal();
            this.fetchPatients();
          }
        },
        async deletePatient(id){
          if(!await confirmAction('هل أنت متأكد من حذف المريض؟')) return;
          const res = await fetch(`/api/patients/${id}`, { method:'DELETE', credentials: 'same-origin', headers:{'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')} });
          if (! res.ok) {
            console.error('Delete patient failed', await res.text());
            alert('فشل حذف المريض');
            return;
          }
          this.fetchPatients();
        },
        formatDate(dt){ return dt ? new Date(dt).toLocaleString('ar-EG') : '-'; },
        init(){ this.fetchPatients(); }
      }
    }
  </script>
@endsection
