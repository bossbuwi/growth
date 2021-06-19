<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Eventtype;
use App\Models\System;
use App\Models\Zone;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    // public $timestamps = false;
    protected $dates = ['deleted_at'];

    public function eventtypes()
    {
        return $this->belongsToMany(Eventtype::class);
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class);
    }
}
