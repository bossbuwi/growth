<?php

namespace App\Http\Resources\Configuration;

use Illuminate\Http\Resources\Json\JsonResource;

class ConfigurationResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string
     */
    public static $wrap = null;

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
            'code' => $this->code,
            'application' => $this->app,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'currentValue' => $this->current_value,
            'defaultValue' => $this->default_value,
            'acceptedValues' => $this->accepted_values,
            'lastModifiedBy' => $this->last_modified_by,
            'lastModifiedOn' => $this->last_modified_on
        ];
    }
}
