@extends('layouts.app')

@section('content')
    <div x-data="financialApp()">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">المستحقات المالية (Financial Entitlements)</h2>
                <p class="text-sm text-slate-500">نظام شامل: الأرباح، المصروفات، ومستحقات الأطباء.</p>
            </div>

            <div class="flex items-center gap-2 bg-white p-2 rounded shadow-sm border">
                <input type="date" x-model="dateFrom" class="border rounded px-2 py-1 text-sm bg-slate-50">
                <span class="text-gray-400 font-bold">-</span>
                <input type="date" x-model="dateTo" class="border rounded px-2 py-1 text-sm bg-slate-50">
                <button @click="fetchData()"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded transition-colors shadow-sm">
                    عرض
                </button>
            </div>
        </div>

        <!-- 1. General Financial KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Income -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between h-full">
                <div>
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-1">إجمالي المقبوضات (Income)</div>
                    <div class="text-3xl font-bold text-green-600" x-text="formatMoney(summary.income)"></div>
                </div>
                <div class="text-xs text-slate-400 mt-2">الكاش الفعلي في الصندوق</div>
            </div>

            <!-- Expenses -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between h-full">
                <div>
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-1">إجمالي المصروفات (Expenses)</div>
                    <div class="text-3xl font-bold text-red-600" x-text="formatMoney(summary.expenses?.total)"></div>
                </div>
                <div class="text-xs text-slate-400 mt-2">تشغيل + رواتب + معامل</div>
            </div>

            <!-- Net Profit -->
            <div
                class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 relative overflow-hidden flex flex-col justify-between h-full">
                <div class="absolute right-0 top-0 h-full w-1 bg-indigo-500"></div>
                <div>
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-1">صافي الربح (Net Profit)</div>
                    <div class="text-4xl font-black text-indigo-700" x-text="formatMoney(summary.net_profit)"></div>
                </div>
                <div class="text-xs text-indigo-300 mt-2 font-medium">الدخل - المصروفات</div>
            </div>

            <!-- Inventory Value (may be disabled) -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between h-full">
                <div>
                    <div class="text-xs text-slate-500 uppercase font-semibold mb-1">تكلفة مشتريات المخزون</div>
                    <div x-show="!summary.expenses?.inventory_disabled" class="text-2xl font-bold text-orange-600" x-text="formatMoney(summary.expenses?.inventory)"></div>
                    <div x-show="summary.expenses?.inventory_disabled" class="text-sm text-slate-400">الميزة معطلة حالياً</div>
                </div>
                <div class="text-xs text-slate-400 mt-2">تكلفة الاصناف الموجودة حالياً في المخزون</div>
            </div>
        </div>

        <hr class="border-slate-200 my-8">

        <!-- 2. Doctors Payouts Section -->
        <div class="mb-6">
            <h3 class="text-xl font-bold text-slate-800 mb-4">مستحقات الأطباء والسحوبات</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="doc in doctors" :key="doc.id">
                    <div
                        class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden flex flex-col group hover:shadow-md transition-shadow">
                        <div class="p-5 border-b border-gray-50 bg-slate-50 flex justify-between items-center">
                            <h3 class="font-bold text-lg text-slate-700" x-text="doc.name"></h3>
                            <span class="text-xs bg-white border px-2 py-1 rounded text-slate-500 font-mono"
                                x-text="'عمولة: ' + (doc.commission_pct||0) + '%'"></span>
                        </div>

                        <div class="p-5 space-y-3 flex-1">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">إجمالي الفواتير</span>
                                <span class="font-medium font-mono" x-text="formatMoney(doc.report?.total_invoiced)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">المبلغ المحصل (مدفوعات)</span>
                                <span class="font-medium font-mono"
                                    x-text="formatMoney(doc.report?.total_collected)"></span>
                            </div>
                            <div class="border-t border-dashed my-2"></div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">العمولة المكتسبة (من المحصل)</span>
                                <span class="font-bold text-green-600 font-mono"
                                    x-text="formatMoney(doc.report?.commission_earned)"></span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-slate-500">إجمالي المسحوبات</span>
                                <span class="font-bold text-red-500 font-mono"
                                    x-text="formatMoney(doc.report?.total_withdrawn)"></span>
                            </div>
                        </div>

                        <div class="p-4 bg-slate-50 border-t border-slate-100 mt-auto">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-sm font-bold text-slate-700">الرصيد الحالي</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-xl font-black font-mono" dir="ltr"
                                        :class="{'text-green-600': (doc.report?.balance_due > 0), 'text-red-600': (doc.report?.balance_due < 0)}"
                                        x-text="formatMoney(doc.report?.balance_due)"></span>
                                    <span x-show="doc.report?.balance_due < 0"
                                        class="text-[10px] text-red-500 font-bold bg-red-50 px-1 rounded">مديونية
                                        (Debt)</span>
                                </div>
                            </div>
                            <button @click="openWithdraw(doc)"
                                class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-medium shadow-sm transition-colors flex items-center justify-center gap-2">
                                <span>تسجيل سحب نقدي</span>
                                <svg class="w-4 h-4 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </button>
                            <button @click="openWithdrawals(doc)"
                                class="w-full mt-2 py-2 bg-white hover:bg-slate-100 text-indigo-600 border border-indigo-100 rounded font-medium shadow-sm transition-colors flex items-center justify-center gap-2">
                                <span>عرض السحوبات</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Withdrawals List Modal -->
        <div x-show="withdrawalsModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div @click.away="withdrawalsModal = false" class="bg-white w-full max-w-2xl p-6 rounded-xl shadow-2xl transform transition-all">
                <h3 class="font-bold text-lg mb-4 text-slate-800">سجلات السحوبات - <span x-text="activeDoc?.name"></span></h3>
                <div class="mb-4">
                    <template x-if="withdrawals.length === 0">
                        <div class="text-center text-slate-500 p-6">لا توجد سحوبات لهذا الطبيب.</div>
                    </template>
                    <template x-for="w in withdrawals" :key="w.id">
                        <div class="flex items-center justify-between gap-4 p-3 border-b">
                            <div>
                                <div class="font-medium" x-text="w.recorded_by ? w.recorded_by : ''"></div>
                                <div class="text-xs text-slate-500" x-text="new Date(w.created_at).toLocaleString()"></div>
                            </div>
                            <div class="font-mono font-bold text-red-600" x-text="formatMoney(w.amount)"></div>
                            <div class="flex gap-2">
                                <button @click="openEditWithdrawal(w)" class="text-indigo-600 px-3 py-1 rounded border">تعديل</button>
                                <button @click="deleteWithdrawal(w.id)" class="text-red-600 px-3 py-1 rounded border">حذف</button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex justify-end">
                    <button @click="withdrawalsModal = false" class="px-4 py-2 border rounded">إغلاق</button>
                </div>
            </div>
        </div>

        <!-- Edit Withdrawal Modal -->
        <div x-show="editWithdrawModal" x-cloak class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div @click.away="editWithdrawModal = false" class="bg-white w-full max-w-sm p-6 rounded-xl shadow-2xl transform transition-all">
                <h3 class="font-bold text-lg mb-4 text-slate-800">تعديل سحب نقدي</h3>
                <form @submit.prevent="submitEditWithdrawal">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">المبلغ</label>
                        <input type="number" x-model="editWithdrawForm.amount" step="0.01" min="0.01" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">ملاحظات</label>
                        <textarea x-model="editWithdrawForm.note" class="w-full border rounded px-3 py-2" rows="3"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editWithdrawModal = false" class="px-4 py-2 border rounded">إلغاء</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">حفظ</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Withdrawal Modal -->
        <div x-show="withdrawModal" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
            <div @click.away="withdrawModal = false"
                class="bg-white w-full max-w-sm p-6 rounded-xl shadow-2xl transform transition-all">
                <h3 class="font-bold text-lg mb-4 text-slate-800">تسجيل سحب نقدي</h3>
                <div class="bg-indigo-50 p-3 rounded mb-4 text-sm border border-indigo-100">
                    <div class="flex justify-between mb-1">
                        <span class="text-indigo-600">الطبيب:</span>
                        <span class="font-bold text-indigo-900" x-text="activeDoc?.name"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-indigo-600">الرصيد الحالي:</span>
                        <span class="font-mono font-bold text-indigo-900" dir="ltr"
                            x-text="formatMoney(activeDoc?.report?.balance_due)"></span>
                    </div>
                    <div x-show="activeDoc?.report?.balance_due < 0" class="mt-1 text-xs text-red-600 font-medium">
                        * هذا الطبيب عليه مديونية حالياً. السحب الجديد سيزيد المديونية.
                    </div>
                </div>

                <form @submit.prevent="submitWithdraw">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1 text-slate-700">المبلغ المسحوب</label>
                        <div class="relative">
                            <input type="number" x-model="withdrawForm.amount" step="0.01" min="1"
                                class="w-full border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500 pr-8 font-mono font-bold text-lg"
                                required>
                            <span class="absolute left-3 top-2.5 text-slate-400 text-sm">ر.ع</span>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1 text-slate-700">ملاحظات / سبب السحب</label>
                        <textarea x-model="withdrawForm.notes"
                            class="w-full border-slate-300 rounded focus:ring-indigo-500 focus:border-indigo-500" rows="3"
                            placeholder="مثال: سلفة نقدية، راتب شهر..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="withdrawModal = false"
                            class="px-4 py-2 text-slate-600 hover:bg-slate-100 rounded font-medium">إلغاء</button>
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded font-medium hover:bg-indigo-700 shadow-sm">تأكيد
                            السحب</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function financialApp() {
            return {
                dateFrom: '{{ now()->startOfYear()->format('Y-m-d') }}',
                dateTo: '{{ now()->format('Y-m-d') }}',
                summary: { income: 0, expenses: { total: 0 }, net_profit: 0 },
                doctors: [],
                withdrawModal: false,
                activeDoc: null,
                withdrawForm: { amount: '', note: '' },

                async init() {
                    await this.fetchData();
                },

                async fetchData() {
                    let qs = `?from=${this.dateFrom}&to=${this.dateTo}`;

                    // 1. Fetch Summary
                    let sRes = await fetch(`/api/reports/financial-summary${qs}`, { credentials: 'same-origin' });
                    this.summary = await sRes.json();

                    // 2. Fetch Doctors Logic
                    let uRes = await fetch('/api/users', { credentials: 'same-origin' });
                    if (!uRes.ok) {
                        console.error('Failed fetching users:', await uRes.text());
                        this.doctors = [];
                        return;
                    }
                    let uJson = await uRes.json();
                    let allUsers = uJson.data ?? uJson;
                    let docs = Array.isArray(allUsers) ? allUsers.filter(u => u.role === 'doctor') : [];

                    this.doctors = await Promise.all(docs.map(async (doc) => {
                        try {
                            let rRes = await fetch(`/api/reports/doctor/${doc.id}${qs}`, { credentials: 'same-origin' });
                            if (!rRes.ok) {
                                console.error('Failed fetching doctor report for', doc.id, await rRes.text());
                                return { ...doc, report: {} };
                            }
                            let report = await rRes.json();
                            return { ...doc, report: report.summary ?? report };
                        } catch (e) {
                            console.error('Error fetching report for', doc.id, e);
                            return { ...doc, report: {} };
                        }
                    }));
                },

                formatMoney(amount) {
                    return (Number(amount) || 0).toFixed(2) + ' ر.ي';
                },

                openWithdraw(doc) {
                    this.activeDoc = doc;
                    this.withdrawForm = { amount: '', note: '' };
                    this.withdrawModal = true;
                },

                // Withdrawals list modal
                withdrawals: [],
                withdrawalsModal: false,
                editWithdrawModal: false,
                editWithdrawForm: { id: null, amount: '', note: '' },

                async openWithdrawals(doc) {
                    this.activeDoc = doc;
                    await this.fetchWithdrawals(doc.id);
                    this.withdrawalsModal = true;
                },

                async fetchWithdrawals(userId) {
                    try {
                        let qs = `?user_id=${userId}`;
                        let res = await fetch(`/api/salaries/withdrawals${qs}`, { credentials: 'same-origin' });
                        if (!res.ok) { console.error('Failed to fetch withdrawals', await res.text()); this.withdrawals = []; return; }
                        this.withdrawals = await res.json();
                    } catch (e) { console.error(e); this.withdrawals = []; }
                },

                openEditWithdrawal(w) {
                    this.editWithdrawForm = { id: w.id, amount: w.amount, note: w.note };
                    this.editWithdrawModal = true;
                },

                async submitEditWithdrawal() {
                    const token = '{{ csrf_token() }}';
                    try {
                        let res = await fetch(`/api/salaries/withdrawals/${this.editWithdrawForm.id}`, {
                            method: 'PUT',
                            credentials: 'same-origin',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                            body: JSON.stringify({ amount: this.editWithdrawForm.amount, note: this.editWithdrawForm.note })
                        });
                        if (res.ok) {
                            Swal.fire({ icon:'success', title:'تم', text:'تم تحديث السحب', toast:true, position:'top-end', timer:2000, showConfirmButton:false });
                            this.editWithdrawModal = false;
                            await this.fetchWithdrawals(this.activeDoc.id);
                            this.fetchData();
                        } else {
                            Swal.fire('خطأ','تعذر تحديث السحب','error');
                        }
                    } catch (e) { console.error(e); Swal.fire('خطأ','خطأ في الاتصال','error'); }
                },

                async deleteWithdrawal(id) {
                    const token = '{{ csrf_token() }}';
                    try {
                        let ok = await Swal.fire({ title:'تأكيد', text:'هل تريد حذف السحب؟', icon:'warning', showCancelButton:true, confirmButtonText:'نعم', cancelButtonText:'إلغاء' });
                        if (!ok.isConfirmed) return;
                        let res = await fetch(`/api/salaries/withdrawals/${id}`, { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': token } });
                        if (res.ok) {
                            Swal.fire({ icon:'success', title:'تم', text:'تم حذف السحب', toast:true, position:'top-end', timer:2000, showConfirmButton:false });
                            await this.fetchWithdrawals(this.activeDoc.id);
                            this.fetchData();
                        } else {
                            Swal.fire('خطأ','تعذر حذف السحب','error');
                        }
                    } catch (e) { console.error(e); Swal.fire('خطأ','خطأ في الاتصال','error'); }
                },

                async submitWithdraw() {
                    if (!this.withdrawForm.amount) return;
                    try {
                        let res = await fetch('/api/salaries/withdraw', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                credentials: 'same-origin',
                                body: JSON.stringify({
                                    user_id: this.activeDoc.id,
                                    amount: this.withdrawForm.amount,
                                    note: this.withdrawForm.note,
                                    date: new Date().toISOString().split('T')[0]
                                })
                            });
                            if (res.ok) {
                                Swal.fire({ icon:'success', title:'تم', text:'تم تسجيل عملية السحب', toast:true, position:'top-end', timer:2000, showConfirmButton:false });
                                this.withdrawModal = false;
                                this.fetchData();
                            } else {
                                Swal.fire('خطأ','حدث خطأ أثناء الحفظ','error');
                            }
                    } catch (e) { console.error(e); alert('خطأ في الاتصال'); }
                }
            }
        }
    </script>
@endsection