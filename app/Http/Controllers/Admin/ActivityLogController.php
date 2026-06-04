<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->model, function ($query, $model) {
                $query->where('model', $model);
            })
            ->when($request->user, function ($query, $user) {
                $query->whereHas('user', function ($q) use ($user) {
                    $q->where('name', 'like', '%' . $user . '%')
                      ->orWhere('email', 'like', '%' . $user . '%');
                });
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}