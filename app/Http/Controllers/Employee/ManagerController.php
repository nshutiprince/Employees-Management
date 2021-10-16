<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Imports\EmployeesImport;
use App\Imports\UsersImport;
use App\Mail\EmployeeMail;
use App\Models\Employee;
use Carbon\Carbon;
use Cron\DayOfWeekField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Excel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Validators\ValidationException;

class ManagerController extends Controller
{
    /**
     * @OA\Post(
     ** path="/signup",
     *   tags={"signup"},
     *   summary="manager signup api",
     *   operationId="signup",
     *
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="phoneNumber",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="nationalId",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="dob",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="date"
     *      )
     *   ),
     *   @OA\Response(
     *      response=200,
     *       description="Success",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     *   ),
     *   @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     *   @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *)
     **/
    /**
     *
     * @return \Illuminate\Http\Response
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phoneNumber' => 'required|digits:10|unique:employees',
            'nationalId' => 'required|digits:16|unique:employees',
            'dob' => 'required|date|before:-18 years',
            'email' => 'required|email|unique:employees',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = $request->all();
        $code = $this->generateUniqueCode();
        $password = $this->randomPassword();
        $createDate = Date::now();
        $email = $data['email'];
        $name = $data['name'];
        $employee = Employee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'nationalId' => $data['nationalId'],
            'code' => $code,
            'phoneNumber' => $data['phoneNumber'],
            'dob' => $data['dob'],
            'position' => "MANAGER",
            'createDate' => $createDate,
            'status' => "INACTIVE",
            'password' => Hash::make($password),
        ]);
        if (!$employee) {
            return response()->json(['message' => 'Failed Registration'], 400);
        } else {
            event(new Registered($employee));
            return response()->json(['message' => 'Manager registered successfully', 'action' => 'An email was sent to your email address so you have to first verify your email to activate your account', 'employee' => $employee], 200);
        }
    }
    public function generateUniqueCode()
    {
        do {
            $code = "EMP" . random_int(1000, 9999);
        } while (Employee::where("code", "=", $code)->first());

        return $code;
    }
    public function randomPassword()
    {
        $alphabet = '1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i <= 5; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
}
