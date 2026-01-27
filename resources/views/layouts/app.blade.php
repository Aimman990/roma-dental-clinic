<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dental Clinic') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- CDN Fallback (Since npm is missing) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Cairo', 'Inter', 'sans-serif'],
                    },
                    colors: {
                        indigo: {
                            600: '#4F46E5',
                            700: '#4338CA',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Cairo', 'Inter', sans-serif;
        }

        /* Print Styles - Hide sidebar and mobile header */
        @media print {

            aside,
            .md\\:hidden,
            header,
            .print\\:hidden {
                display: none !important;
            }

            main {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            body {
                background: white !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Check for hidden flash messages (for non-Blade pages or future use)
            const successFlash = document.getElementById('flash-success');
            const errorFlash = document.getElementById('flash-error');

            if (successFlash) {
                Swal.fire({
                    icon: 'success',
                    title: 'نجاح',
                    text: successFlash.dataset.message,
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }

            if (errorFlash) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: errorFlash.dataset.message,
                    toast: true,
                    position: 'top-end'
                });
            }

            // Global confirm helper using SweetAlert2
            window.confirmAction = function(message, options = {}) {
                return Swal.fire(Object.assign({
                    title: 'تأكيد',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'نعم',
                    cancelButtonText: 'لا',
                    reverseButtons: true
                }, options)).then(res => res.isConfirmed);
            };

            // Intercept form submits that include data-confirm attribute
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                const msg = form.getAttribute('data-confirm');
                if (!msg) return;
                e.preventDefault();
                confirmAction(msg).then(ok => { if (ok) form.submit(); });
            }, true);

            // Intercept clicks on anchors/buttons with data-confirm-href (navigate after confirmation)
            document.addEventListener('click', function(e) {
                const el = e.target.closest && e.target.closest('[data-confirm-href]');
                if (!el) return;
                const msg = el.getAttribute('data-confirm-href');
                if (!msg) return;
                e.preventDefault();
                confirmAction(msg).then(ok => { if (ok) window.location.href = el.getAttribute('href'); });
            });
        });
    </script>
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-900 selection:bg-indigo-500 selection:text-white">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex flex-col md:flex-row">

        @auth
            <!-- Mobile Header -->
            <div
                class="md:hidden bg-white border-b border-slate-200 p-4 flex justify-between items-center sticky top-0 z-50">
                <div class="font-bold text-xl text-indigo-600 flex items-center gap-2">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span>عيادتي</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                </button>
            </div>

            <!-- Sidebar -->
            <aside :class="sidebarOpen ? 'translate-x-0' : 'translate-x-[100%] md:translate-x-0'"
                class="fixed md:sticky top-0 right-0 h-screen w-64 bg-slate-900 text-white z-40 transition-transform duration-300 ease-in-out md:block flex flex-col shadow-2xl overflow-y-auto">
                <div class="p-6 border-b border-slate-800 hidden md:flex items-center gap-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Roma Clinic"
                        class="w-12 h-12 rounded-lg bg-white object-contain p-0.5 shadow-lg">
                    <div>
                        <h1 class="font-bold text-base tracking-wide leading-tight text-white">عيادة روما</h1>
                        <p class="text-[11px] text-slate-400 font-medium">لطب وتجميل الأسنان</p>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="flex-1 py-6 px-3 space-y-1">
                    @include('layouts.navigation')
                </div>

                <!-- User Profile (Bottom) -->
                <div class="p-4 border-t border-slate-800 bg-slate-900">
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-slate-300">
                            {{ auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 1)) : '?' }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Guest' }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="/logout">
                        @csrf
                        <button
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-800 hover:bg-red-500/10 hover:text-red-400 text-slate-300 rounded-lg text-sm transition-colors duration-200 group">
                            <svg class="w-4 h-4 group-hover:stroke-red-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            <span>تسجيل خروج</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Overlay for mobile -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
                class="fixed inset-0 bg-black/50 z-30 md:hidden backdrop-blur-sm transition-opacity"></div>
        @endauth

        <!-- Main Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50">
            <!-- Top Bar (Search, Notifs) -->
            <header
                class="bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-20 px-8 py-4 flex justify-between items-center">
                <h2 class="text-xl font-bold text-slate-800">
                    @yield('header', 'لوحة التحكم')
                </h2>
                <div class="flex items-center gap-4">
                    <button class="p-2 text-slate-400 hover:text-indigo-600 transition-colors relative">
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                    </button>
                </div>
            </header>

            <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div id="flash-success" data-message="{{ session('success') }}" class="hidden"></div>
                @endif
                @if(session('error'))
                    <div id="flash-error" data-message="{{ session('error') }}" class="hidden"></div>
                @endif

                @yield('content')

            </div>
            @yield('scripts')
        </main>
    </div>
</body>

</html>