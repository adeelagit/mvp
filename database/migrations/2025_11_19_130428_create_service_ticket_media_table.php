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
        Schema::create('service_ticket_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_ticket_id')->constrained()->onDelete('cascade');
            $table->string('file_path'); // path of the media
            $table->enum('type', ['image', 'video'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_ticket_media');
    }
};
