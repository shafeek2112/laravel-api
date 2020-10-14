<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at', 'application_date'];

    protected $fillable = [
        'user_id',
        'application_no',
        'loan_term',
        'repayment_frequency',
        'loan_amount',
        'approved_status',
        'application_date',
        'current_payment_status',
    ];

    /* 
    * Each applications belongs to one user
    */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
