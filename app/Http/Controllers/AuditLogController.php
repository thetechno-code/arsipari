<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a paginated listing of system audit trail logs (Admin Only).
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        // Filter by Search (description or entity_id)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('entity_id', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter by User
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by Action
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        // Date Range Filters
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $auditLogs = $query->latest('created_at')->paginate(20)->withQueryString();

        $users   = User::orderBy('name')->get();
        $actions = AuditAction::cases();

        return view('audit-logs.index', compact('auditLogs', 'users', 'actions'));
    }
}
