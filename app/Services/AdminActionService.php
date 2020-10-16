<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\UserStatus;
use App\Models\LoanRepaymentDetail;
use App\Models\User;



/**
 * ClaimRepository 
 */
class AdminActionService
{
    protected $loanApplicationModelInstance;
    protected $loanApplication;
    protected $userModelInstance;
    protected $loanRepaymentDetail;
    protected $isAdmin;
    protected $user;

    public function __construct()
    { 
        $this->loanApplicationModelInstance     = new LoanApplication();
        $this->userModelInstance                = new User();
        $this->loanRepaymentDetailModelInstance = new LoanRepaymentDetail();
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
     * Approve or Reject user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response or array
     */
    public function userApproveReject($request)
    {
        $data = $request->all();
        
        $this->setAuthUser();

        // if(!$this->isAdmin)
        //     return ['error' => 'You are not allowed to do this action.'];

        $validator = Validator::make($data, [
            'user_id'   => 'required|integer',
            'status'    => 'required|in:'.UserStatus::APPROVED.','.UserStatus::PENDING.','.UserStatus::REJECTED,
        ]);

        if($validator->fails()){
            return ['error' => $validator->errors()];
        }
        
        $user = $this->userModelInstance->where('id', $data['user_id'])->get()->first();

        if(empty($user))
            return ['error' => 'Cannot find the user. Please check the "user_id" field.'];

        if($user['status'] === $data['status'])
            return ['error' => 'This user is already in '.$user['status'].' status'];

        $user->fill($data)->save();
        return $user;
    }

    /**
     * Approve or reject Loan Application
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response or array
     */
    public function loanApproveReject($request, $loanApplicationNo)
    {
        $data = $request->all();
        
        $this->setAuthUser();

        $validator = Validator::make($data, [
            'approved_status'    => 'required|in:'.LoanStatus::APPROVED.','.LoanStatus::REJECTED,
        ]);

        if($validator->fails()){
            return ['error' => $validator->errors()];
        }

        $this->loanApplication = $this->loanApplicationModelInstance->where('application_no', $loanApplicationNo)->get()->first();
        if(empty($this->loanApplication))
            return ['error' => 'Cannot find the Loan Application. Please check the "application_no" field.'];

        # For simplicity - If loan is already in approval status cannot change back to Rejected or Pending. Becas it will affect the old 
        # Loan payment detail records.
        if(($this->loanApplication['approved_status'] === LoanStatus::APPROVED) || $this->loanApplication['approved_status'] === $data['approved_status'])
            return ['error' => 'This user is already in '.$this->loanApplication['approved_status'].' status'];

    
        $this->loanApplication->fill($data)->save();

        # If the loan is successfully approved then create one new record for first repayment instalment.
        if($data['approved_status'] === LoanStatus::APPROVED)
        {
            ## First find the one time instalment from payment term and payemnt frequency for the loan and save into loan application table.
            $findEachInstalmentAmount = $this->findEachInstalmentAmount();
            $this->loanApplication->fill(['one_time_repayment_amount' => $findEachInstalmentAmount])->save();

            ## Create the first time repayment record.
            $loanRepayData = [
                'user_id'                   => $this->loanApplication['user_id'],
                'loan_application_id'       => $this->loanApplication['id'],
                'loan_repayment_amount'     => $this->findNextRepaymentAmount(),
            ];
            $this->loanRepaymentDetailModelInstance::create($loanRepayData);
        }

        return $this->loanApplication;
    }

    public function findEachInstalmentAmount()
    {
        ## Get the loan term - 12months / 24months / 64months.
        $paymentTermMonths = (($this->loanApplication['loan_term'] === LoanStatus::LOAN_TERM_SHORT) ? LoanStatus::LOAN_TERM_SHORT_INT :  
                             (($this->loanApplication['loan_term'] === LoanStatus::LOAN_TERM_MEDIUM) ? LoanStatus::LOAN_TERM_MEDIUM_INT : 
                             (($this->loanApplication['loan_term'] === LoanStatus::LOAN_TERM_LONG) ?  LoanStatus::LOAN_TERM_LONG_INT : LoanStatus::LOAN_TERM_DEFAULT )));
        $paymentTermMonths = is_numeric($paymentTermMonths) ? $paymentTermMonths : LoanStatus::LOAN_TERM_DEFAULT;

        ## Find the total days for the loan payment term.
        $totalDays = LoanStatus::TOTAL_DAYS_IN_MONTH * $paymentTermMonths;
        
        ## Get the Repayment frequency - weekly/monthly/yearly
        $paymentFrequency = (($this->loanApplication['repayment_frequency'] === LoanStatus::REPAYMENT_FREQUENCY_WEEKLY) ? LoanStatus::REPAYMENT_FREQUENCY_WEEKLY_INT :  
                            (($this->loanApplication['repayment_frequency'] === LoanStatus::REPAYMENT_FREQUENCY_MONTHLY) ? LoanStatus::REPAYMENT_FREQUENCY_MONTHLY_INT : 
                            (($this->loanApplication['repayment_frequency'] === LoanStatus::REPAYMENT_FREQUENCY_YEARL) ?  LoanStatus::REPAYMENT_FREQUENCY_YEARL_INT : LoanStatus::REPAYMENT_FREQUENCY_DEFAULT )));
    
        ## Find one time instalment amount from payment term and payemnt frequency
        $totalInstalment = ceil($totalDays / $paymentFrequency);
        return ceil($this->loanApplication['loan_amount'] / $totalInstalment);
    }

    public function findNextRepaymentAmount()
    {
        $nextRepaymentAmount = $this->loanApplication['one_time_repayment_amount'];

        ## If the sum of (already paid + next repayment) is greater than the actual loan amount then we just take the difference of (actual loan amount - already paid amount)
        if(($this->loanApplication['repaid_loan_amount'] + $nextRepaymentAmount) > $this->loanApplication['loan_amount'])
            return ($this->loanApplication['loan_amount'] - $this->loanApplication['repaid_loan_amount']);

        return $nextRepaymentAmount;
    }
}