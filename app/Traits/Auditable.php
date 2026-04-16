<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn($model) => $model->writeAuditLog('created'));
        static::updated(fn($model) => $model->writeAuditLog('updated'));
        static::deleted(fn($model) => $model->writeAuditLog('deleted'));
    }

    protected function writeAuditLog(string $action): void
    {
        AuditLog::create([
            'user_id'    => Auth::id(),
            'model_type' => class_basename($this),
            'model_id'   => $this->id,
            'action'     => $action,
            'ip'         => request()->ip(),
            'changes'    => [
                'old' => $this->getOriginal(),
                'new' => $this->getDirty(),
            ],
        ]);
    }
}
