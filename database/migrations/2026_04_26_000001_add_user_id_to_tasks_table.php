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
        Schema::table('tasks', function (Blueprint $table): void {
            $table->foreignId('user_id')->after('id')->constrained()->cascadeOnDelete();
            $table->dropUnique('tasks_title_due_date_unique');
            $table->unique(['user_id', 'title', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropUnique('tasks_user_id_title_due_date_unique');
            $table->unique(['title', 'due_date']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};