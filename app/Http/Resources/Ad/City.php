<?php

namespace App\Http\Resources\Ad;

use Illuminate\Http\Resources\Json\JsonResource;

class City extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $full_path = $this->region->country->name . ' > ' . $this->region->name . ' > ' . $this->name;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'path' => $full_path
        ];
    }
}
