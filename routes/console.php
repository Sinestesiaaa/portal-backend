<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('documents:relations:sync {--dry-run : Show missing reverse relations without inserting}', function () {
    $missingReverse = DB::table('document_relations as r')
        ->leftJoin('document_relations as rev', function ($join) {
            $join->on('rev.document_id', '=', 'r.related_document_id')
                ->on('rev.related_document_id', '=', 'r.document_id');
        })
        ->whereNull('rev.id')
        ->select('r.document_id', 'r.related_document_id')
        ->get();

    if ($missingReverse->isEmpty()) {
        $this->info('No one-way relations found. Data already two-way.');
        return;
    }

    $this->warn('Found ' . $missingReverse->count() . ' one-way relation(s).');

    foreach ($missingReverse as $row) {
        $from = DB::table('documents')->where('id', $row->document_id)->value('document_number') ?? $row->document_id;
        $to = DB::table('documents')->where('id', $row->related_document_id)->value('document_number') ?? $row->related_document_id;
        $this->line("- {$from} -> {$to}");
    }

    if ($this->option('dry-run')) {
        $this->comment('Dry run mode: no data changed.');
        return;
    }

    $inserted = 0;

    foreach ($missingReverse as $row) {
        $exists = DB::table('document_relations')
            ->where('document_id', $row->related_document_id)
            ->where('related_document_id', $row->document_id)
            ->exists();

        if (!$exists) {
            DB::table('document_relations')->insert([
                'document_id' => $row->related_document_id,
                'related_document_id' => $row->document_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $inserted++;
        }
    }

    $this->info("Inserted {$inserted} missing reverse relation(s).");
})->purpose('Ensure document relations are always two-way');
