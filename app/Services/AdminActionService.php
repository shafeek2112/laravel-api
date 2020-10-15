<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Enums\UserStatus;
use App\Models\User;


/**
 * ClaimRepository 
 */
class AdminActionService
{
    protected $loanApplicationModelInstance;
    protected $userModelInstance;
    protected $isAdmin;
    protected $user;

    public function __construct()
    { 
        $this->loanApplicationModelInstance = new LoanApplication();
        $this->userModelInstance            = new User();
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

        $loan = $this->loanApplicationModelInstance->where('application_no', $loanApplicationNo)->get()->first();
        // dd($loan);
        if(empty($loan))
            return ['error' => 'Cannot find the Loan Application. Please check the "application_no" field.'];

        if(($loan['approved_status'] === LoanStatus::APPROVED) || $loan['approved_status'] === $data['approved_status'])
            return ['error' => 'This user is already in '.$loan['approved_status'].' status'];

        
        $loan->fill($data)->save();


    }
}