<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkflowLog extends Model
{
    protected $fillable = [
        'workflow_name',
        'payload',
        'status',
        'response',
        'triggered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'triggered_at' => 'datetime',
    ];
}
