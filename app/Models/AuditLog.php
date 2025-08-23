<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'batch_id','event','subject_type','subject_id',
        'actor_id','actor_name','actor_role',
        'url','method','ip','user_agent',
        'old_values','new_values','properties','description','created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()    { return $this->belongsTo(User::class, 'actor_id'); }
}
