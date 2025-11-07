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
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('category', [
                'Low Battery / Charging Help',
                'Mechanical Issue',
                'Battery Swap Needed',
                'Flat Tyre',
                'Tow / Pickup Required',
                'Other'
            ]);
            $table->string('other_text')->nullable();
            $table->string('media_path')->nullable();
            $table->enum('status', ['Open', 'In Progress', 'Resolved'])->default('Open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_tickets');
    }
};
