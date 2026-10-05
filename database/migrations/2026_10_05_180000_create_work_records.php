<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('hidden_modules')->nullable()->after('department_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->boolean('is_candidate')->default(false)->after('user_id');
            $table->string('pay_type', 16)->nullable()->after('started_on');
            $table->decimal('pay_amount', 12, 2)->nullable()->after('pay_type');
            $table->string('currency', 8)->default('TRY')->after('pay_amount');
            $table->date('left_on')->nullable()->after('currency');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->string('template', 40)->nullable()->unique()->after('public_id');
        });

        Schema::create('lookups', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 40);
            $table->string('name');
            $table->json('meta')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lookup_id')->constrained('lookups')->cascadeOnDelete();
            $table->string('unit', 8);
            $table->decimal('amount', 8, 2)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'lookup_id', 'unit']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lookup_id')->constrained('lookups')->cascadeOnDelete();
            $table->string('mode', 8);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->decimal('hours', 6, 2)->nullable();
            $table->string('status', 16)->default('pending');
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('hours', 6, 2)->nullable();
            $table->decimal('gross', 12, 2);
            $table->decimal('deduction', 12, 2)->default(0);
            $table->decimal('net', 12, 2);
            $table->timestamps();
            $table->unique(['employee_id', 'year', 'month']);
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('given_on')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lookup_id')->nullable()->constrained('lookups')->nullOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('work_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lookup_id')->nullable()->constrained('lookups')->nullOnDelete();
            $table->text('body');
            $table->string('status', 16)->default('open');
            $table->timestamps();
        });

        Schema::create('staffing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('headcount')->default(1);
            $table->string('status', 16)->default('open');
            $table->timestamps();
        });

        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('held_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('candidate_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('status', 16)->default('draft');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('course_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('held_on');
            $table->foreignId('pre_form_id')->nullable()->constrained('forms')->nullOnDelete();
            $table->foreignId('post_form_id')->nullable()->constrained('forms')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->boolean('attended')->default(false);
            $table->timestamps();
            $table->unique(['course_session_id', 'employee_id']);
        });

        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('collar', 16);
            $table->string('period', 32);
            $table->text('body');
            $table->string('status', 16)->default('draft');
            $table->timestamps();
        });

        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->date('observed_on');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('served_on');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('starts_on');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->boolean('done')->default(false);
            $table->timestamps();
        });

        Schema::create('safety_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->date('recorded_on');
            $table->timestamps();
        });

        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('low_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('high_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['low_user_id', 'high_user_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('name')->default('telefon');
            $table->timestamps();
        });

        Schema::create('employee_moves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('to_position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('moved_on');
            $table->timestamps();
        });

        Schema::create('employee_health', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('blood_lookup_id')->nullable()->constrained('lookups')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_relatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('kinship')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind', 16);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_notes');
        Schema::dropIfExists('employee_relatives');
        Schema::dropIfExists('employee_health');
        Schema::dropIfExists('employee_moves');
        Schema::dropIfExists('api_tokens');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('safety_records');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('events');
        Schema::dropIfExists('meals');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('observations');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('competencies');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('course_sessions');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('candidate_references');
        Schema::dropIfExists('interviews');
        Schema::dropIfExists('staffing_requests');
        Schema::dropIfExists('work_requests');
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('lookups');
        Schema::table('forms', function (Blueprint $table) {
            $table->dropUnique(['template']);
            $table->dropColumn('template');
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['is_candidate', 'pay_type', 'pay_amount', 'currency', 'left_on']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('hidden_modules');
        });
    }
};
