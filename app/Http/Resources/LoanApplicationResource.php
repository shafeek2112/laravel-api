<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                                => $this->id,
            'user_id'                           => $this->user_id,
            'application_no'                    => $this->application_no,
            'loan_term'                         => $this->loan_term,
            'repayment_frequency'               => $this->repayment_frequency,
            'loan_amount'                       => $this->loan_amount,
            'each_instalment_payment_amount'    => $this->each_instalment_payment_amount,
            'total_repaid_loan_amount'          => $this->total_repaid_loan_amount,
            'approved_status'                   => $this->approved_status,
            'application_date'                  => $this->application_date,
            'current_payment_status'            => $this->current_payment_status,
            'application_date'                  => $this->application_date,
            'created_at'                        => (string) $this->created_at,
            'updated_at'                        => (string) $this->updated_at,
        ];
    }
}
