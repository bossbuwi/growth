<?php

namespace App\Http\Resources\System;

use Illuminate\Http\Resources\Json\JsonResource;

class SystemResource extends JsonResource
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
            'machineName' =>$this->machine->name,
            'globalPrefix' => $this->global_prefix,
            'description' => $this->description,
            'owners' => $this->owners,
            'url' => $this->url,
            'currentVersion' => $this->currentVersion()->name,
            'releaseDate' => $this->currentVersion()->release_date,
            'usernames' => $this->usernames,
            'password' => $this->password,
            'createdBy' => $this->created_by,
            'lastModifiedBy' => $this->last_modified_by,
            'deletedBy' => $this->when($this->deleted_by !== null, $this->deleted_by),
            'deletedAt' => $this->when($this->deleted_at !== null, $this->deleted_at),
            'versions' => count($this->versions()->get()),
            'activeDependents' => [
                'events' => count($this->events()->get()),
                'zones' => count($this->zones()->get())
            ],
            'trashedDependents' => [
                'events' => count($this->events()->onlyTrashed()->get())
            ]
        ];
    }
}
