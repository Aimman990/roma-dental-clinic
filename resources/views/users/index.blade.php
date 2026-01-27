@extends('layouts.app')

@section('content')
  <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">إدارة المستخدمين</h2>
          <div class="text-sm text-gray-500">إنشاء وتحرير صلاحيات المستخدمين، ومراجعة نشاط التغييرات.</div>
        </div>
        <div>
          <a href="{{ route('users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded shadow hover:brightness-110 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            مستخدم جديد
          </a>
        </div>
      </div>

      <!-- Alert handled by SweetAlert2 globally, but keeping fallback just in case -->
      @if(session('success'))
        <div class="hidden" id="flash-success" data-message="{{ session('success') }}"></div>
      @endif
      @if(session('error'))
        <div class="hidden" id="flash-error" data-message="{{ session('error') }}"></div>
      @endif

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
          <div class="overflow-x-auto">
            <table class="w-full text-right">
              <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                <tr>
                  <th class="p-3">الاسم</th>
                  <th class="p-3">الدور</th>
                  <th class="p-3">الحالة</th>
                  <th class="p-3">المالية</th>
                  <th class="p-3">إجراءات</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                  <tr class="hover:bg-slate-50 transition-colors">
                    <td class="p-3">
                      <div class="font-bold text-slate-800">{{ $u->name }}</div>
                      <div class="text-xs text-slate-500">{{ $u->email }}</div>
                    </td>
                    <td class="p-3">
                      @php
                        $roleColors = [
                          'admin' => 'bg-purple-100 text-purple-700',
                          'doctor' => 'bg-blue-100 text-blue-700',
                          'user' => 'bg-slate-100 text-slate-700'
                        ];
                      @endphp
                      <span class="px-2 py-1 rounded text-xs font-semibold {{ $roleColors[$u->role] ?? 'bg-gray-100' }}">
                          {{ strtoupper($u->role) }}
                      </span>
                    </td>
                    <td class="p-3">
                      <button onclick="toggleUserStatus({{ $u->id }})" 
                          id="status-btn-{{ $u->id }}"
                          class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none {{ $u->is_active ? 'bg-green-500' : 'bg-gray-200' }}">
                          <span id="status-dot-{{ $u->id }}" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $u->is_active ? 'translate-x-1' : 'translate-x-6' }}"></span>
                      </button>
                      <div id="status-text-{{ $u->id }}" class="text-[10px] text-gray-500 mt-1">{{ $u->is_active ? 'نشط' : 'موقف' }}</div>
                    </td>
                    <td class="p-3 text-xs">
                      @if($u->role === 'doctor')
                        <div>عمولة: {{ $u->commission_pct }}%</div>
                        <div>راتب: {{ $u->monthly_salary }}</div>
                      @else
                        <span class="text-gray-300">-</span>
                      @endif
                    </td>
                    <td class="p-3">
                      <div class="flex gap-2 justify-end">
                        <a href="{{ route('users.edit', $u->id) }}"
                          class="px-2 py-1 text-xs border border-indigo-200 text-indigo-600 rounded hover:bg-indigo-50">تعديل</a>

                        <button type="button" onclick="confirmDelete({{ $u->id }})" class="px-2 py-1 text-xs border border-red-200 text-red-600 rounded hover:bg-red-50">حذف</button>
                        <form id="delete-form-{{ $u->id }}" action="{{ route('users.destroy', $u->id) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                      <td colspan="5" class="p-8 text-center text-slate-500">لا يوجد مستخدمين مضافين</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 lg:col-span-1">
          <h4 class="text-sm font-semibold mb-3 border-b pb-2">سجل النشاطات</h4>
          <div class="space-y-3 max-h-[500px] overflow-y-auto" id="audit-log-container">
              <div class="text-center text-gray-400 text-xs py-4">جاري التحميل...</div>
          </div>
        </div>
      </div>
    </div>

    <script>
      // Load Audits via AJAX purely for the side panel
      fetch('/api/audit/logs', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
          .then(res => res.json())
          .then(data => {
              const logs = data.data || data;
              const container = document.getElementById('audit-log-container');
              if(!logs || logs.length === 0) {
                  container.innerHTML = '<div class="text-center text-gray-400 text-xs py-4">لا يوجد نشاطات مسجلة</div>';
                  return;
              }
              let html = '';
              logs.forEach(a => {
                  html += `
                      <div class="p-2 border rounded bg-gray-50 text-xs">
                          <div class="font-bold text-indigo-700 mb-1">${a.action}</div>
                          <div class="flex justify-between text-gray-400">
                          <span>${a.user ? a.user.name : 'System'}</span>
                          <span>${new Date(a.created_at).toLocaleDateString('en-GB')}</span>
                          </div>
                      </div>
                  `;
              });
              container.innerHTML = html;
          });

      function confirmDelete(id) {
          Swal.fire({
              title: 'هل أنت متأكد؟',
              text: "لا يمكن التراجع عن حذف المستخدم!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'نعم، احذف',
              cancelButtonText: 'إلغاء'
          }).then((result) => {
              if (result.isConfirmed) {
                  document.getElementById('delete-form-' + id).submit();
              }
          })
      }

      function toggleUserStatus(id) {
          // We'll use fetch to toggle, then update UI if success
          fetch(`/users/${id}/toggle-status`, {
              method: 'POST',
              headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              }
          })
          .then(res => {
              if(!res.ok) throw new Error();
              return res.json();
          })
          .then(data => {
              // Update UI
              const isActive = data.is_active;
              const btn = document.getElementById(`status-btn-${id}`);
              const dot = document.getElementById(`status-dot-${id}`);
              const txt = document.getElementById(`status-text-${id}`);

              if(isActive) {
                  btn.classList.remove('bg-gray-200'); btn.classList.add('bg-green-500');
                  dot.classList.remove('translate-x-6'); dot.classList.add('translate-x-1');
                  txt.textContent = 'نشط';
                  Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'تم تنشيط المستخدم', showConfirmButton: false, timer: 3000 });
              } else {
                  btn.classList.remove('bg-green-500'); btn.classList.add('bg-gray-200');
                  dot.classList.remove('translate-x-1'); dot.classList.add('translate-x-6');
                  txt.textContent = 'موقف';
                  Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'تم إيقاف المستخدم', showConfirmButton: false, timer: 3000 });
              }
          })
          .catch(err => {
              Swal.fire('خطأ', 'حدث خطأ أثناء تغيير الحالة', 'error');
          });
      }
    </script>
@endsection