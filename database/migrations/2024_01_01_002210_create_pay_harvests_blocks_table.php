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

        Schema::create('pay_harvests_blocks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_harvests_blocks', true, false);
            $table->integer('cod_harvest');
            $table->integer('cod_block');
            $table->integer('cod_plantacion');

            $table->unique('cod_harvests_blocks', 'cod_harvests_blocks_UNIQUE');

            $table->index('cod_block', 'idx_pay_harvests_blocks_cod_block');
            $table->index('cod_plantacion', 'idx_pay_harvests_blocks_cod_plantacion');
            $table->index('cod_harvest', 'idx_pay_harvests_blocks_cod_harvest');

            $table->foreign('cod_plantacion', 'fk_inventario_plantaciones_pay_harvest_block')->references('cod_plantacion')->on('bw_inventario_plantaciones');
            $table->foreign('cod_block', 'fk_pay_harvest_block_far_bloque')->references('cod_bloque')->on('far_bloques');
            $table->foreign('cod_harvest', 'fk_pay_harvest_block_pay_harvest')->references('cod_harvest')->on('pay_harvests');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_harvests_blocks');
        Schema::enableForeignKeyConstraints();
    }
};
