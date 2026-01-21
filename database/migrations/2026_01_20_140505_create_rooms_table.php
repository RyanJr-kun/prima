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
            $table->integer('capacity');  
            $table->enum('type', ['teori', 'laboratorium'])->default('teori'); 
            $table->string('location')->index();
            $table->string('facility_tag')->nullable()->default('general');    
             
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
