<?php

namespace App\Imports;

use App\Mail\EmployeeMail;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsOnError, SkipsOnFailure
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    use Importable, SkipsErrors, SkipsFailures;
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $code = $this->generateUniqueCode();
            $password = $this->randomPassword();
            $createDate = Date::now();
            Employee::create([
                'name' => $row['employee_name'],
                'email' => $row['email'],
                'password' => Hash::make($password),
                'nationalId' => $row['national_id_number'],
                'code' => $code,
                'phoneNumber' => $row['phone_number'],
                'dob' => SharedDate::excelToDateTimeObject($row['date_of_birth']),
                'position' => $row['position'],
                'status' => $row['status'],
                'createDate' => $createDate,
            ]);
            $name = $row['employee_name'];
            $email = $row['email'];
            try {
                $details = [
                    'message' => "Dear $name, Thank you for joining our organization, your employee code is $code and your  password is $password ",
                ];
                Mail::to("$email")->send(new EmployeeMail($details));
            } catch (\Exception $e) {
                // Get error here
            }
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
