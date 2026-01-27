@extends('layouts.app')

@section('header', 'إدارة المخزون')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div class="flex gap-4">
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 block">إجمالي الأصناف</span>
                <span class="font-bold text-lg">{{ $totalItems }}</span>
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 block">منخفض المخزون</span>
                <span class="font-bold text-lg text-red-600">{{ $lowStockItems }}</span>
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 block">قيمة المخزون</span>
                <span class="font-bold text-lg text-green-600">{{ number_format($totalValue, 2) }}</span>
            </div>
        </div>
        <a href="{{ route('inventory.create') }}"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>إضافة صنف</span>
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-right">
                <thead class="bg-slate-50 text-slate-600 font-medium border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">اسم الصنف</th>
                        <th class="px-6 py-4">التصنيف</th>
                        <th class="px-6 py-4">الكمية الحالية</th>
                        <th class="px-6 py-4">القيمة</th>
                        <th class="px-6 py-4">الحالة</th>
                        <th class="px-6 py-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $item->name }}</div>
                                <div class="text-xs text-slate-400">{{ $item->sku ?? '--' }}</div>
                            </td>
                            <td class="px-6 py-4">{{ $item->category->name ?? 'عام' }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-bold {{ $item->current_stock <= $item->min_stock_level ? 'text-red-600' : 'text-slate-700' }}">
                                        {{ $item->current_stock }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $item->unit }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                {{ number_format($item->cost_per_unit, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($item->current_stock <= 0)
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">نفذت الكمية</span>
                                @elseif($item->current_stock <= $item->min_stock_level)
                                    <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded text-xs">منخفض</span>
                                @else
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">متوفر</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 flex items-center gap-2">
                                <!-- Adjustment Modal Trigger (Basic Implementation using Alpine) -->
                                <div x-data="{ open: false }">
                                    <button @click="open = true" class="text-blue-600 hover:text-blue-800 p-1"
                                        title="تعديل الكمية">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>

                                    <!-- Modal -->
                                    <div x-show="open"
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                                        x-cloak>
                                        <div class="bg-white rounded-xl shadow-xl p-6 w-96 max-w-full m-4"
                                            @click.away="open = false">
                                            <h3 class="text-lg font-bold mb-4">تعديل المخزون: {{ $item->name }}</h3>
                                            <form action="{{ route('inventory.adjustment', $item) }}" method="POST">
                                                @csrf
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium mb-1">نوع العملية</label>
                                                    <div class="flex gap-4">
                                                        <label class="flex items-center gap-2">
                                                            <input type="radio" name="type" value="add" class="text-indigo-600"
                                                                checked>
                                                            <span>إضافة (+)</span>
                                                        </label>
                                                        <label class="flex items-center gap-2">
                                                            <input type="radio" name="type" value="subtract"
                                                                class="text-red-600">
                                                            <span>سحب (-)</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium mb-1">الكمية</label>
                                                    <input type="number" name="quantity"
                                                        class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                                        required min="1">
                                                </div>
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium mb-1">ملاحظات</label>
                                                    <textarea name="notes"
                                                        class="w-full border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                                        rows="2"></textarea>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="open = false"
                                                        class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg">إلغاء</button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">حفظ</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <a href="{{ route('inventory.edit', ['inventory' => $item->id]) }}"
                                    class="text-slate-500 hover:text-indigo-600 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                                <button type="button" onclick="confirmDeleteInventory({{ $item->id }})"
                                    class="text-slate-400 hover:text-red-500 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                                <form id="delete-form-{{ $item->id }}"
                                    action="{{ route('inventory.destroy', ['inventory' => $item->id]) }}" method="POST"
                                    style="display: none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                لا يوجد أصناف مخزنة حالياً. ابدأ بإضافة أصناف جديدة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Define function globally on window object to ensure access from onclick
        window.confirmDeleteInventory = function (id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "لن تتمكن من استرجاع هذا الصنف!",
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
    </script>
@endsection