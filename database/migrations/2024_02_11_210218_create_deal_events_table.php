<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('deal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals')->onDelete('cascade');
            $table->string('deal_eventable_type');
            $table->unsignedBigInteger('deal_eventable_id');
            $table->enum('actor_type', ['seller', 'buyer', 'admin']);
            $table->enum('event_type', ['listing', 'purchase', 'report_delivery', 'report_receipt', 'cancel']);
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_events');
    }
};
