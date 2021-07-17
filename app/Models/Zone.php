<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\EventsHistory;
use App\Models\System;


class Zone extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function eventhistory()
    {
        return $this->belongsToMany(EventsHistory::class, 'evnthist_zone', 'zone_id', 'evnthist_id');
    }

    public function system()
    {
        return $this->belongsTo(System::class);
    }
}
