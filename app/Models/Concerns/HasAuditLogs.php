<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasAuditLogs
{
    public static function bootHasAuditLogs(): void
    {
        static::created(function (Model $model) {
            static::writeAudit($model, 'created');
        });

        static::updated(function (Model $model) {
            static::writeAudit($model, 'updated');
        });

        static::deleted(function (Model $model) {
            static::writeAudit($model, 'deleted');
        });

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restored(function (Model $model) {
                static::writeAudit($model, 'restored');
            });
            static::forceDeleted(function (Model $model) {
                static::writeAudit($model, 'forceDeleted');
            });
        }
    }

    protected static function writeAudit(Model $model, string $event, ?string $description = null, array $properties = []): void
    {
        try {
            $actor   = auth()->user();
            $context = app()->has('audit.context') ? app('audit.context') : [];

            [$old, $new] = [null, null];
            $props = $properties ?: [];

            if ($event === 'created') {
                $new = static::filterAttrs($model->getAttributes(), $model);

                // snapshot file awal (opsional)
                $media = static::captureMediaOnCreate($model, $new);
                if ($media) $props['media_changes'] = $media;

            } elseif ($event === 'updated') {
                $original = static::filterAttrs($model->getOriginal(),  $model);
                $current  = static::filterAttrs($model->getAttributes(), $model);

                $changedKeys = array_keys($model->getChanges());
                $ignore      = static::auditIgnoreList($model);
                $changedKeys = array_values(array_diff($changedKeys, $ignore));

                if (empty($changedKeys)) return;

                $old = Arr::only($original, $changedKeys);
                $new = Arr::only($current,  $changedKeys);

                // snapshot file jika atribut file ikut berubah
                $media = static::captureMediaDiff($model, $old, $new);
                if ($media) $props['media_changes'] = $media;

            } elseif ($event === 'deleted' || $event === 'forceDeleted') {
                $old = static::filterAttrs($model->getAttributes(), $model);

            } elseif ($event === 'restored') {
                $new = static::filterAttrs($model->getAttributes(), $model);
            }

            AuditLog::create([
                'batch_id'     => $context['batch_id'] ?? null,
                'event'        => $event,
                'subject_type' => get_class($model),
                'subject_id'   => $model->getKey(),
                'actor_id'     => $actor->id   ?? null,
                'actor_name'   => $actor->name ?? 'SYSTEM',
                'actor_role'   => $actor->role ?? null,
                'url'          => $context['url'] ?? null,
                'method'       => $context['method'] ?? null,
                'ip'           => $context['ip'] ?? null,
                'user_agent'   => $context['user_agent'] ?? null,
                'old_values'   => $old ?: null,
                'new_values'   => $new ?: null,
                'properties'   => $props ?: null,
                'description'  => $description ?? static::defaultDescription($event, $model),
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->warning('audit-log-failed: '.$e->getMessage(), ['model' => get_class($model)]);
        }
    }

    /* ===================== Helpers ===================== */

    /** Daftar kolom yang diabaikan; bisa ditambah di model via properti $auditIgnore (array). */
    protected static function auditIgnoreList(Model $model): array
    {
        $base = ['updated_at', 'remember_token', 'password', 'email_verified_at'];
        if (property_exists($model, 'auditIgnore') && is_array($model->auditIgnore)) {
            $base = array_merge($base, $model->auditIgnore);
        }
        // jangan duplikat
        return array_values(array_unique($base));
    }

    protected static function filterAttrs(array $attrs, Model $model): array
    {
        $ignore = static::auditIgnoreList($model);
        return Arr::except($attrs, $ignore);
    }

    protected static function defaultDescription(string $event, Model $model): string
    {
        $name = $model->getAttribute('nama')
            ?? $model->getAttribute('name')
            ?? ('#'.$model->getKey());

        return match ($event) {
            'created'      => "Membuat {$model->getTable()} {$name}",
            'updated'      => "Mengubah {$model->getTable()} {$name}",
            'deleted'      => "Menghapus {$model->getTable()} {$name}",
            'restored'     => "Memulihkan {$model->getTable()} {$name}",
            'forceDeleted' => "Menghapus permanen {$model->getTable()} {$name}",
            default        => ucfirst($event)." {$model->getTable()} {$name}",
        };
    }

    /* ===================== File snapshot helpers ===================== */

    /** Snapshot file saat CREATED → hanya simpan "new" jika ada. */
    protected static function captureMediaOnCreate(Model $model, array $new): array
    {
        $files = static::getAuditFiles($model);
        if (!$files) return [];

        $uuid = (string) Str::uuid();
        $out  = [];

        foreach ($files as $attr => $disk) {
            $newPath = $new[$attr] ?? null;
            if (!$newPath) continue;

            $newUrl = static::snapshotFile($disk, $newPath, $uuid, 'new', $attr);
            if ($newUrl) {
                $out[] = [
                    'attribute' => $attr,
                    'old' => null,
                    'new' => $newUrl,
                ];
            }
        }
        return $out;
    }

    /** Snapshot file saat UPDATED → simpan pasangan old/new bila berubah. */
    protected static function captureMediaDiff(Model $model, array $old, array $new): array
    {
        $files = static::getAuditFiles($model);
        if (!$files) return [];

        $uuid = (string) Str::uuid();
        $out  = [];

        foreach ($files as $attr => $disk) {
            if (!array_key_exists($attr, $old) && !array_key_exists($attr, $new)) continue;

            $oldPath = $old[$attr] ?? null;
            $newPath = $new[$attr] ?? null;

            if ($oldPath === $newPath) continue;

            $oldUrl = static::snapshotFile($disk, $oldPath, $uuid, 'old', $attr);
            $newUrl = static::snapshotFile($disk, $newPath, $uuid, 'new', $attr);

            if ($oldUrl || $newUrl) {
                $out[] = [
                    'attribute' => $attr,
                    'old' => $oldUrl,
                    'new' => $newUrl,
                ];
            }
        }
        return $out;
    }

    /** Ambil mapping atribut file dari model (jika ada). */
    protected static function getAuditFiles(Model $model): array
    {
        return (property_exists($model, 'auditFiles') && is_array($model->auditFiles))
            ? $model->auditFiles
            : [];
    }

    /**
     * Copy file ke folder snapshot dan kembalikan URL-nya.
     * - Jika value URL eksternal → langsung return.
     * - Jika file tidak ada → return value as-is (tetap tercatat).
     */
    protected static function snapshotFile(string $disk, ?string $path, string $uuid, string $kind, string $attr): ?string
    {
        if (!$path) return null;

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', '/storage/'])) {
            return $path;
        }

        if (!Storage::disk($disk)->exists($path)) {
            return $path;
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
        $filename = $attr . '-' . Str::random(6) . '.' . $ext;
        $target = "audit/{$uuid}/{$kind}/{$filename}";

        Storage::disk($disk)->copy($path, $target);

        return Storage::disk($disk)->url($target);
    }
}
