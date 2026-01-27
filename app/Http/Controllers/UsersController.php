<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may view users');
        }
        $users = User::paginate(30);
        return view('users.index', compact('users'));
    }

    public function apiIndex(Request $request)
    {
        // Simple API for dropdowns and internal fetches
        $query = User::query();
        if ($request->has('role')) {
            $query->where('role', $request->query('role'));
        }
        // Return all users (no pagination) or pagination if requested?
        // For reports iterate, we usually need all.
        // Let's return all for now to ensure all doctors show up.
        return response()->json($query->get());
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may create users');
        }
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $password = $data['password'] ?? null;
        if (!$password && ($data['role'] ?? '') === 'doctor') {
            $password = bin2hex(random_bytes(8));
        }

        if (empty($data['email']) && ($data['role'] ?? '') === 'doctor') {
            $data['email'] = 'doc_' . time() . '_' . strtolower(preg_replace('/[^a-z]/', '', $data['name'])) . '@system.local';
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => $data['role'],
            'commission_pct' => $data['commission_pct'] ?? 0,
            'monthly_salary' => $data['monthly_salary'] ?? 0,
            'is_active' => true,
        ]);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'Created user: ' . $user->email,
            'route' => $request->path(),
            'payload' => json_encode($user->toArray()),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'تم إضافة المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may edit users');
        }
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may update users');
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'role' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'commission_pct' => 'nullable|numeric|min:0|max:100',
            'monthly_salary' => 'nullable|numeric|min:0',
            'is_active' => 'boolean'
        ]);

        $before = $user->getOriginal();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (isset($data['commission_pct']) && $data['commission_pct'] === null)
            $data['commission_pct'] = 0;
        if (isset($data['monthly_salary']) && $data['monthly_salary'] === null)
            $data['monthly_salary'] = 0;

        $user->update($data);

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'Updated user: ' . $user->email,
            'route' => $request->path(),
            'payload' => json_encode(['before' => $before, 'after' => $user->toArray()]),
            'ip' => $request->ip(),
        ]);

        return redirect()->route('users.index')->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function toggleStatus(User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Forbidden');
        }
        // assumes is_active column exists (migration pending)
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['is_active' => $user->is_active]);
    }

    public function destroy(User $user)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Only administrators may delete users');
        }

        $snapshot = $user->toArray();
        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id() ?? null,
            'action' => 'Deleted user: ' . ($snapshot['email'] ?? $snapshot['id']),
            'route' => request()->path(),
            'payload' => json_encode($snapshot),
            'ip' => request()->ip(),
        ]);

        return response()->json(['message' => 'deleted']);
    }
}
