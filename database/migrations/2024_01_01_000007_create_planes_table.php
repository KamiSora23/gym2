<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id('id_plan');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('foto')->nullable();
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
