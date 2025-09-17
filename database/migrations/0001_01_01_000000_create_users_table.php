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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('motDePasse');
            $table->integer('solde')->default(1000);
            $table->string('imageProfil')->default('default.jpg');
            $table->string('couleurProfil')->default('white');
            $table->integer('or')->default(0);
            $table->integer('argent')->default(0);
            $table->integer('bronze')->default(0);
            $table->integer('pointPerdu')->default(0);
            $table->string('bio')->nullable(); //ATTENTION MAX DE 255 CARACTERES
            $table->boolean('confirme')->default(false);
            $table->boolean('banni')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
