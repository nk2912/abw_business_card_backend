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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();



            //  company info
            $table->string('name');
            $table->string('industry');       // IT, Food, Trading
            $table->string('business_type');  // Software, Restaurant, Mobile App Dev
            $table->text('description')->nullable();

            // contact
            $table->string('address')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            // ownership
            $table->foreignId('created_by')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // audit (optional but ready)
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            // soft delete
            $table->softDeletes();

            // timestamps
            $table->timestamps();
        });

        Schema::create('company_socials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('platform');
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_socials');
        Schema::dropIfExists('companies');
    }
};
