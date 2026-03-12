<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionnaireItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
            'required' => $this->required,
            'order' => $this->order,
            'properties' => $this->properties,
            'choices' => QuestionnaireChoiceResource::collection($this->whenLoaded('choices')),
        ];
    }
}
