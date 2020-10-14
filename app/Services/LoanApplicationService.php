<?php

namespace App\Services;

use App\Models\LoanApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\LoanStatus;

/**
 * ClaimRepository 
 */
class LoanApplicationService
{
    protected $loanApplication;

    public function __construct(LoanApplication $loanApplication)
    { 
        $this->loanApplication = $loanApplication;
    }

    public function all(): Collection
    {
        $isAdmin = Auth::user()['is_admin'];
        if($isAdmin === 'Y')
        {
            return $this->loanApplication->all();
        }
        else 
        {
            return $this->loanApplication->where('user_id', Auth::user()->id)->get();
        }
    }

    public function store($request)
    {
        $data = $request->all();

        $lastApplicationId              = $this->loanApplication::orderBy('id','DESC')->first()->id ?? 0;
        $data['application_no']         = 'LA-'.str_pad($lastApplicationId + 1, 8, "0", STR_PAD_LEFT);
        $data['user_id']                = Auth::user()['id'];
        $data['approved_status']        = LoanStatus::PENDING;
        $data['application_date']       = date('Y-m-d');
        $data['current_payment_status'] = '';
        
        $validator = Validator::make($data, [
            'loan_amount'           => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'loan_term'             => 'required|in:'.LoanStatus::LOAN_TERM_SHORT.','.LoanStatus::LOAN_TERM_MEDIUM.','.LoanStatus::LOAN_TERM_LONG,
            'repayment_frequency'   => 'required|in:'.LoanStatus::REPAYMENT_FREQUENCY_WEEKLY.','.LoanStatus::REPAYMENT_FREQUENCY_MONTHLY.','.LoanStatus::REPAYMENT_FREQUENCY_YEARLY,
        ]);

        if($validator->fails()){
            // return response();
            return ['error' => $validator->errors()];
        }

        return $this->loanApplication::create($data);
    }

    public function find(string $id): ? Collection
    {
        return $this->loanApplication->find($id);
    }

    public function destroy(string $id): int
    {
        return $this->loanApplication::destroy($id);
    }

}
