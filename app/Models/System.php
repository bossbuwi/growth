<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Event;
use App\Models\Machine;
use App\Models\Version;
use App\Models\Zone;

class System extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function versions()
    {
        return $this->hasMany(Version::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function currentVersion() {
        return $this->versions()->orderBy('release_date', 'DESC')->first();
    }
}
