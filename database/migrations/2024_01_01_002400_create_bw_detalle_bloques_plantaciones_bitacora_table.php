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

        Schema::create('bw_detalle_bloques_plantaciones_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_detalle');
            $table->integer('cod_plantacion');
            $table->integer('cod_bloque');
            $table->decimal('cantidad_acres', 11, 3);
            $table->integer('cod_estado_plantacion')->nullable()->default(null);
            $table->text('motivo_estado_plantacion')->nullable();
            $table->tinyInteger('activo')->nullable()->default('1');
            $table->integer('user_insert');
            $table->dateTime('date_insert');
            $table->integer('user_update');
            $table->timestamp('date_update')->useCurrent();

            $table->index('cod_detalle', 'fk_bw_det_blo_pla_bit_bw_det_blo_pla_idx');
            $table->index('user_update', 'fk_bw_det_blo_pla_bit_usu_usuarios_idx');

            $table->foreign('cod_detalle', 'fk_bw_det_blo_pla_bit_bw_det_blo_pla')->references('cod_detalle')->on('bw_detalle_bloques_plantaciones');
            $table->foreign('user_update', 'fk_bw_det_blo_pla_bit_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_bloques_plantaciones_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
