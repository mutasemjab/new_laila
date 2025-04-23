<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new User([
            'name'     => $row['name'],
            'company'  => $row['company'],
            'country'  => $row['country'],
            'email'    => $row['email'] ?? null,
            'gender'   => $row['gender'] ?? 1,
            'category' => $row['category'] ?? 1,
            'phone'    => $row['phone'],
            'barcode'  => $row['barcode'] ?? Str::random(10),
            'activate' => $row['activate'] ?? 1,
        ]);
    }
}
