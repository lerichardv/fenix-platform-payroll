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
        Schema::disableForeignKeyConstraints();

        Schema::create('pay_miscellaneous', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_miscellaneous', true, false);
            $table->integer('cod_location');
            $table->integer('cod_activity');
            $table->integer('crop_age');
            $table->integer('cod_farm')->nullable()->default(null);
            $table->integer('cod_field')->nullable()->default(null);
            $table->integer('cod_bloque')->nullable()->default(null);
            $table->integer('cod_tipo_pago');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_miscellaneous', 'cod_miscellaneous_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_miscellaneous');
        Schema::enableForeignKeyConstraints();
    }
};
