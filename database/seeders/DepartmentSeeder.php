<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            'CPSD',
            'ENG',
            'GS',
            'HC',
            'OPR',
            'PDV',
            'PLANT',
            'SPD',
            'SHE',
            'SM',
            'FAT'
        ];

        foreach ($departments as $dept) {
            DB::table('departments')->insert([
                'name' => $dept,
                'description' => $dept . ' Department'
            ]);
        }
    }
}
