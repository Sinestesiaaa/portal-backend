<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('revision_number')->default(0)->after('form_description_path');
            $table->string('previous_file_path')->nullable()->after('revision_number');
            $table->text('revision_note')->nullable()->after('previous_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['revision_number', 'previous_file_path', 'revision_note']);
        });
    }
};
