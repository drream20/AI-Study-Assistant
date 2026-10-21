<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('subject')->default('General');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('source_type')->default('upload');
            $table->string('source_file_name')->nullable();
            $table->json('generation_meta')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('study_set_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_set_id')->constrained('study_sets')->cascadeOnDelete();
            $table->string('question_type')->default('multiple_choice');
            $table->string('difficulty')->default('medium');
            $table->string('prompt');
            $table->text('explanation')->nullable();
            $table->string('correct_answer')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('study_set_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_set_question_id')->constrained('study_set_questions')->cascadeOnDelete();
            $table->string('label');
            $table->text('value');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('study_set_flashcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_set_id')->constrained('study_sets')->cascadeOnDelete();
            $table->text('front');
            $table->text('back');
            $table->string('review_status')->default('new');
            $table->timestamp('due_at')->nullable();
            $table->unsignedSmallInteger('interval_days')->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->float('ease_factor', 3, 2)->default(2.50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_set_flashcards');
        Schema::dropIfExists('study_set_question_options');
        Schema::dropIfExists('study_set_questions');
        Schema::dropIfExists('study_sets');
    }
};
