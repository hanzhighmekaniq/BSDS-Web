<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Pembaca file daftar siswa (.csv/.xlsx). Kolom: name, email, nis, classroom, absence_no, password (opsional).
 * Baris diproses oleh Admin\UserController::import().
 */
class StudentsImport implements ToArray, WithHeadingRow
{
    public function array(array $array): void {}
}
