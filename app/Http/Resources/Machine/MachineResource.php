<?php

namespace App\Http\Resources\Machine;

use Illuminate\Http\Resources\Json\JsonResource;

class MachineResource extends JsonResource
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
            'name' => $this->name,
            'createdBy' => $this->created_by,
            'lastModifiedBy' => $this->last_modified_by,
            'deletedBy' => $this->when($this->deleted_by !== null, $this->deleted_by),
            'deletedAt' => $this->when($this->deleted_at !== null, $this->deleted_at),
            'dependents' => [
                'systems' => count($this->systems()->get())
            ]
        ];
    }
}
