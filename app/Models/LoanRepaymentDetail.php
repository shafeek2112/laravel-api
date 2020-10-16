<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRepaymentDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at', 'payment_date'];

    protected $fillable = [
        'user_id',
        'loan_application_id',
        'loan_repayment_amount',
        'status',
        'payment_date',
    ];

    /* 
    * Each record belongs to one user
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* 
    *  Each record belongs to one Loan Application
    */
    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class);
    }
}
