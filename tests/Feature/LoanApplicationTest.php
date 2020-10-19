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
        $response = $this->loanCreation($token);

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
        $response = $this->loanCreation($token);

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
    * Admin approve the invalid loan.
    *
    * @return void
    */
    public function testAdminUserApproveLoanWithInvalidApplicationNumber():void
    {
        ## Retrieving test
        $token = $this->getAdminToken();
        $loanData = [
            'approved_status' => 'approved'
        ];
        $loan_application = 'LA-00000001q';
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/loan/'.$loan_application, $loanData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => 'Cannot find the Loan Application. Please check the "application_no" field.'
                ], 
        ]);
    }
    
    /**
    * Admin approve the loan.
    *
    * @return void
    */
    public function testAdminUserApproveLoanAndAutoInsertInstalmentPaymet():void
    {
        ## Admin Approve the loan
        $token = $this->getAdminToken();
        $loanData = [
            'approved_status' => 'approved'
        ];
        $loan_application = 'LA-00000001';
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/loan/'.$loan_application, $loanData, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => "Loan Application Record Updated Successfully", 
        ]);
        
        ## Auto calculation of the instalment
        $eachInstalment = $response->json()['data']['each_instalment_payment_amount'];
        $this->assertGreaterThan(0,$eachInstalment);
    }

    /**
    * Wrong user paying instalment
    * @param string token
    * @return void
    */
    public function testNonOwnerUserPayForInstalment(): void
    {
        $token = $this->getAdminToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/user/pay-instalment/1', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => "You are not the owner of this application, so you are not allowed to pay for this loan."
                ], 
        ] );
    }

    /**
    * User paying instalment
    *
    * @return void
    */
    public function testRightOwnerUserPayForInstalment(): void
    {
        $token = $this->getToken();
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/user/pay-instalment/1', [], ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => 'Successfully Submitted Payment.', 
        ] );
    }

    /**
    * Admin try to change to paid status before payment made
    * 
    * @return void
    */
    public function testAdminApprovePaymentBeforeUseMakePayment(): void
    {
        $token = $this->getAdminToken();
        $data = [
            'status' => 'paid'
        ];

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/payment-update/1', $data, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Error", 
                "message"   => [
                    'error' => 'This payment is not yet made by the user for this. So you cannot approve before payment is paid.',
                ], 
            ] );
    }
    
    /**
    * Admin approve the instalment payment
    * 
    * @return void
    */
    public function testAdminApprovePaymentAndCreateNextInstalment(): void
    {
        $token = $this->getAdminToken();
        $data = [
            'status' => 'paid'
        ];

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->json('POST', 'api/v1/admin/payment-update/2', $data, ['Accept' => 'application/json'])
            ->assertStatus(200)
            ->assertJson([
                "status"   => "Success", 
                "message"   => 'Successfully Updated the Payment', 
            ] );
    }


    ######### Helper function ##################
    
    /**
    * Get admin token
    * 
    * @return String Token
    */
    public function getAdminToken(): string
    {
        $userData = [
            "email" => "admin@aspire.test",
            "password" => "password",
        ];
        $response = $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json']);
        return $response->json()['data']['accessToken'];
    }

    /**
    * Create loan
    * 
    * @return Json Loan
    */
    public function createNewLoan()
    {
        $token = $this->getToken();
        return $response = $this->loanCreation($token);
    }
    
    /**
    * Get user token
    * 
    * @return String Token
    */
    public function getToken()
    {
        $userData = [
            "email"     => "approved@aspire.test",
            "password"  => "approved",
        ];
        $response = $this->json('POST', 'api/v1/auth/login', $userData, ['Accept' => 'application/json']);
        return $response->json()['data']['accessToken'];
        
    }

    /**
    * Create new loan
    * @param string token
    * @return json Loan
    */
    public function loanCreation(string $token)
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
