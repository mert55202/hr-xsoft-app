<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('thank_you')->nullable();
            $table->string('identity');
            $table->string('status')->default('draft');
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();
        });

        Schema::create('form_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort')->default(1);
            $table->timestamps();
        });

        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_section_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('prompt');
            $table->string('help')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('sort')->default(1);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('form_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('sort')->default(1);
            $table->unsignedInteger('points')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });

        Schema::create('form_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->unsignedInteger('sort')->default(1);
            $table->timestamps();
        });

        Schema::create('form_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->unique(['form_id', 'employee_id']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->unique(['form_id', 'employee_id']);
        });

        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_option_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('form_row_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body')->nullable();
            $table->decimal('value_number', 8, 2)->nullable();
            $table->date('value_date')->nullable();
            $table->time('value_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_answers');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_links');
        Schema::dropIfExists('form_rows');
        Schema::dropIfExists('form_options');
        Schema::dropIfExists('form_questions');
        Schema::dropIfExists('form_sections');
        Schema::dropIfExists('forms');
    }
};
