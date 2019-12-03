<?php

namespace App\Http\Controllers\API\Ad;

use App\AdRegion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\Ad\Region as RegionResource;

class Region extends Controller
{
    public function show($id) {
        $region = AdRegion::find($id);
        return new RegionResource($region);
    }
}
