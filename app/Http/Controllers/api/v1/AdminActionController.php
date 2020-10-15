<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminActionService;
use App\Traits\ApiResponser;
use PhpParser\Node\Expr\Cast\String_;

class AdminActionController extends Controller
{
    use ApiResponser;
    protected $adminActionService;

    public function __construct()
    {
        $this->adminActionService = new AdminActionService();
    }

     /**
     * Approve or Reject the new user 
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON Repsonse
     */
    public function userApproveReject(Request $request): String    
    {
        $adminActionService = $this->adminActionService->userApproveReject($request);

        if(!empty($adminActionService['error'])) 
            return $this->error($adminActionService,401);

        // dd(gettype($adminActionService));
        return $this->success($adminActionService,'User Record Updated Successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function loanApproveReject(Request $request, $loanApplicationNo): String
    {
        $adminActionService = $this->adminActionService->loanApproveReject($request, $loanApplicationNo);

        if(!empty($adminActionService['error'])) 
            return $this->error($adminActionService,401);

        return $this->success($adminActionService,'Loan Application Record Updated Successfully', 200);
    }

}