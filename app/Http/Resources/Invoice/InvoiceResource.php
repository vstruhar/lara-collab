<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'client_company' => $this->clientCompany,
            'created_by_user' => $this->createdByUser,
            'status' => $this->status,
            'type' => $this->type,
            'amount' => $this->amount,
            'amount_with_tax' => $this->amount_with_tax,
            'hourly_rate' => $this->hourly_rate,
            'due_date' => $this->due_date,
            'note' => $this->note,
            'filename' => $this->filename,
            'created_at' => $this->created_at,
        ];
    }
}
