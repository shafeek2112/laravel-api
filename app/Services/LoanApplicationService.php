<?php

namespace App\Services;

use App\Models\LoanApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\LoanStatus;
use App\Models\User;


/**
 * ClaimRepository 
 */
class LoanApplicationService
{
    protected $loanApplication;
    protected $loanApplicationModelInstance;
    protected $isAdmin;
    protected $user;

    public function __construct()
    { 
        $this->loanApplicationModelInstance  = new LoanApplication();
    }

    /* 
    * This will set the Authuser 
    */
    public function setAuthUser(): void
    {
        $this->user     = Auth::user();
        $this->isAdmin  = $this->user['is_admin'] === 'Y' ? TRUE : FALSE;
    }

    /**
     * Return all applications if authenticated user is Admin. Otherwise return only their own application
     * 
     * @return \Illuminate\Support\Collection
     */
    public function all(): Collection
    {
        $this->setAuthUser();

        if($this->isAdmin)
            return $this->loanApplicationModelInstance->all();
        
        # If its a normal user, then return only their own applications
        return $this->loanApplicationModelInstance->where('user_id', $this->user['id'])->get();
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

        $this->setAuthUser();
        
        # For simplicity, Admin can only create application for other normal users. Because to avoid the unwanted approval/rejection actions conflicts.
        # if authenticated is Admin, then request body should have 'user_id' to whom the
        # application is created for. Admin user cannot create application for themselves.
        if(($this->isAdmin) && ((empty($data['user_id'])) || (!empty($data['user_id']) && $this->user['id'] === $data['user_id'])) )
            return ['error' => 'Admin user cannot sumbit application for themselves. Please signup as normal user account then submit your loan application.'];

        # Admin also cannot application on behalf of another admin user.
        if(!$this->checkApplicationCreateForNormalUser())
            return ['error' => 'Admin user cannot sumbit application for another Admin user.'];

        $lastApplicationId              = $this->loanApplicationModelInstance::orderBy('id','DESC')->first()->id ?? 0;
        $data['application_no']         = 'LA-'.str_pad($lastApplicationId + 1, 8, "0", STR_PAD_LEFT);
        $data['user_id']                = $this->user['id'];
        $data['approved_status']        = LoanStatus::PENDING;
        $data['application_date']       = date('Y-m-d');
        $data['current_payment_status'] = '';
        
        $validator = $this->validRequest($data);

        if($validator->fails()){
            return ['error' => $validator->errors()];
        }

        return $this->loanApplicationModelInstance::create($data);
    }

    /**
     * Display the specified application using Application number.
     *
     * @param  string  $loanApplicationNo
     * @return LoanApplication instance / null / array
     */
    public function findByApplicationNo(string $loanApplicationNo)
    {
        $this->setAuthUser();

        $this->loanApplication = $this->loanApplicationModelInstance->where('application_no', $loanApplicationNo)->get()->first();
        
        if(empty($this->loanApplication))
            return ['error' => 'Cannot find the application. Please check your application number.'];

        if(!$this->checkRequestedActionOwnership())
            return ['error' => 'You are not allowed to access this application.'];

        return $this->loanApplication;
    }

    /**
     * Update the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function update($request, $loanApplicationNo)
    {
        $this->setAuthUser();

        $this->loanApplication = $this->loanApplicationModelInstance->where('application_no', $loanApplicationNo)->get();
        
        if(!$this->checkRequestedActionOwnership())
            return ['error' => 'You are not allowed to access this application.'];
            
        # Check the status of the Loan Application, if deleted/approved/rejected then user cannot edit this.
        if(!$this->checkApplicationCanBeEditable())
            return ['error' => 'This Application is in '.$this->getApplicationCurrentStatus().' status, so you cannot edit this.'];

        $data = $request->all();
        
        $validator = $this->validRequest($data);

        if($validator->fails()){
            return ['error' => $validator->errors()];
        }
        
        return $this->loanApplication->fill($data)->save();
    }

    /**
     * To pay the repay amount.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function repayment(string $loanApplicationNo)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $loanApplicationNo)
    {
        $this->setAuthUser();

        if(!$this->checkRequestedActionOwnership())
            return ['error' => 'You are not allowed to access this application.'];
        
        return $this->loanApplicationModelInstance::destroy($loanApplicationNo);
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
    
    /**
     *  Check the auth user allowed to do the action.
     *
     * @return boolen
     */
    public function checkApplicationCreateForNormalUser(): bool
    {
        $user = User::find($this->user['id']);
        return ($user['is_admin'] === 'Y') ? FALSE : TRUE;
    }

     /**
     *  Check the auth user allowed to do the action.
     *
     * @param Request $data
     * @return boolen
     */
    public function validRequest($data)
    {
        return  Validator::make($data, [
                    'loan_amount'           => 'required|regex:/^\d+(\.\d{1,2})?$/',
                    'loan_term'             => 'required|in:'.LoanStatus::LOAN_TERM_SHORT.','.LoanStatus::LOAN_TERM_MEDIUM.','.LoanStatus::LOAN_TERM_LONG,
                    'repayment_frequency'   => 'required|in:'.LoanStatus::REPAYMENT_FREQUENCY_WEEKLY.','.LoanStatus::REPAYMENT_FREQUENCY_MONTHLY.','.LoanStatus::REPAYMENT_FREQUENCY_YEARLY,
                ]);
    }

    /**
     *  Check whether the application can be editable.
     *
     * @return boolen
     */
    public function checkApplicationCanBeEditable(): bool
    {
        return ($this->loanApplication['approved_status'] ===  LoanStatus::APPROVED || $this->loanApplication['approved_status'] ===  LoanStatus::REJECTED) ? FALSE : TRUE;
    }
    
    /**
     *  Get the application status.
     *
     * @return string
     */
    public function getApplicationCurrentStatus(): string
    {
        return (($this->loanApplication['approved_status'] === LoanStatus::APPROVED) ? 'Approved' :
               (($this->loanApplication['approved_status'] === LoanStatus::REJECTED) ? 'Rejected' : 
               (($this->loanApplication['approved_status'] === LoanStatus::REJECTED) ? 'Rejected' : 
               (($this->loanApplication['approved_status'] === LoanStatus::REJECTED) ? 'Need Additional Info' : 'Locked' ))));
    }
}
