<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasAuditLogs;

class Supplier extends Model
{
    use HasAuditLogs;
    
    protected $fillable = [
        'name','contact_person','phone','email','address','notes'
    ];
}
