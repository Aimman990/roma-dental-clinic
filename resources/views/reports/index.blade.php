@extends('layouts.app')

@section('content')
<div x-data="reportsHub()" class="min-h-screen">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800">مركز التقارير الشامل</h2>
        
        <!-- Global Filters -->
        <div class="flex items-center gap-2 bg-white p-2 rounded shadow-sm border">
            <label class="text-sm text-gray-500 pl-2">الفترة:</label>
            <input type="date" x-model="filters.from" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <span class="text-gray-400 font-bold">-</span>
            <input type="date" x-model="filters.to" class="border rounded px-2 py-1 text-sm bg-slate-50">
            <button @click="refreshAll" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded transition-colors shadow-sm">
                تحديث
            </button>
        </div>
    </div>

    <!-- Tabs/Navigation -->
    <div class="flex border-b border-slate-200 mb-6 space-x-8 space-x-reverse">
        <button @click="activeTab = 'operations'" :class="{'border-indigo-600 text-indigo-600': activeTab === 'operations', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'operations'}" class="pb-4 px-2 font-bold text-sm border-b-2 transition-colors">
            العمليات والعيادة
        </button>
        <button @click="activeTab = 'patients'" :class="{'border-indigo-600 text-indigo-600': activeTab === 'patients', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'patients'}" class="pb-4 px-2 font-bold text-sm border-b-2 transition-colors">
            المرضى
        </button>
        <button @click="activeTab = 'doctors'" :class="{'border-indigo-600 text-indigo-600': activeTab === 'doctors', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'doctors'}" class="pb-4 px-2 font-bold text-sm border-b-2 transition-colors">
            الأطباء
        </button>
        <button @click="activeTab = 'financials'" :class="{'border-indigo-600 text-indigo-600': activeTab === 'financials', 'border-transparent text-slate-500 hover:text-slate-700': activeTab !== 'financials'}" class="pb-4 px-2 font-bold text-sm border-b-2 transition-colors">
            المعاملات المالية
        </button>
    </div>

    <!-- OPERATIONS TAB -->
    <div x-show="activeTab === 'operations'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Appointments Stats -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-lg mb-4 text-slate-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    حالة المواعيد
                </h3>
                <div class="space-y-3">
                    <template x-for="stat in opsData.appointments_breakdown" :key="stat.status">
                        <div class="flex justify-between items-center bg-slate-50 p-3 rounded">
                            <span class="text-sm font-medium capitalize" x-text="translateStatus(stat.status)"></span>
                            <span class="font-bold text-indigo-600 text-lg" x-text="stat.total"></span>
                        </div>
                    </template>
                    <div x-show="!opsData.appointments_breakdown?.length" class="text-center text-gray-400 py-4">لا توجد بيانات مواعيد</div>
                </div>
            </div>

            <!-- Lab Orders Stats -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-100">
                <h3 class="font-bold text-lg mb-4 text-slate-700 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                    طلبات المعامل
                </h3>
                <div class="space-y-3">
                    <template x-for="stat in opsData.lab_orders_breakdown" :key="stat.status">
                        <div class="flex justify-between items-center bg-slate-50 p-3 rounded">
                            <span class="text-sm font-medium capitalize" x-text="translateStatus(stat.status)"></span>
                            <span class="font-bold text-purple-600 text-lg" x-text="stat.total"></span>
                        </div>
                    </template>
                    <div x-show="!opsData.lab_orders_breakdown?.length" class="text-center text-gray-400 py-4">لا توجد طلبات معامل</div>
                </div>
            </div>
        </div>

        <!-- Inventory Alerts -->
        <div class="bg-red-50 p-6 rounded-xl border border-red-100">
            <h3 class="font-bold text-lg mb-4 text-red-700 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                تنبيهات المخزون (Low Stock)
            </h3>
            <div x-show="!opsData.inventory_alerts?.disabled && opsData.inventory_alerts?.low_stock_count > 0">
                <div class="mb-2 text-sm text-red-600 font-bold">يوجد <span x-text="opsData.inventory_alerts?.low_stock_count"></span> صنف وصل للحد الأدنى:</div>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-1">
                    <template x-for="item in opsData.inventory_alerts?.low_stock_items" :key="item.id">
                        <li>
                            <span x-text="item.name"></span> - <span class="font-bold bg-white px-1 rounded border" x-text="item.current_stock + ' ' + item.unit"></span>
                        </li>
                    </template>
                </ul>
            </div>
            <div x-show="opsData.inventory_alerts?.disabled" class="text-slate-500 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                خاصية المخزون معطلة حالياً
            </div>
            <div x-show="!opsData.inventory_alerts?.disabled && !opsData.inventory_alerts?.low_stock_count" class="text-green-600 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                المخزون في حالة جيدة.
            </div>
        </div>
    </div>

    <!-- PATIENTS TAB -->
    <div x-show="activeTab === 'patients'" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sidebar / List -->
            <div class="col-span-1 bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">بحث عن مريض</label>
                    <div class="relative">
                        <select x-model="selectedPatientId" @change="fetchPatientDetails" class="w-full border rounded p-2 text-sm">
                            <option value="">-- اختر مريض للتقرير المفصل --</option>
                            <template x-for="p in allPatients" :key="p.id">
                                <option :value="p.id" x-text="p.first_name + ' ' + (p.last_name||'')"></option>
                            </template>
                        </select>
                    </div>
                </div>
                
                <div class="border-t pt-4">
                    <h4 class="font-bold text-slate-700 mb-3">أكثر المرضى دخلاً (Top 10)</h4>
                    <ul class="space-y-2">
                        <template x-for="(p, idx) in patientsData.top_patients" :key="p.id">
                            <li class="flex justify-between items-center text-sm p-2 hover:bg-slate-50 rounded cursor-pointer" @click="selectedPatientId=p.id; fetchPatientDetails()">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold" x-text="idx+1"></span>
                                    <span x-text="p.first_name + ' ' + (p.last_name||'')"></span>
                                </div>
                                <span class="font-bold text-green-600" x-text="formatMoney(p.total_paid)"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Detail View -->
            <div class="col-span-1 lg:col-span-2">
                <div x-show="!selectedPatientId" class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl h-64 flex flex-col items-center justify-center text-slate-400">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>اختر مريضاً لعرض سجله الكامل</span>
                </div>

                <div x-show="selectedPatientId && patientDetails" class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50 flex justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-800" x-text="patientDetails?.data?.first_name + ' ' + (patientDetails?.data?.last_name||'')"></h3>
                            <p class="text-sm text-slate-500" x-text="'تاريخ التسجيل: ' + new Date(patientDetails?.data?.created_at).toLocaleDateString('ar-SA')"></p>
                        </div>
                        <div class="text-left space-y-1">
                            <div class="text-xs text-slate-500">مجموع المدفوعات</div>
                            <div class="font-bold text-green-600 text-lg" x-text="formatMoney(patientDetails?.stats?.total_paid)"></div>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <div class="text-xs text-blue-600 font-bold mb-1">الزيارات</div>
                                <div class="text-xl font-black text-blue-800" x-text="patientDetails?.stats?.total_visits"></div>
                            </div>
                            <div class="p-3 bg-red-50 rounded-lg">
                                <div class="text-xs text-red-600 font-bold mb-1">المبلغ المستحق</div>
                                <div class="text-xl font-black text-red-800" x-text="formatMoney(patientDetails?.stats?.total_due)"></div>
                            </div>
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-600 font-bold mb-1">آخر زيارة</div>
                                <div class="text-sm font-bold text-gray-800" x-text="patientDetails?.stats?.last_visit || '-'"></div>
                            </div>
                        </div>

                        <!-- recent Appointments -->
                        <div>
                            <h4 class="font-bold text-sm text-slate-700 mb-2 border-b pb-1">سجل المواعيد</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-right text-sm">
                                    <thead class="text-slate-500 bg-slate-50"><tr><th class="p-2">التاريخ</th><th class="p-2">الحالة</th><th class="p-2">ملاحظات</th></tr></thead>
                                    <tbody>
                                        <template x-for="apt in patientDetails?.data?.appointments" :key="apt.id">
                                            <tr class="border-b">
                                                <td class="p-2" x-text="apt.appointment_date"></td>
                                                <td class="p-2"><span class="px-2 py-0.5 rounded bg-gray-100 text-xs" x-text="translateStatus(apt.status)"></span></td>
                                                <td class="p-2 text-gray-500 truncate max-w-xs" x-text="apt.notes || '-'"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DOCTORS TAB -->
    <div x-show="activeTab === 'doctors'" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <table class="w-full text-right">
                <thead class="bg-slate-50 text-slate-500 text-sm font-semibold">
                    <tr>
                        <th class="p-4">الطبيب</th>
                        <th class="p-4">عدد المواعيد (في الفترة)</th>
                        <th class="p-4">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <template x-for="doc in doctorsData.doctors" :key="doc.id">
                        <tr class="hover:bg-slate-50">
                            <td class="p-4 font-medium text-slate-800" x-text="doc.name"></td>
                            <td class="p-4 font-mono text-indigo-600 font-bold" x-text="doc.appointments_count"></td>
                            <td class="p-4">
                                <button @click="viewDoctorStats(doc.id)" class="px-3 py-1 text-xs border border-indigo-200 text-indigo-600 rounded hover:bg-indigo-50">تقرير مفصل</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Detailed Doctor Modal overlay can be added here or reuse logic, for brevity kept simple alert or inline expansion could be better but let's stick to simple -->
    </div>

    <!-- FINANCIALS TAB -->
    <div x-show="activeTab === 'financials'" class="space-y-6">
        <div class="flex justify-end gap-2 mb-4">
            <button @click="printReport('financials-table')" class="bg-gray-800 text-white px-3 py-1 text-sm rounded flex items-center gap-2 hover:bg-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                طباعة الجدول
            </button>
            <button @click="exportCSV('financials-table')" class="bg-green-600 text-white px-3 py-1 text-sm rounded flex items-center gap-2 hover:bg-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                تصدير Excel/CSV
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden" id="financials-table">
            <div class="p-4 bg-slate-50 border-b font-bold text-slate-700">سجل المصروفات والمسحوبات والمعامل</div>
            <table class="w-full text-right text-sm">
                <thead class="bg-slate-100 text-slate-600">
                    <tr>
                        <th class="p-3">التاريخ</th>
                        <th class="p-3">النوع</th>
                        <th class="p-3">الوصف / المستفيد</th>
                        <th class="p-3">المبلغ</th>
                        <th class="p-3">ملاحظات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Expenses -->
                    <template x-for="exp in financialsData.expenses" :key="'exp-'+exp.id">
                        <tr class="hover:bg-red-50/30">
                            <td class="p-3" x-text="exp.incurred_on"></td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-red-100 text-red-800 text-xs">مصروف</span> <span class="text-xs text-gray-500" x-text="translateStatus(exp.category)"></span></td>
                            <td class="p-3 font-medium" x-text="exp.title"></td>
                            <td class="p-3 font-bold text-red-600" x-text="'- ' + formatMoney(exp.amount)"></td>
                            <td class="p-3 text-gray-400 text-xs truncate max-w-xs" x-text="exp.description || '-'"></td>
                        </tr>
                    </template>
                    <!-- Withdrawals -->
                    <template x-for="wd in financialsData.withdrawals" :key="'wd-'+wd.id">
                        <tr class="hover:bg-orange-50/30">
                            <td class="p-3" x-text="new Date(wd.created_at).toLocaleDateString('ar-SA')"></td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-orange-100 text-orange-800 text-xs">سحب طبيب</span></td>
                            <td class="p-3 font-medium" x-text="wd.user?.name"></td>
                            <td class="p-3 font-bold text-orange-600" x-text="'- ' + formatMoney(wd.amount)"></td>
                            <td class="p-3 text-gray-400 text-xs truncate max-w-xs" x-text="wd.note || '-'"></td>
                        </tr>
                    </template>
                    <!-- Lab Costs -->
                    <template x-for="lc in financialsData.lab_costs" :key="'lc-'+lc.id">
                        <tr class="hover:bg-purple-50/30">
                            <td class="p-3" x-text="new Date(lc.created_at).toLocaleDateString('ar-SA')"></td>
                            <td class="p-3"><span class="px-2 py-0.5 rounded bg-purple-100 text-purple-800 text-xs">معمل خارجي</span></td>
                            <td class="p-3 font-medium" x-text="lc.lab_name"></td>
                            <td class="p-3 font-bold text-purple-600" x-text="'- ' + formatMoney(lc.cost)"></td>
                            <td class="p-3 text-gray-400 text-xs truncate max-w-xs" x-text="lc.order_details || '-'"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function reportsHub() {
    return {
        activeTab: 'operations',
        filters: {
            from: '{{ now()->startOfMonth()->format('Y-m-d') }}',
            to: '{{ now()->format('Y-m-d') }}'
        },
        opsData: {},
        patientsData: {},
        doctorsData: {},
        financialsData: { expenses: [], withdrawals: [], lab_costs: [] },
        allPatients: [],
        
        selectedPatientId: '',
        patientDetails: null,

        async init() {
            // Load patient list for dropdown
            let pRes = await fetch('/api/patients', {credentials: 'same-origin'});
            let pData = await pRes.json();
            this.allPatients = pData.data || pData;

            await this.refreshAll();
        },

        async refreshAll() {
            const qs = `?from=${this.filters.from}&to=${this.filters.to}`;
            
            // 1. Operations
            fetch(`/api/reports/ops/operations${qs}`, {credentials: 'same-origin'})
                .then(r => r.json()).then(d => this.opsData = d);

            // 2. Patients General
            fetch(`/api/reports/ops/patients${qs}`, {credentials: 'same-origin'})
                .then(r => r.json()).then(d => this.patientsData = d);

            // 3. Doctors General
            fetch(`/api/reports/ops/doctors${qs}`, {credentials: 'same-origin'})
                .then(r => r.json()).then(d => this.doctorsData = d);
            
            // 4. Financials
            fetch(`/api/reports/ops/financials${qs}`, {credentials: 'same-origin'})
                .then(r => r.json()).then(d => this.financialsData = d);
                
            // Refresh detailed view if selected
            if(this.selectedPatientId) this.fetchPatientDetails();
        },

        async fetchPatientDetails() {
            if(!this.selectedPatientId) return;
            const qs = `?patient_id=${this.selectedPatientId}&from=${this.filters.from}&to=${this.filters.to}`;
            let res = await fetch(`/api/reports/ops/patients${qs}`, {credentials: 'same-origin'});
            this.patientDetails = await res.json();
        },

        async viewDoctorStats(id) {
            // Simple alert for now or redirect to Payouts page which has most details
            if (await confirmAction('هل تريد الذهاب لصفحة المستحقات لرؤية التفاصيل المالية لهذا الطبيب؟')) {
                window.location.href = '/finance/payouts'; // Assuming payouts is the finance page
            }
        },
        
        printReport(elementId) {
            let prtContent = document.getElementById(elementId);
            let WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');
            WinPrint.document.write('<html dir="rtl"><head><title>Print Report</title>');
            WinPrint.document.write('<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">'); // Using CDN for simplicity in print pop-up
            WinPrint.document.write('</head><body class="p-10">');
            WinPrint.document.write('<h1>' + (this.activeTab === 'financials' ? 'تقرير المعاملات المالية' : 'تقرير') + '</h1>');
            WinPrint.document.write('<p>الفترة: ' + this.filters.from + ' إلى ' + this.filters.to + '</p><hr class="my-4">');
            WinPrint.document.write(prtContent.innerHTML);
            WinPrint.document.write('</body></html>');
            WinPrint.document.close();
            WinPrint.focus();
            setTimeout(() => { WinPrint.print(); WinPrint.close(); }, 1000);
        },
        
        exportCSV(elementId) {
            // Quick and dirty CSV export from table
            let table = document.getElementById(elementId).getElementsByTagName('table')[0];
            let rows = table.rows;
            let csv = [];
            for (let i = 0; i < rows.length; i++) {
                let row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 0; j < cols.length; j++) 
                    row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
                csv.push(row.join(','));
            }
            let csvFile = new Blob(["\uFEFF" + csv.join('\n')], {type: "text/csv"}); // UTF-8 BOM
            let downloadLink = document.createElement("a");
            downloadLink.download = `report_${this.activeTab}_${this.filters.from}.csv`;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        },

        formatMoney(amount) {
            return (Number(amount) || 0).toFixed(2) + ' ر.ي';
        },

        translateStatus(status) {
            const map = {
                'scheduled': 'جدول', 'confirmed': 'مؤكد', 'completed': 'مكتمل', 'cancelled': 'ملغى',
                'pending': 'قيد الانتظار', 'sent': 'مرسل', 'received': 'مستلم', 'delivered': 'تم التسليم',
                'paid': 'مدفوع', 'unpaid': 'غير مدفوع', 'partial': 'جزئي',
                'salary': 'رواتب', 'supplies': 'مواد', 'rent': 'إيجار', 'utilities': 'مرافق', 'other': 'أخرى'
            };
            return map[status] || status;
        }
    }
}
</script>
@endsection
