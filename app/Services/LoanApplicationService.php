<?php

namespace App\Services;

use App\Models\LoanApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\LoanStatus;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * ClaimRepository 
 */
class LoanApplicationService
{
    protected $loanApplication;
    protected $isAdmin;
    protected $user;

    public function __construct(LoanApplication $loanApplication)
    { 
        $this->loanApplication  = $loanApplication;
        $this->user             = Auth::user();
        $this->isAdmin          = $this->user['is_admin'] === 'Y' ? TRUE : FALSE;
    }

    /**
     * Return all applications if authenticated user is Admin. Otherwise return only their own application
     * 
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection
    {
        if($this->isAdmin)
            return $this->loanApplication->all();
        
        # If its a normal user, then return only their own applications
        return $this->loanApplication->where('user_id', Auth::user()->id)->get();
    }

     /**
     * Store a newly created application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response or array
     */
    public function store($request)
    {
        $data = $request->all();

        # For simplicity, if authenticated is Admin, then request body should have 'user_id' to whom the
        # application is created for. Admin user cannot create application for themselves.
        if(($this->isAdmin) && ((empty($data['user_id'])) || (!empty($data['user_id']) && $this->user['id'] === $data['user_id'])) )
            return ['error' => 'Admin user cannot sumbit application for themselves. Please signup as normal user account then submit your loan application.'];

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
            return ['error' => $validator->errors()];
        }

        return $this->loanApplication::create($data);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return LoanApplication instance or null
     */
    /*  public function find(string $id): ? LoanApplication
    {
        return $this->loanApplication->find($id);
    } */
    
    /**
     * Display the specified application using Application number.
     *
     * @param  string  $loanApplicationNo
     * @return LoanApplication instance / null / array
     */
    public function findApplicationNo(string $loanApplicationNo)
    {
        $this->loanApplication->where('application_no', $loanApplicationNo)->get();

        if(!$this->checkRequestedActionOwnership())
            return ['error' => 'You are not allowed to access this application.'];

        return $this->loanApplication;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $loanApplicationNo)
    {
        if(!$this->checkRequestedActionOwnership())
            return ['error' => 'You are not allowed to access this application.'];
        
        return $this->loanApplication::destroy($loanApplicationNo);
    }

    /**
     *  Check the auth user allowed to do the action.
     *
     * @return boolen
     */
    public function checkRequestedActionOwnership(): bool
    {
        return ((!$this->isAdmin) && ($this->user['id'] != $this->loanApplication['user_id'])) ? FALSE : TRUE;
    }

}
