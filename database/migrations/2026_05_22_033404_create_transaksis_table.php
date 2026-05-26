<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('total_harga'); // Pakai bigInteger biar aman di Postgres
            $table->bigInteger('bayar')->nullable();
            $table->bigInteger('kembali')->nullable();
            $table->timestamps(); // Postgres akan otomatis handle timestamp dengan timezone standar
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};