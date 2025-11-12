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

            $table->string('display_name')->nullable();
            $table->string('profile_picture')->nullable();
            $table->text('bio')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            // Living Preferences
            $table->integer('budget_min')->nullable();
            $table->integer('budget_max')->nullable();
            $table->date('move_in_date')->nullable();
            
            // Lifestyle
            $table->enum('cleanliness', ['very_clean', 'clean', 'average', 'messy'])->nullable();
            $table->enum('schedule', ['morning_person', 'night_owl', 'flexible'])->nullable();
            $table->boolean('smokes')->nullable();
            $table->boolean('pets_ok')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Indexes for performance
            $table->index(['budget_min', 'budget_max']);
            
            // Foreign keys
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cluster_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
