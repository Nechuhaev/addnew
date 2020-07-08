<?php

namespace App\Http\Resources\Ad;

use App\AdCategory;
use Illuminate\Http\Resources\Json\JsonResource;

class Category extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $parent = ($this->parent_id) ? AdCategory::find($this->parent_id)->first() : null;

        if ($parent) {
            $path = $parent->name . ' > ' . $this->name;
        } else {
            $path = $this->name;
        }

        return [
            'name' => $this->name,
            'path' => $this->path,
            'id' => $this->id
        ];
    }
}
