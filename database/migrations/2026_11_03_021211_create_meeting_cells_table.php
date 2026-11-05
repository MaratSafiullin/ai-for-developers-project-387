<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Every meeting occupies one cell per 15 minutes of its interval. The
     * unique index on the cell start forbids overlapping meetings (ADR 0002).
     */
    public function up(): void
    {
        Schema::create('meeting_cells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_cells');
    }
};
