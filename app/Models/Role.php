<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Eventtype;
use App\Models\User;

class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'superuser' => 'boolean',
        'admin' => 'boolean',
        'user' => 'boolean',
        'banned' => 'boolean',
    ];

    public function eventtypes()
    {
        return $this->belongsToMany(Eventtype::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
