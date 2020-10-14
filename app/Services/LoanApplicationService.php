<?php

namespace App\Services;

use App\Models\LoanApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * ClaimRepository 
 */
class LoanApplicationService
{
    protected $loanApplication;

    public function __construct(LoanApplication $loanApplication)
    { 
        $this->loanApplication = $loanApplication;
    }

    public function all(): Collection
    {
        $isAdmin = Auth::user()->isAdmin;
        if($isAdmin === 'Y')
        {
            return $this->loanApplication->all();
        }
        else 
        {
            return $this->loanApplication->where('user_id', Auth::user()->id)->get();
        }
    }

    public function find(string $id): ? Collection
    {
        return $this->loanApplication->find($id);
    }

    public function destroy(string $id): int
    {
        return $this->loanApplication::destroy($id);
    }

}
