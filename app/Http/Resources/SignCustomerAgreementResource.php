<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SignCustomerAgreementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'id' => $this->id,
            'company_job_id' => $this->company_job_id,
            'sign_pdf_url' => asset('storage/'.$this->sign_pdf_url),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
