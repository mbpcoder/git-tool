<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('commits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('repository_id')->constrained('repositories');
            $table->foreignId('author_user_id')->constrained('users');

            $table->string('branch', 64)->nullable()->index();
            $table->string('hash', 40);
            $table->text('message');
            $table->timestamp('commit_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commits');
    }
};
