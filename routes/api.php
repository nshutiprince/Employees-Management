<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\EmployeeOperationController;
use App\Http\Controllers\Employee\ManagerController;
use App\Http\Controllers\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [ManagerController::class, 'signup']);


Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Resend link to verify email
Route::post('/email/verify/resend', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth:api', 'throttle:6,1'])->name('verification.send');

Route::post('forgot-password', [AuthController::class, 'forgot']);
Route::post('password/reset', [AuthController::class, 'reset']);





Route::middleware(['auth:api'])->group(function () {
    Route::group(['middleware' => ['manager']], function () {
        Route::delete('DeleteEmployee/{code}', [EmployeeOperationController::class, 'DeleteEmployee']);
        Route::get('EmployeeList', [EmployeeOperationController::class, 'EmployeeList']);
        Route::get('SearchEmployee/{data}', [EmployeeOperationController::class, 'SearchEmployee']);
        Route::post('SuspendEmployee/{code}', [EmployeeOperationController::class, 'SuspendEmployee']);
        Route::post('ActivateEmployee/{code}', [EmployeeOperationController::class, 'ActivateEmployee']);
        Route::put('updateEmployee/{code}', [EmployeeOperationController::class, 'updateEmployee']);
        Route::post('import', [EmployeeOperationController::class, 'import']);
        Route::post('/createEmployee', [EmployeeOperationController::class, 'createEmployee']);
    });
    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
