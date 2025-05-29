<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
             'title'=> $this->title,
            'subject'=> $this->subject,

            'content' =>$this->content,
            'supplier_id' =>$this->supplier_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at

        ];
    }
}
