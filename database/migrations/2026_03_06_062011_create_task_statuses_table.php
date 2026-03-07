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
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('label');
            $table->string('color');
            $table->string('category')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['organization_id','name']);
            $table->index(['organization_id','sort_order']);
            $table->index(['organization_id','is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
