<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'color'
    ];

    protected $hidden = [
        'updated_at', 'deleted_at'
    ];

    public function tasks() {
        return $this->belongsToMany(Task::class);
    }
}
