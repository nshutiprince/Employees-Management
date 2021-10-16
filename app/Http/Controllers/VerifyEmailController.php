<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $employee = Employee::find($request->route('id'));

        if ($employee->hasVerifiedEmail()) {

            return response()->json(['message' => 'Employee email has been already verified', 'employee' => $employee], 200);
        }

        if ($employee->markEmailAsVerified()) {
            event(new Verified($employee));
        }
        $employee->update([
            'status' => 'ACTIVE'
        ]);
        return response()->json(['message' => 'Employee email verified successfully', 'employee' => $employee], 200);
    }
}
