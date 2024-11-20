<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marker extends Model
{
    protected $fillable = ['quarantine', 'commodity', 'disease', 'information', 'color', 'date_found', 'latitude', 'longitude', 'photo_path'];
}
