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
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->foreignId('type_service_id')->nullable()->constrained();
            $table->string('type_data')->nullable();
            $table->string('phone');
            $table->string('address')->nullable();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->text('description')->nullable();
            $table->double('lng')->nullable();
            $table->double('lat')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
