<?php

namespace App\Services;

use App\Models\LoanApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\LoanStatus;
use App\Models\LoanRepaymentDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * ClaimRepository 
 */
class LoanApplicationService
{
    protected $loanApplication;
    protected $loanApplicationModelInstance;
    protected $loanApplicationDetailModelInstance;
    protected $isAdmin;
    protected $user;

    public function __construct()
    { 
        $this->loanApplicationModelInstance         = new LoanApplication();
        $this->loanApplicationDetailModelInstance   = new LoanRepaymentDetail();
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

        # If the submitted user is not admin,then set their own id as user_id.
        if(!$this->isAdmin)
            $data['user_id'] = $this->user['id'];

        # Admin also cannot application on behalf of another admin user.
        if(!$this->checkApplicationCreateForNormalUser($data['user_id']))
            return ['error' => 'Admin user cannot sumbit application for another Admin user.'];

        $lastApplicationId              = $this->loanApplicationModelInstance::orderBy('id','DESC')->first()->id ?? 0;
        $data['application_no']         = 'LA-'.str_pad($lastApplicationId + 1, 8, "0", STR_PAD_LEFT);
        $data['user_id']                = $data['user_id'];
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
        return $this->checkValidationFindApplication($loanApplicationNo);
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

        $this->loanApplication = $this->checkValidationFindApplication($loanApplicationNo);

        if(!empty($this->loanApplication['error'])) 
            return $this->loanApplication;
            
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
     * Remove the specified resource from storage.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $loanApplicationNo)
    {
        $this->setAuthUser();

        $this->loanApplication = $this->checkValidationFindApplication($loanApplicationNo);

        if(!empty($this->loanApplication['error'])) 
            return $this->loanApplication;

        # Check the status of the Loan Application, if deleted/approved/rejected then user cannot edit this.
        if(!$this->checkApplicationCanBeEditable())
            return ['error' => 'This Application is in '.$this->getApplicationCurrentStatus().' status, so you cannot delete this.'];
        
        return $this->loanApplication::destroy($this->loanApplication['id']);
    }

    /**
     * Get all/outstanding instalment
     *
     * @param  int  $loanApplicationNo
     * @param  string  $all
     * @return \Illuminate\Http\Response
     */
    public function repaymentInstalmentList(string $loanApplicationNo, string $where)
    {
        $this->setAuthUser();
        
        $this->loanApplication = $this->checkValidationFindApplication($loanApplicationNo);
        if(!empty($this->loanApplication['error'])) 
            return $this->loanApplication;
        
        $data = ['where' => $where, 'loan_application_no' => $loanApplicationNo];
        $validator = Validator::make($data, [
            'where'                 => 'required|in:'.LoanStatus::PAYMENT_STATUS_PAID.','.LoanStatus::PAYMENT_STATUS_FAILED.','.LoanStatus::PAYMENT_STATUS_PROCESSING.','.LoanStatus::PAYMENT_STATUS_ALL,
            'loan_application_no'   =>  'required'
        ]);
       
        if($validator->fails()){
            return ['error' => $validator->errors()];
        }
       
        $whereCondition = [];

        ## Check if any filter applied
        if($where !== 'all')
            $whereCondition['status'] = $where;
        
        ## Check if this admin or normal user. If admin then just get all the application.
        if(!$this->isAdmin)
            $whereCondition['user_id'] =  $this->user['id'];

        $loanRepaymentDetail = empty($whereCondition) ? $this->user->loanRepaymentDetail->all() : $this->user->loanRepaymentDetail()->where($whereCondition)->get();
        return $loanRepaymentDetail;
    }

    /**
     * To pay the repay amount.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function payInstalment(string $loanApplicationNo)
    {
        
    }

    public function checkValidationFindApplication(string $loanApplicationNo)
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
    public function checkApplicationCreateForNormalUser(string $user_id): bool
    {
        $user = User::find($user_id);
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
                    'repayment_frequency'   => 'required|in:'.LoanStatus::REPAYMENT_FREQUENCY_WEEKLY.','.LoanStatus::REPAYMENT_FREQUENCY_MONTHLY.','.LoanStatus::REPAYMENT_FREQUENCY_DEFAULT,
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
