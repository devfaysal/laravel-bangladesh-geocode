<?php

namespace Devfaysal\BangladeshGeocode\Models;

use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function unions()
    {
        return $this->hasMany(Union::class);
    }
}
