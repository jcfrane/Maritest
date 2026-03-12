<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire_choices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_item_id')->constrained()->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire_choices');
    }
};
