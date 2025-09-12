<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'task_id', 'user_id', 'changes', 'operation_type',
    ];

    protected $hidden = [
        'updated_at'
    ];

    protected $casts = [
        'changes' => 'array'
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
