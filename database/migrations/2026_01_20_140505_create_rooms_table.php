<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');

            $table->enum('location', ['kampus_1', 'kampus_2']);
            $table->string('building')->nullable();
            $table->integer('floor')->default(1);

            $table->integer('capacity');
            $table->enum('type', ['teori', 'laboratorium', 'aula'])->default('teori');
            $table->json('facility_tags')->nullable()->default('general');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
