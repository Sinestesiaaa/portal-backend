<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Default types
        $now = now();
        DB::table('document_types')->insert([
            ['name' => 'SOP', 'description' => 'Standard Operating Procedure', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IK', 'description' => 'Instruksi Kerja', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'FORM', 'description' => 'Formulir', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'STD', 'description' => 'Standar', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
