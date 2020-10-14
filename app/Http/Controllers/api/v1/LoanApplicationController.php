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

    public function __construct(LoanApplicationService $loanApplicationService)
    {
        $this->loanApplicationService = $loanApplicationService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JSON Repsonse
     */
    public function index(): String    {
        
        $loanApplications = $this->loanApplicationService->all();
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

        return $this->success($loanApplications,'Successfully Added Loan', 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $loanApplicationNo
     * @return JSON Repsonse
     */
    public function show($loanApplicationNo)
    {
        $loanApplications = $this->loanApplicationService->findApplicationNo($loanApplicationNo); 
        return $loanApplications;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return JSON Repsonse
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
