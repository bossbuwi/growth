<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\Role;

class Eventtype extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
