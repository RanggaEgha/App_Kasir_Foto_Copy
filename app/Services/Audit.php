<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function log(string $event, ?Model $subject = null, ?string $description = null, array $properties = []): void
    {
        try {
            $actor   = auth()->user();
            $context = app('audit.context') ?? [];

            AuditLog::create([
                'batch_id'     => $context['batch_id'] ?? null,
                'event'        => $event,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->getKey(),
                'actor_id'     => $actor->id ?? null,
                'actor_name'   => $actor->name ?? 'SYSTEM',
                'actor_role'   => $actor->role ?? null,
                'url'          => $context['url'] ?? null,
                'method'       => $context['method'] ?? null,
                'ip'           => $context['ip'] ?? null,
                'user_agent'   => $context['user_agent'] ?? null,
                'properties'   => $properties ?: null,
                'description'  => $description,
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('audit-log-manual-failed: '.$e->getMessage(), ['event' => $event]);
        }
    }
}
