<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('gray');
            $table->integer('days')->default(5);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
