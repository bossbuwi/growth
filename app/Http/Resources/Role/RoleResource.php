<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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
            'role' => $this->role,
            'superuser' => $this->when($this->superuser == true, $this->superuser),
            'admin' => $this->when($this->admin == true, $this->admin),
            'user' => $this->when($this->user == true, $this->user),
            'banned' => $this->when($this->banned == true ,$this->banned),
            'usersWithRole' => count($this->users()->get())
        ];
    }
}
