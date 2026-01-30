@extends('layouts.app')

@section('header', 'تسجيل الدخول')

@section('content')
<div class="min-h-[80vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-slate-50">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="h-24 w-24 rounded-2xl bg-white flex items-center justify-center shadow-lg p-2 border border-slate-100">
                <img src="{{ asset('images/logo.jpg') }}" alt="Roma Clinic Logo" class="h-full w-full object-contain">
            </div>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-900">
            تسجيل الدخول للنظام
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
             عيادة روما لطب وتجميل الأسنان 
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow-xl shadow-slate-200 border border-slate-100 sm:rounded-xl sm:px-10">
            
            @if(session('error'))
                <div class="mb-4 bg-red-50 border-r-4 border-red-500 p-4 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-red-700 font-medium">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form class="space-y-6" action="{{ route('login') }}" method="POST">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700"> البريد الإلكتروني </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700"> كلمة المرور </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                        <label for="remember_me" class="mr-2 block text-sm text-slate-900"> تذكرني </label>
                    </div>

                    <div class="text-sm">
                        <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500"> نسيت كلمة المرور؟ </a>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        تسجيل الدخول
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Hide header on login page specifically if needed, or rely on auth check in layout */
    header.sticky { display: none !important; }
</style>
@endsection
