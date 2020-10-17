<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LoanApplicationService;
use App\Traits\ApiResponser;

class LoanApplicationController extends Controller
{
    use ApiResponser;
    protected $loanApplicationService;

    public function __construct()
    {
        $this->loanApplicationService = new LoanApplicationService();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JSON Repsonse
     */
    public function index(): String    
    { 
        $loanApplications = $this->loanApplicationService->all();
        dd($loanApplications);
        return $this->success($loanApplications,'Successfully Fetched Loans', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON Repsonse
     */
    public function store(Request $request)
    {
        $loanApplications = $this->loanApplicationService->store($request);  

        if(!empty($loanApplications['error'])) 
            return $this->error($loanApplications,401);

        return $this->success($loanApplications,'Loan has been successfully added', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $loanApplicationNo
     * @return JSON Repsonse
     */
    public function show($loanApplicationNo)
    {
        $loanApplications = $this->loanApplicationService->findByApplicationNo($loanApplicationNo); 

        if(!empty($loanApplications['error'])) 
            return $this->error($loanApplications,401);

        return $this->success($loanApplications,'Successfully Fetched', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $loanApplicationNo)
    {
        $loanApplications = $this->loanApplicationService->update($request, $loanApplicationNo);  

        if(!empty($loanApplications['error'])) 
            return $this->error($loanApplications,401);

        return $this->success($loanApplications,'Successfully Edited the Loan Application', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $loanApplicationNo
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $loanApplicationNo)
    {
        $loanApplications = $this->loanApplicationService->destroy($loanApplicationNo);  

        if(!empty($loanApplications['error'])) 
            return $this->error($loanApplications,401);

        return $this->success($loanApplications,'Successfully deleted the Loan Application', 200);
    }

    /**
     * Get all/outstanding instalment
     *
     * @param  int  $loanApplicationNo
     * @param  string  $all
     * @return \Illuminate\Http\Response
     */
    public function repaymentInstalmentList(string $loanApplicationNo, string $where = 'all')
    {
        $loanApplications = $this->loanApplicationService->repaymentInstalmentList($loanApplicationNo,$where);  
        return $this->success($loanApplications,'Successfully Fetched Loan Payment List', 200);
    }
    
    /**
     * Pay instalment
     *
     * @param  string  $loanRepaymentDetailId
     * @return \Illuminate\Http\Response
     */
    public function payInstalment(string $loanRepaymentDetailId)
    {
        $loanApplications = $this->loanApplicationService->payInstalment($loanRepaymentDetailId);  
        if(!empty($loanApplications['error'])) 
            return $this->error($loanApplications,401);
        return $this->success($loanApplications,'Successfully Submitted Payment.', 200);
    }
}
