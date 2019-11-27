<?php

namespace Devfaysal\BangladeshGeocode\Models;

use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    protected $guarded = [];

    public function district()
    {
        return $this->belongsTo(District::Class);
    }
}
