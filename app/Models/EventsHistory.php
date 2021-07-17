<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\Eventtype;
use App\Models\System;
use App\Models\Zone;


class EventsHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'eventshistory';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventtypes()
    {
        return $this->belongsToMany(Eventtype::class, 'evnthist_evnttype', 'evnthist_id', 'evnttype_id');
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }

    public function zones()
    {
        return $this->belongsToMany(Zone::class, 'evnthist_zone', 'evnthist_id', 'zone_id');
    }
}
