<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->string('kategori'); // SOP, IK, STD, FORM
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('department_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('file_path');

            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
