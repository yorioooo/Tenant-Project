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
       Schema::create('transactions', function (Blueprint $table) {
           $table->id();
           $table->string('tenant_name');
           $table->string('address');
           $table->date('date_transaction');
           $table->integer('interval');
           $table->timestamp('date_time_transaction');
           $table->string('transaction_id');
           $table->decimal('total_amount_gross', 15, 2);
           $table->decimal('total_amount_net', 15, 2);
           $table->decimal('discount', 15, 2);
           $table->decimal('tax', 15, 2);
           $table->decimal('service', 15, 2);
           $table->string('status_sync');
           $table->string('cashier');
           $table->timestamps();
       });
   }
   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
