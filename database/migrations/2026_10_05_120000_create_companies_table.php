<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();
            $table->text('kvkk')->nullable();
            $table->string('primary_color', 7)->default('#0d6efd');
            $table->unsignedInteger('personnel_quota')->nullable();
            $table->date('founded_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
