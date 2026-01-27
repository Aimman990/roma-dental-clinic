@extends('layouts.app')

@section('content')
  <div class="space-y-4">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-bold">الفواتير</h2>
      <a href="{{ route('invoices.create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">إنشاء فاتورة</a>
    </div>

      {{-- Filters & Totals --}}
      <div class="bg-white rounded shadow p-4 mb-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
          <div>
            <label class="text-xs text-slate-600">من تاريخ</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="mt-1 w-full border rounded p-2 text-sm" />
          </div>
          <div>
            <label class="text-xs text-slate-600">إلى تاريخ</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="mt-1 w-full border rounded p-2 text-sm" />
          </div>
          <div>
            <label class="text-xs text-slate-600">الطبيب</label>
            <select name="doctor_id" class="mt-1 w-full border rounded p-2 text-sm">
              <option value="">كل الأطباء</option>
              @foreach($doctors as $d)
                <option value="{{ $d->id }}" {{ request('doctor_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="text-xs text-slate-600">الحالة</label>
            <select name="status" class="mt-1 w-full border rounded p-2 text-sm">
              <option value="">كل الحالات</option>
              <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>غير مدفوعة</option>
              <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>مدفوعة جزئياً</option>
              <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوعة</option>
            </select>
          </div>
          <div class="md:col-span-4 flex items-center gap-2 mt-2">
            <div class="flex-1">
              <label class="text-xs text-slate-600">ترتيب</label>
              <select name="sort" class="mt-1 w-full border rounded p-2 text-sm">
                <option value="" {{ request('sort') == '' ? 'selected' : '' }}>الأحدث أولاً</option>
                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>الأقدم أولاً</option>
                <option value="amount_asc" {{ request('sort') == 'amount_asc' ? 'selected' : '' }}>المبلغ - تصاعدي</option>
                <option value="amount_desc" {{ request('sort') == 'amount_desc' ? 'selected' : '' }}>المبلغ - تنازلي</option>
              </select>
            </div>
            <div class="flex gap-2">
              <button type="submit" class="px-3 py-2 bg-indigo-600 text-white rounded">تطبيق</button>
              <a href="{{ route('invoices.index') }}" class="px-3 py-2 border rounded text-sm">مسح</a>
            </div>
          </div>
        </form>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="p-3 bg-slate-50 rounded">
            <div class="text-sm text-slate-600">إجمالي الفواتير</div>
            <div class="text-2xl font-bold">{{ number_format($totalInvoiced ?? 0, 2) }}</div>
          </div>
          <div class="p-3 bg-slate-50 rounded">
            <div class="text-sm text-slate-600">إجمالي المدفوع</div>
            <div class="text-2xl font-bold text-green-600">{{ number_format($totalPaid ?? 0, 2) }}</div>
          </div>
          <div class="p-3 bg-slate-50 rounded">
            <div class="text-sm text-slate-600">إجمالي المتبقي</div>
            <div class="text-2xl font-bold text-red-600">{{ number_format($totalDue ?? 0, 2) }}</div>
          </div>
        </div>
      </div>
    <div class="overflow-x-auto bg-white rounded shadow">
      <table class="w-full text-right min-w-[700px]">
        <thead>
            <tr class="text-sm text-gray-500 border-b bg-slate-50">
            <th class="p-3">رقم الفاتورة</th>
            <th class="p-3">المريض</th>
            <th class="p-3">الطبيب</th>
            <th class="p-3">الإجمالي</th>
            <th class="p-3">المدفوع</th>
            <th class="p-3">المتبقي</th>
            <th class="p-3">الحالة</th>
            <th class="p-3">إجراءات</th>
          </tr>
        </thead>
        <tbody>
          @forelse($invoices as $inv)
            <tr class="border-b hover:bg-gray-50">
              <td class="p-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
              <td class="p-3 font-semibold">{{ $inv->patient->name ?? '-' }}</td>
              <td class="p-3 text-sm text-gray-600">{{ $inv->doctor->name ?? '-' }}</td>
              <td class="p-3 font-bold">{{ number_format($inv->total, 2) }}</td>
              <td class="p-3 text-green-600">{{ number_format($inv->payments->sum('amount'), 2) }}</td>
              <td class="p-3 text-red-600">{{ number_format($inv->remaining, 2) }}</td>
              <td class="p-3">
                  <span class="px-2 py-1 rounded text-xs {{ $inv->status == 'paid' ? 'bg-green-100 text-green-800' : ($inv->status == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                      {{ $inv->status }}
                  </span>
              </td>
              <td class="p-3 flex gap-2 justify-end">
                  <a href="{{ route('invoices.show', $inv->id) }}" class="px-2 py-1 text-xs border rounded hover:bg-slate-50 text-indigo-600">عرض</a>
                  <a href="{{ route('invoices.edit', $inv->id) }}" class="px-2 py-1 text-xs border rounded hover:bg-slate-50 text-gray-600">تعديل</a>
                    <form action="{{ route('invoices.destroy', $inv->id) }}" method="POST" data-confirm="حذف الفاتورة؟">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="px-2 py-1 text-xs border rounded hover:bg-red-50 text-red-600">حذف</button>
                  </form>
              </td>
            </tr>
          @empty
            <tr>
                <td colspan="8" class="p-8 text-center text-slate-500">لا توجد فواتير مسجلة.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      
      <div class="p-4">
          {{ $invoices->links() }}
      </div>
    </div>
  </div>
@endsection
