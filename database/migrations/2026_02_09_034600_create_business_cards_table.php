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
        Schema::create('business_cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();

            $table->string('name')->nullable(); // Added name field
            $table->string('position')->nullable();

            $table->json('phones')->nullable();
            $table->json('emails')->nullable();
            $table->json('addresses')->nullable();

            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            
            // "user_card" (another user) vs "my_card" (manual entry)
            $table->string('card_type')->default('my_card'); 
            
            // QR Code Data
            $table->text('qr_code_data')->nullable();

            // Social Links (replacing company_socials if needed here)
            $table->json('social_links')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_cards');
    }
};
