<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AdminActionService;
use App\Traits\ApiResponser;


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

        return $this->success($adminActionService,'User Record Updated Successfully', 200);
    }

}