<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JCFrane\ResourceScope\Concerns\HasResourceScope;

class UserResource extends JsonResource
{
    use HasResourceScope;

    /**
     * @return array<string, list<string>>
     */
    protected function scopeDefinitions(): array
    {
        return [
            'listing' => ['id', 'name', 'email', 'roles', 'created_at'],
            'detail' => ['id', 'name', 'email', 'roles', 'is_landlord', 'email_verified_at', 'created_at', 'updated_at'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->scoped([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'is_landlord' => $this->is_landlord,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
