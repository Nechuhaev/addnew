<?php

namespace App\Http\Resources\Ad;

use Illuminate\Http\Resources\Json\JsonResource;

class Region extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cities' => City::collection($this->cities)
        ];
    }
}
