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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            
            // The user who added the card (the "Owner" of this relationship)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // The business card they added (The card being saved)
            $table->foreignId('business_card_id')->constrained()->cascadeOnDelete();

            // Friendship & interaction details
            $table->boolean('is_friend')->default(false);
            $table->string('friend_status')->default('none'); // none, pending, accepted
            
            // In case you want to tag/categorize the card in your collection
            $table->string('tag')->nullable(); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
