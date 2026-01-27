<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')->latest()->paginate(25);
        return response()->json($logs);
    }

    public function webIndex()
    {
        $logs = AuditLog::with('user')->latest()->take(50)->get();
        return view('audit.index', compact('logs'));
    }
}
