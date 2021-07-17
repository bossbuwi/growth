<?php

namespace App\Http\Resources\Rule;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RuleCollection extends ResourceCollection
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
