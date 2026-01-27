@extends('layouts.app')

@section('content')
  <div x-data="appointmentsApp()" class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h2 class="text-xl font-bold">المواعيد</h2>
      </div>
      <div class="flex items-center gap-2">
        <button @click="openNew()" class="px-3 py-2 bg-indigo-600 text-white rounded">حجز موعد</button>
      </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-slate-100">
      <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2">
                <input x-model="filters.q" @input.debounce.500ms="fetch" placeholder="ابحث باسم المريض أو الطبيب" class="px-3 py-2 border rounded w-64" />
          <select x-model="filters.doctor_id" @change="fetch" class="px-3 py-2 border rounded">
            <option value="">كل الأطباء</option>
            <template x-for="d in doctors"><option :value="d.id" x-text="d.name"></option></template>
          </select>
          <select x-model="filters.patient_id" @change="fetch" class="px-3 py-2 border rounded">
            <option value="">كل المرضى</option>
            <template x-for="p in patients"><option :value="p.id" x-text="p.first_name + ' ' + (p.last_name||'')"></option></template>
          </select>
          <select x-model="filters.status" @change="fetch" class="px-3 py-2 border rounded">
            <option value="">كل الحالات</option>
            <option value="scheduled">مجدول</option>
            <option value="confirmed">مؤكد</option>
            <option value="completed">مكتمل</option>
            <option value="cancelled">ملغي</option>
            <option value="no_show">لم يحضر</option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <label class="text-sm text-slate-500">فرز:</label>
          <select x-model="filters.sort" @change="fetch" class="px-3 py-2 border rounded">
            <option value="start_at">التاريخ</option>
            <option value="status">الحالة</option>
          </select>
          <select x-model="filters.order" @change="fetch" class="px-3 py-2 border rounded">
            <option value="asc">تصاعدي</option>
            <option value="desc">تنازلي</option>
          </select>
        </div>
      </div>

      <table class="w-full text-sm text-right">
            <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 cursor-pointer hover:bg-slate-100" @click="sortBy('start_at')">الموعد <span x-show="sort==='start_at'" x-text="order==='asc'?'↑':'↓'"></span></th>
                    <th class="px-6 py-4">المريض</th>
                    <th class="px-6 py-4">الطبيب</th>
                    <th class="px-6 py-4">الحالة</th>
                    <th class="px-6 py-4">الإجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="a in appointments" :key="a.id">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4" dir="ltr" class="text-right">
                            <span x-text="new Date(a.start_at).toLocaleDateString('en-GB')"></span>
                            <span class="text-xs text-slate-500 block" x-text="new Date(a.start_at).toLocaleTimeString('short')"></span>
                        </td>
                        <td class="px-6 py-4 font-semibold" x-text="a.patient?.first_name + ' ' + (a.patient?.last_name||'')"></td>
                        <td class="px-6 py-4" x-text="a.doctor?.name || '-'"></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-700': a.status === 'completed' || a.status === 'confirmed',
                                    'bg-red-100 text-red-700': a.status === 'cancelled' || a.status === 'no_show',
                                    'bg-blue-100 text-blue-700': a.status === 'scheduled'
                                }"
                                x-text="statusLabels[a.status] || a.status">
                            </span>
                        </td>
                        <td class="px-6 py-4 flex gap-2">
                          <button @click="edit(a)" class="text-indigo-600 hover:text-indigo-800 text-xs px-2 py-1 border border-indigo-100 rounded bg-indigo-50">تعديل</button>
                          <button @click="updateStatus(a, 'completed')" x-show="a.status !== 'completed'" class="text-green-600 hover:text-green-800 text-xs px-2 py-1 border border-green-100 rounded bg-green-50">إكمال</button>
                          <button @click="updateStatus(a, 'cancelled')" x-show="a.status !== 'cancelled'" class="text-red-600 hover:text-red-800 text-xs px-2 py-1 border border-red-100 rounded bg-red-50">إلغاء</button>
                          <button @click="deleteAppointment(a.id)" class="text-red-500 hover:text-red-700 text-xs px-2 py-1 border border-red-100 rounded bg-red-50">حذف</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="appointments.length === 0">
                    <td colspan="5" class="p-8 text-center text-slate-500">لا توجد مواعيد مسجلة.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div @click.away="closeModal" class="bg-white w-full max-w-2xl p-6 rounded-lg shadow">
        <h3 class="text-lg font-bold mb-4">حجز / تعديل موعد</h3>
        <form @submit.prevent="save()" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <!-- Patient Search -->
            <div>
                <input list="ptList" x-model="patientSearch" @input="onPatientInput" placeholder="ابحث عن مريض..." class="px-3 py-2 border rounded w-full" />
                <datalist id="ptList">
                    <template x-for="p in patients"><option :value="p.first_name + ' ' + (p.last_name||'')" :data-id="p.id"></option></template>
                </datalist>
                <input type="hidden" x-model="form.patient_id">
            </div>

            <!-- Date & Time (single field) -->
            <div>
              <label class="sr-only">تاريخ ووقت الموعد</label>
              <input type="datetime-local" x-model="form.start_at" @input="onStartAtChange" class="px-3 py-2 border rounded w-full" />
            </div>

            <!-- Doctor Search -->
            <div>
                <input list="drList" x-model="doctorSearch" @input="onDoctorInput" placeholder="ابحث عن طبيب..." class="px-3 py-2 border rounded w-full" />
                <datalist id="drList">
                    <template x-for="d in doctors"><option :value="d.name" :data-id="d.id"></option></template>
                </datalist>
                <input type="hidden" x-model="form.doctor_id">
            </div>
          </div>
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-500">حدد الوقت والطبيب ثم اضغط حفظ</div>
            <div class="flex gap-2">
              <button type="button" @click="closeModal" class="px-3 py-2 border rounded">إلغاء</button>
              <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">حفظ</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function appointmentsApp(){
      return {
        appointments: [], patients: [], doctors: [], modalOpen:false, patientSearch:'', doctorSearch:'', 
        sort: 'start_at', order: 'asc',
        statusLabels: { 'scheduled': 'مجدول', 'confirmed': 'مؤكد', 'completed': 'مكتمل', 'cancelled': 'ملغي', 'no_show': 'لم يحضر' },
        form:{id:null,patient_id:'',doctor_id:'',start_at:'',end_at:'',status:'scheduled',notes:''},
        
        filters: { q:'', doctor_id:'', patient_id:'', status:'', sort:'start_at', order:'asc' },

        async fetch(){
          const params = new URLSearchParams();
          if(this.filters.q) params.append('q', this.filters.q);
          if(this.filters.doctor_id) params.append('doctor_id', this.filters.doctor_id);
          if(this.filters.patient_id) params.append('patient_id', this.filters.patient_id);
          if(this.filters.status) params.append('status', this.filters.status);
          if(this.filters.sort) params.append('sort', this.filters.sort);
          if(this.filters.order) params.append('order', this.filters.order);

          const res = await fetch('/api/appointments?'+params.toString(), { credentials: 'same-origin' });
          let data = (await res.json());
          // handle paginated response
          let list = data.data ?? data;
          
            this.appointments = list;
          
            if(this.patients.length === 0) {
              const p = await fetch('/api/patients', { credentials: 'same-origin' });
              const pjson = await p.json();
              this.patients = pjson.data ?? pjson;
            }
            if(this.doctors.length === 0) {
              const d = await fetch('/api/users', { credentials: 'same-origin' }); 
              const djson = await d.json();
              const allUsers = djson.data ?? djson;
              this.doctors = Array.isArray(allUsers) ? allUsers.filter(u=>u.role==='doctor') : [];
            }
        },
        sortBy(field){
          if(this.filters.sort === field) { this.filters.order = this.filters.order === 'asc' ? 'desc' : 'asc'; }
          else { this.filters.sort = field; this.filters.order = 'asc'; }
          this.fetch();
        },
        openNew(){ this.modalOpen = true; this.patientSearch=''; this.doctorSearch=''; this.form = { id:null,patient_id:'',doctor_id:'',start_at:'',end_at:'',status:'scheduled',notes:'' } },
        edit(a){
            this.modalOpen = true;
          const formatForInput = (dt) => {
            if(!dt) return '';
            const d = new Date(dt);
            const pad = n => n.toString().padStart(2,'0');
            return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
          };
          this.form = { ...a, patient_id: a.patient_id, doctor_id: a.doctor_id, start_at: formatForInput(a.start_at), end_at: formatForInput(a.end_at) };
            // Populate search fields
            const p = this.patients.find(x => x.id == a.patient_id);
            if(p) this.patientSearch = p.first_name + ' ' + (p.last_name||'');
            const d = this.doctors.find(x => x.id == a.doctor_id);
            if(d) this.doctorSearch = d.name;
        },
        async updateStatus(a, status){
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          Swal.fire({
            title: 'تأكيد',
            text: 'هل تريد تغيير حالة الموعد؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'نعم',
            cancelButtonText: 'إلغاء'
          }).then(async (res) => {
            if(!res.isConfirmed) return;
            const resp = await fetch(`/api/appointments/${a.id}`,{
              method:'PUT',
              credentials: 'same-origin',
              headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token}, 
              body: JSON.stringify({ status: status })
            });
            if(resp.ok){
              Swal.fire({ icon:'success', title:'تم', text:'تم تحديث الحالة', toast:true, position:'top-end', timer:2000, showConfirmButton:false });
              this.fetch();
            } else {
              Swal.fire('خطأ','تعذر تحديث الحالة','error');
            }
          });
        },
        closeModal(){ this.modalOpen = false },
        onPatientInput(e){
          const name = this.patientSearch.trim();
          const p = this.patients.find(x => (x.first_name + ' ' + (x.last_name||'')).trim() === name);
          this.form.patient_id = p ? p.id : '';
        },
        onDoctorInput(e){
          const name = this.doctorSearch.trim();
          const d = this.doctors.find(x => x.name === name);
          this.form.doctor_id = d ? d.id : '';
        },
        onStartAtChange(e){
          // when start time changes, set end_at to +30 minutes if empty or earlier
          if(this.form.start_at){
            const start = new Date(this.form.start_at);
            const end = new Date(start.getTime() + 30*60000);
            const pad = n => n.toString().padStart(2,'0');
            const format = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            if(!this.form.end_at || new Date(this.form.end_at) <= start){
              this.form.end_at = format(end);
            }
          }
        },
        async save(){
          const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
          // basic validation
          if(!this.form.patient_id){ Swal.fire('خطأ','الرجاء اختيار مريض صالح','error'); return; }
          if(!this.form.start_at){ Swal.fire('خطأ','الرجاء اختيار وقت الموعد','error'); return; }

          if(this.form.id){
            const resp = await fetch(`/api/appointments/${this.form.id}`,{method:'PUT',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify(this.form)});
            if(resp.ok){ Swal.fire({ icon:'success', title:'تم', text:'تم تحديث الموعد', toast:true, position:'top-end', timer:2000, showConfirmButton:false }); }
            else { Swal.fire('خطأ','تعذر تحديث الموعد','error'); }
          } else {
            const res = await fetch('/api/appointments',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify(this.form)});
            if (res.ok) {
              Swal.fire({ icon:'success', title:'تم', text:'تم إنشاء الموعد', toast:true, position:'top-end', timer:2000, showConfirmButton:false });
            } else {
              console.error('Create appointment error', await res.text());
              Swal.fire('خطأ','تعذر إنشاء الموعد — تفقد Console للمزيد','error');
            }
          }
          this.modalOpen = false; this.fetch();
        },

        async deleteAppointment(id){
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            Swal.fire({
                title: 'هل تريد حذف الموعد؟',
                text: 'سيتم حذف الموعد نهائياً',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then(async (r) => {
                if(!r.isConfirmed) return;
                const resp = await fetch(`/api/appointments/${id}`,{ method:'DELETE', credentials:'same-origin', headers:{'X-CSRF-TOKEN': token} });
                if(resp.ok){ Swal.fire({ icon:'success', title:'تم', text:'تم حذف الموعد', toast:true, position:'top-end', timer:2000, showConfirmButton:false }); this.fetch(); }
                else { Swal.fire('خطأ','تعذر حذف الموعد','error'); }
            });
        },
        formatDate(dt){ return dt ? new Date(dt).toLocaleString('ar-EG') : '-'; },
        init(){ this.fetch(); }
      }
    }
  </script>
@endsection
