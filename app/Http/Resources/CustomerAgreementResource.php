<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerAgreementResource extends JsonResource
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
            // 'sign_pdf_url' => $this->sign_pdf_url,
            'sign_pdf_url' => asset('storage/' .$this->sign_pdf_url ),

            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
