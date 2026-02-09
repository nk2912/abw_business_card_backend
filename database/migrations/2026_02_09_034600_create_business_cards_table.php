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

            $table->string('position')->nullable();

            $table->json('phones')->nullable();
            $table->json('emails')->nullable();
            $table->json('addresses')->nullable();

            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();

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
