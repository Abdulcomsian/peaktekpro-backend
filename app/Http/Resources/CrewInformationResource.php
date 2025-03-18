<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CrewInformationResource extends JsonResource
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
            'company_job_id' => $this->company_job_id,
            'build_date' => $this->build_date,
            // 'status' => $this->status,
            'crew_name'=> $this->crew_name,
            'content'=> $this->content,

        ];
    }
}
