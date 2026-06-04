<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,editor,user'],
        ]);

        $oldRole = $user->role;

        $user->update([
            'role' => $validated['role'],
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'updated',
            'model' => 'User',
            'model_id' => $user->id,
            'title' => $user->email,
            'ip' => request()->ip(),
            'description' => 'Изменена роль пользователя: ' . $oldRole . ' → ' . $user->role,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Роль пользователя обновлена.');
    }
}