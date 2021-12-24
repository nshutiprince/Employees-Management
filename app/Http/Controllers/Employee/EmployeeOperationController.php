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
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeeOperationController extends Controller
{
    /**
     * @OA\Post(
     ** path="/createEmployee",
     *   tags={"createEmployee"},
     *   summary="create an Employee",
     *   operationId="createEmployee",
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
     *          type="string"
     *      )
     *   ),@OA\Parameter(
     *      name="position",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *          type="string"
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
    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phoneNumber' => 'required|digits:10|unique:employees',
            'nationalId' => 'required|digits:16|unique:employees',
            'dob' => 'required|date|before:-18 years',
            'email' => 'required|email|unique:employees',
            'position' =>  ['required', Rule::in(['MANAGER', 'DEVELOPER', 'DESIGNER', 'TESTER', 'DEVOPS'])],
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
            'position' => $data['position'],
            'createDate' => $createDate,
            'password' => Hash::make($password),
        ]);
        if (!$employee) {
            return response()->json(['message' => 'Failed Registration'], 400);
        } else {
            try {
                $details = [
                    'message' => "Dear $name, Thank you for joining our organization, your employee code is $code and your  password is $password ",
                ];
                Mail::to("$email")->send(new EmployeeMail($details));
            } catch (\Exception $e) {
                // Get error here
                return response()->json(['message' => 'Employee registered successfully', 'employee' => $employee, 'mail error' => "make your email configuration"], 200);
            }
            return response()->json(['message' => 'Employee registered successfully', 'employee' => $employee], 200);
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
    /**
     * @OA\Get(
     ** path="/EmployeeList",
     * security={{"bearer":{}}},
     *     tags={"Authorize"},
     *   summary="return list of all employeees",
     *   operationId="EmployeeList",
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

    public function EmployeeList()
    {
        $employee = Employee::all();
        if ($employee->count() == 0) {
            return response()->json(['message' => 'You have no Registered Employee'], 200);
        }
        return response()->json([
            "message" => "Employee list",
            "data" => $employee
        ], 200);
    }
    /**
     * @OA\Delete(
     ** path="/DeleteEmployee",
     *   tags={"DeleteEmployee"},
     *   summary="deletes an employee",
     *   operationId="DeleteEmployee",
     *  @OA\Parameter(
     *      name="code",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    public function DeleteEmployee($code)
    {
        $employee = Employee::where("code", "=", $code)->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 400);
        }
        $result = $employee->delete();
        if ($result) {
            return response()->json(['message' => 'Employee deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Employee was not deleted'], 400);
        }
    }
    /**
     * @OA\Get(
     ** path="/SearchEmployee",
     *   tags={"SearchEmployee"},
     *   summary="used to search an employee",
     *   operationId="SearchEmployee",
     *  @OA\Parameter(
     *      name="data",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    public function SearchEmployee($data)
    {
        $employee = Employee::where("code", "=", $data)
            ->orwhere("position", "=", $data)
            ->orwhere("name", "=", $data)
            ->orwhere("email", "=", $data)
            ->orwhere("phoneNumber", "=", $data)
            ->get();
        if (!$employee) {
            return response()->json(['message' => 'No record found'], 400);
        }
        return response()->json([
            "message" => "Employee search list",
            "data" => $employee
        ], 200);
    }
    /**
     * @OA\Post(
     ** path="/SuspendEmployee",
     *   tags={"SuspendEmployee"},
     *   summary="used to suspend an employee",
     *   operationId="SuspendEmployee",
     *  @OA\Parameter(
     *      name="code",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    public function SuspendEmployee($code)
    {
        $employee = Employee::where("code", "=", $code)->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 400);
        }
        if ($employee->status == "INACTIVE") {
            return response()->json(['message' => 'Employee is already inactive', 'employee' => $employee], 200);
        }
        $result = $employee->update([
            'status' => 'INACTIVE'
        ]);
        if ($result) {
            return response()->json(['message' => 'Employee suspended successfully', 'employee' => $employee], 200);
        } else {
            return response()->json(['message' => 'Employee was not suspended', 'employee' => $employee], 400);
        }
    }
    /**
     * @OA\Post(
     ** path="/ActivateEmployee",
     *   tags={"ActivateEmployee"},
     *   summary="used to activate an employee",
     *   operationId="ActivateEmployee",
     *  @OA\Parameter(
     *      name="code",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
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
    public function ActivateEmployee($code)
    {
        $employee = Employee::where("code", "=", $code)->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 400);
        }
        if ($employee->status == "ACTIVE") {
            return response()->json(['message' => 'Employee is already active', 'employee' => $employee], 200);
        }
        $result = $employee->update([
            'status' => 'ACTIVE'
        ]);
        if ($result) {
            return response()->json(['message' => 'Employee activated successfully', 'employee' => $employee], 200);
        } else {
            return response()->json(['message' => 'Employee was not activated', 'employee' => $employee], 400);
        }
    }
    /**
     * @OA\Put(
     ** path="/updateEmployee",
     *   tags={"updateEmployee"},
     *   summary="update employee information according to the given one",
     *   operationId="updateEmployee",
     *
     *  @OA\Parameter(
     *      name="code",
     *      in="path",
     *      required=true,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="phoneNumber",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="nationalId",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="integer"
     *      )
     *   ),
     * @OA\Parameter(
     *      name="dob",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="date"
     *      )
     *   ),@OA\Parameter(
     *      name="position",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *          type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="email",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
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
     *      response=401,
     *       description="Unauthenticated"
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
     * login api
     *
     * @return \Illuminate\Http\Response
     */
    public function updateEmployee(Request $request, $code)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'phoneNumber' => 'digits:10|unique:employees',
            'nationalId' => 'digits:16|unique:employees',
            'dob' => 'date|before:-18 years',
            'email' => 'email|unique:employees',
            'position' =>  [Rule::in(['MANAGER', 'DEVELOPER', 'DESIGNER', 'TESTER', 'DEVOPS'])],
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = $request->all();
        $employee = Employee::where("code", "=", $code)->first();
        if (!$employee) {
            return response()->json(['message' => 'Employee not found'], 400);
        }
        if ($request->name) {
            $employee->name = $request->name;
        }
        if ($request->email) {
            $employee->email = $request->email;
        }
        if ($request->nationalId) {
            $employee->nationalId = $request->nationalId;
        }
        if ($request->phoneNumber) {
            $employee->phoneNumber = $request->phoneNumber;
        }
        if ($request->dob) {
            $employee->dob = $request->dob;
        }
        if ($request->position) {
            $employee->position = $request->position;
        }
        $result = $employee->update();
        if (!$result) {
            return response()->json(['message' => 'Failed update'], 400);
        } else {
            return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
        }
    }
    /**
     * @OA\Post(
     ** path="/import",
     *   tags={"import"},
     *   summary="used to import excel file and save employee in the database",
     *   operationId="import",
     *  @OA\Parameter(
     *      name="file",
     *      in="query",
     *      required=true,
     *      @OA\Schema(
     *           type="file"
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
    public function import(Request $request)
    {
        Excel::import(new EmployeesImport, $request->file('file'));
        return response()->json(['message' => 'Employee data successfully imported'], 200);
    }
}
