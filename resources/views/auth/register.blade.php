@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
  <h2 class="text-2xl font-bold mb-4">إنشاء حساب جديد</h2>
  <form method="POST" action="/register" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm text-gray-700">الاسم الكامل</label>
      <input name="name" type="text" required class="mt-1 block w-full rounded border-gray-200" />
    </div>
    <div>
      <label class="block text-sm text-gray-700">البريد الإلكتروني</label>
      <input name="email" type="email" required class="mt-1 block w-full rounded border-gray-200" />
    </div>
    <div>
      <label class="block text-sm text-gray-700">كلمة المرور</label>
      <input name="password" type="password" required class="mt-1 block w-full rounded border-gray-200" />
    </div>
    <div>
      <label class="block text-sm text-gray-700">تأكيد كلمة المرور</label>
      <input name="password_confirmation" type="password" required class="mt-1 block w-full rounded border-gray-200" />
    </div>
    <div class="pt-4">
      <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">إنشاء حساب</button>
    </div>
  </form>
</div>
@endsection
