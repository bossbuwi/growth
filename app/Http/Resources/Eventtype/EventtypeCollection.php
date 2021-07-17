<?php

namespace App\Http\Resources\Eventtype;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EventtypeCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'length' => count($this->collection),
            'data' => $this->collection
        ];
    }
}
