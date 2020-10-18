<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Faker\Factory as Faker; 
use Tests\TestCase;
use App\Models\Thread;

class LoanApplicationTest extends TestCase
{
    /**
    * Get loan applciation before login
    *
    * @return void
    */
    public function testGetLoanApplicationByUserWithoutToken():void
    {
        $this->json('GET', 'api/v1/user/loan-application', ['Accept' => 'application/json'])
            ->assertStatus(401  )
            ->assertJson([
                "message"   => "Unauthenticated.", 
            ]);
    }
    
    /**
    * Get loan applciations after login
    *
    * @return void
    */
    public function testGetLoanApplicationByUserWithToken():void
    {
        $token = $this->getToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-application', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => "Successfully Fetched Loans", 
        ]);
    }
    
    /**
    * Creat loan applciation
    *
    * @return void
    */
    public function testApplyAndRetrieveLoanApplicationByUserWithToken():void
    {
        $token = $this->getToken();
        $response = $this->loanCreation($token)            
                    ->assertStatus(200)
                    ->assertJson([
                        "status"   => "Success", 
                        "message"   => "Loan has been successfully added", 
        ]);

        ## Retrieving test
        $loan_application = $response->json()['data']['application_no'];
        $loanData = [
            'loan_term'             => '12Months',
            'repayment_frequency'   => 'weekly',
            'loan_amount'           => '50000',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-application/'.$loan_application, [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => "Loan Successfully Fetched", 
        ]);
    }

    /**
    * Get application using invalid number
    *
    * @return void
    */
    public function testRetrieveLoanApplicationWithInvalidNumber():void
    {
        $token = $this->getToken();

        ## Retrieving test
        $loan_application = "testsample";
        $loanData = [
            'loan_term'             => '12Months',
            'repayment_frequency'   => 'weekly',
            'loan_amount'           => '50000',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-application/'.$loan_application, [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => 'Cannot find the application. Please check your application number.'
                ], 
        ]);
    }
    
    /**
    * Wrong URL testing
    *
    * @return void
    */
    public function testInvalidUrlLoanApplicationRepaymentList():void
    {
        $token = $this->getToken();
        $response = $this->loanCreation($token)            
                    ->assertStatus(200)
                    ->assertJson([
                        "status"   => "Success", 
                        "message"   => "Loan has been successfully added", 
        ]);

        ## Retrieving test
        $loan_application = $response->json()['data']['application_no'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-repayment-list/'.$loan_application, [], ['Accept' => 'application/json'])
            ->assertStatus(404);
    }
    
    /**
    * Get application repayment records
    *
    * @return void
    */
    public function testRetrieveLoanApplicationRepaymentList():void
    {
        $token = $this->getToken();
        $response = $this->loanCreation($token)            
                    ->assertStatus(200)
                    ->assertJson([
                        "status"   => "Success", 
                        "message"   => "Loan has been successfully added", 
        ]);

        ## Retrieving test
        $loan_application = $response->json()['data']['application_no'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('GET', 'api/v1/user/loan-repayment-list/'.$loan_application.'/all', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => "Successfully Fetched Loan Payment List", 
        ]);
    }
    
    /**
    * Non admin user try to approve the loan.
    *
    * @return void
    */
    public function testNonAdminUserApproveLoan():void
    {
        $token = $this->getToken();
        $response = $this->loanCreation($token);

        $loanData = [
            'approved_status' => 'approved'
        ];

        ## Retrieving test
        $loan_application = $response->json()['data']['application_no'];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/loan/'.$loan_application, $loanData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => 'You are not Admin to do this action.'
                ], 
        ]);
    }
    
    /**
    * Admin approve the loan.
    *
    * @return void
    */
    public function testAdminUserApproveLoan():void
    {
        $response = $this->createNewLoan();

        ## Retrieving test
        $token = $this->getAdminToken();

        $loanData = [
            'approved_status' => 'approved'
        ];
        $loan_application = $response->json()['data']['application_no'];
        // dd($token);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/loan/'.$loan_application, $loanData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => 'You are not Admin to do this action.'
                ], 
        ]);
    }
    


    ######### Helper function
    public function getAdminToken()
    {
        $userData = [
            "email" => "admin@aspire.test",
            "password" => "password",
        ];
        // dd($this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json']));
        return $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "Successfully Logged In",
                "code"      => 200
        ]);
        // return $response->json()['data']['accessToken'];
    }

    public function createNewLoan()
    {
        $token = $this->getToken();
        return $response = $this->loanCreation($token);
        
        // $this->withHeader('Authorization', 'Bearer ' . $token)
        //     ->json('GET', 'api/v1/auth/logout', [], ['Accept' => 'application/json'])
        //     ->assertStatus(200)
        //     ->assertJson([
        //         "status"    => "Success",
        //         "message"   => "Successfully Logged Out",
        //         "code"      => 200
        // ]);

    }
    
    public function getToken()
    {
        $userData = [
            "email"     => "approved@aspire.test",
            "password"  => "approved",
        ];

        $response = $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"    => "Success",
                "message"   => "Successfully Logged In",
                "code"      => 200
        ]);;
        return $response->json()['data']['accessToken'];
        
    }

    public function loanCreation($token)
    {
        $loanData = [
            'loan_term'             => '12Months',
            'repayment_frequency'   => 'weekly',
            'loan_amount'           => '50000',
        ];
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                    ->json('POST', 'api/v1/user/loan-application', $loanData, ['Accept' => 'application/json']);

        return $response;
    }
}
