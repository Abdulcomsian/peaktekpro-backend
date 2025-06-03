<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BuildDetailResource extends JsonResource
{
    protected $contractor;

    public function __construct($user, $contractor = null)
    {
        // Always call parent constructor with the resource
        parent::__construct($user);

        // Store additional data
        $this->contractor = $contractor;
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_job_id' => $this->company_job_id,
            'build_date' =>$this->build_date,
            'homeowner' => $this->homeowner,
            'homeowner_email' => $this->homeowner_email,
            // 'contractor' => $this->contractor,
            'contractor_email' => $this->contractor_email,
            'supplier' => $this->supplier,
            'supplier_email' => $this->supplier_email,
            'contractor' => $this->name,
            'subject' => $this->subject,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
