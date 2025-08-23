<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class); // opsional, atau gunakan middleware role

        $q            = $request->input('q');
        $event        = $request->input('event');
        $actorId      = $request->input('actor_id');
        $subjectType  = $request->input('subject_type');
        $dateFrom     = $request->input('date_from');
        $dateTo       = $request->input('date_to');

        $logs = AuditLog::query()
            ->when($q, fn($w) => $w->where('description','like',"%$q%"))
            ->when($event, fn($w) => $w->where('event',$event))
            ->when($actorId, fn($w) => $w->where('actor_id',$actorId))
            ->when($subjectType, fn($w) => $w->where('subject_type',$subjectType))
            ->when($dateFrom, fn($w) => $w->whereDate('created_at','>=',$dateFrom))
            ->when($dateTo, fn($w) => $w->whereDate('created_at','<=',$dateTo))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('audit_logs.index', compact('logs'));
    }

    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog); // opsional
        return view('audit_logs.show', compact('auditLog'));
    }
}
