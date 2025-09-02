<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('display_name');
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();
            $table->integer('age');
            $table->enum('gender', ['male', 'female', 'other']);
            
            // Living Preferences
            $table->integer('budget_min');
            $table->integer('budget_max');
            $table->date('move_in_date')->nullable();
            
            // Lifestyle
            $table->enum('cleanliness', ['very_clean', 'clean', 'average', 'messy']);
            $table->enum('schedule', ['morning_person', 'night_owl', 'flexible']);
            $table->boolean('smokes')->default(false);
            $table->boolean('pets_ok')->default(false);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Indexes for performance
            $table->index(['budget_min', 'budget_max']);
            $table->foreignID('user_id')->constrained()->ondelete('cascade');
            $table->foreignID('cluster_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
