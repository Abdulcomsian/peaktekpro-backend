<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
        protected $emailTemplate;

          public function __construct($user, $emailTemplate = null)
        {
            // Always call parent constructor with the resource
            parent::__construct($user);

            // Store additional data
            $this->emailTemplate = $emailTemplate;
        }
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
            'role_id' => $this->role_id,
            'name' => $this->name,
            'email' => $this->email,
            'location' => $this->location,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            // Add whatever user fields you want

            'email_template' => $this->emailTemplate, // Additional data
        ];
    }
}
