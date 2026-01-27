@php
    $navClass = "flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-200 group";
    $activeClass = "bg-indigo-600 text-white shadow-md shadow-indigo-900/20";
    $inactiveClass = "text-slate-400 hover:bg-slate-800 hover:text-white";
    
    $currentRoute = Route::currentRouteName();
    
    // Helper to check active state
    $isActive = function($prefix) use ($currentRoute) {
        return str_starts_with($currentRoute ?? '', $prefix) ? true : false;
    };
@endphp

<div class="px-2 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">الرئيسية</div>

<a href="/dashboard" class="{{ $navClass }} {{ Request::is('dashboard') ? $activeClass : $inactiveClass }}">
    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
    <span>لوحة التحكم</span>
</a>

<div class="mt-6 px-2 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">إدارة العيادة</div>

<a href="{{ route('patients.index') }}" class="{{ $navClass }} {{ $isActive('patients') ? $activeClass : $inactiveClass }}">
    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
    <span>المرضى</span>
</a>

<a href="{{ route('appointments.index') }}" class="{{ $navClass }} {{ $isActive('appointments') ? $activeClass : $inactiveClass }}">
    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
    <span>المواعيد</span>
</a>

<a href="/treatment-plans" class="{{ $navClass }} {{ $isActive('treatment-plans') ? $activeClass : $inactiveClass }}">
    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
    <span>خطط العلاج</span> <span class="bg-indigo-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-auto">جديد</span>
</a>

<a href="/lab-orders" class="{{ $navClass }} {{ $isActive('lab-orders') ? $activeClass : $inactiveClass }}">
    <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
    <span>المعامل</span> <span class="bg-indigo-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-auto">جديد</span>
</a>

<!-- Inventory navigation hidden because inventory feature is disabled -->
<div class="{{ $navClass }} opacity-50 cursor-not-allowed">
    <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
    <span class="text-slate-400">المخزون (معطّل)</span>
</div>

@if(optional(auth()->user())->role === 'admin')
    <div class="mt-6 px-2 mb-2 text-xs font-semibold text-slate-500 uppercase tracking-widest">بالإدارة والمالية</div>

    <a href="{{ route('invoices.index') }}" class="{{ $navClass }} {{ $isActive('invoices') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>الفواتير</span>
    </a>

    <a href="{{ route('expenses.index') }}" class="{{ $navClass }} {{ $isActive('expenses') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span>المصروفات</span>
    </a>

    <a href="{{ route('reports.income') }}" class="{{ $navClass }} {{ $isActive('reports') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        <span>التقارير</span>
    </a>

    <a href="{{ route('reports.hub') }}" class="{{ $navClass }} {{ $isActive('reports.hub') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
        <span>التقارير الشاملة</span>
    </a>
    
    <a href="{{ route('reports.finance') }}" class="{{ $navClass }} {{ $isActive('reports.finance') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span>المستحقات المالية</span> <span class="bg-green-500 text-white text-[10px] px-1.5 py-0.5 rounded ml-auto">جديد</span>
    </a>

    <a href="{{ route('users.index') }}" class="{{ $navClass }} {{ $isActive('users') ? $activeClass : $inactiveClass }}">
        <svg class="w-5 h-5 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        <span>المستخدمين</span>
    </a>
@endif
