@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-800">تعديل بيانات المستخدم</h2>
            <a href="{{ route('users.index') }}" class="px-4 py-2 border rounded text-slate-600 hover:bg-slate-50">عودة
                للقائمة</a>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-600 p-4 rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <form action="{{ route('users.update', $user->id) }}" method="POST"
                x-data="{ role: '{{ $user->role }}', password: '' }">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">الاسم الكامل <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">الدور الوظيفي <span
                                class="text-red-500">*</span></label>
                        <select name="role" x-model="role"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="user">موظف استقبال / مستخدم عادي</option>
                            <option value="doctor">طبيب معالج</option>
                            <option value="admin">مدير نظام (Admin)</option>
                        </select>
                    </div>

                    <!-- Doctor Specifics -->
                    <div class="grid grid-cols-2 gap-4 p-4 bg-slate-50 rounded-lg border border-slate-100"
                        x-show="role === 'doctor'" x-transition>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">نسبة العمولة (%)</label>
                            <input type="number" name="commission_pct" step="0.1"
                                value="{{ old('commission_pct', $user->commission_pct) }}"
                                class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">الراتب الشهري الثابت</label>
                            <input type="number" name="monthly_salary" step="100"
                                value="{{ old('monthly_salary', $user->monthly_salary) }}"
                                class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="w-full px-4 py-2 border rounded-lg ltr">
                    </div>

                    <!-- Password -->
                    <div class="p-4 border rounded-lg border-orange-100 bg-orange-50">
                        <h4 class="font-bold text-orange-800 mb-2 text-sm">تغيير كلمة المرور</h4>
                        <p class="text-xs text-orange-600 mb-3">اترك الخانات فارغة إذا كنت لا تريد تغيير كلمة المرور
                            الحالية.</p>

                        <div class="space-y-3">
                            <div class="relative">
                                <input type="text" name="password" x-model="password" placeholder="كلمة مرور جديدة"
                                    class="w-full px-4 py-2 border rounded-lg bg-white">
                                <button type="button" @click="password = Math.random().toString(36).slice(-8)"
                                    class="absolute left-2 top-2.5 text-xs text-indigo-600 font-bold hover:text-indigo-800">
                                    توليد
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t flex justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="px-6 py-2 border rounded-lg hover:bg-slate-50">إلغاء</a>
                        <button type="submit"
                            class="px-8 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow-lg shadow-indigo-200">
                            حفظ التغييرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection