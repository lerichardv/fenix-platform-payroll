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

        Schema::create('pay_harvests_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_harvest_fields', true, false);
            $table->integer('cod_harvest');
            $table->integer('cod_field');

            $table->unique('cod_harvest_fields', 'cod_harvest_farm_UNIQUE');

            $table->index('cod_field', 'idx_pay_harvests_fields_cod_field');
            $table->index('cod_harvest', 'idx_pay_harvests_fields_cod_harvest');

            $table->foreign('cod_field', 'fk_pay_harvest_field_far_fields')->references('cod_field')->on('far_fields');
            $table->foreign('cod_harvest', 'fk_pay_harvest_field_pay_harvest')->references('cod_harvest')->on('pay_harvests');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_harvests_fields');
        Schema::enableForeignKeyConstraints();
    }
};
