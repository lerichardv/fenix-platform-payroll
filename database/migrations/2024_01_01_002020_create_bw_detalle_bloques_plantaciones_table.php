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

        Schema::create('bw_detalle_bloques_plantaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_plantacion');
            $table->integer('cod_bloque');
            $table->decimal('cantidad_acres', 11, 3);
            $table->integer('cod_estado_plantacion')->default('1');
            $table->text('motivo_estado_plantacion')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();
            $table->integer('user_update')->nullable()->default(null);

            $table->index('cod_plantacion', 'fk_bw_detalle_bw_plantacion_idx');
            $table->index('cod_bloque', 'fk_bw_detalle_bw_bloques_idx');
            $table->index('user_insert', 'fk_bw_detalle_usu_usuarios_idx');
            $table->index('cod_estado_plantacion', 'fk_bw_detalle_bloques_plantaciones_bw_estados_plantacion_idx');

            $table->foreign('cod_bloque', 'fk_bw_detalle_bloques_plantaciones_bw_bloques')->references('cod_bloque')->on('bw_bloques');
            $table->foreign('cod_estado_plantacion', 'fk_bw_detalle_bloques_plantaciones_bw_estados_plantacion')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('cod_plantacion', 'fk_bw_detalle_bloques_plantaciones_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_detalle_bloques_plantaciones_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_bloques_plantaciones');
        Schema::enableForeignKeyConstraints();
    }
};
