<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\System;

class Zone extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }
}
