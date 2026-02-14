<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $sites = [
            ['code' => 'ALL-SITE', 'name' => 'All Site'],
            ['code' => 'HO', 'name' => 'HO'],
            ['code' => 'HO-ALL-SITE', 'name' => 'HO & All Site'],
            ['code' => 'CAM', 'name' => 'CAM'],
            ['code' => 'MHU', 'name' => 'MHU'],
            ['code' => 'MGM', 'name' => 'MGM'],
            ['code' => 'SMM', 'name' => 'SMM'],
            ['code' => 'KPP', 'name' => 'KPP'],
            ['code' => 'ASMI', 'name' => 'ASMI'],
        ];

        foreach ($sites as $site) {
            DB::table('sites')->updateOrInsert(
                ['code' => $site['code']],
                [
                    'name' => $site['name'],
                    'description' => null,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('sites')->whereIn('code', [
            'ALL-SITE',
            'HO',
            'HO-ALL-SITE',
            'CAM',
            'MHU',
            'MGM',
            'SMM',
            'KPP',
            'ASMI',
        ])->delete();
    }
};

