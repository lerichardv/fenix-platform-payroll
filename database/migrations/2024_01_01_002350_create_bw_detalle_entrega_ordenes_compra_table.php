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

        Schema::create('bw_detalle_entrega_ordenes_compra', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_entrega', true, false);
            $table->integer('cod_detalle');
            $table->decimal('cantidad', 11, 3);
            $table->integer('cod_unidad_medida');
            $table->dateTime('fecha_entrega');
            $table->text('observaciones')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_detalle', 'fk_bw_det_en_or_com_bw_det_or_com_idx');
            $table->index('cod_unidad_medida', 'fk_bw_det_en_or_com_ug_unidades_medida_idx');
            $table->index('user_insert', 'fk_bw_det_en_or_com_usu_usuarios_idx');

            $table->foreign('cod_detalle', 'fk_bw_det_en_or_com_bw_det_or_com')->references('cod_detalle')->on('bw_detalle_ordenes_compra');
            $table->foreign('cod_unidad_medida', 'fk_bw_det_en_or_com_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_det_en_or_com_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_entrega_ordenes_compra');
        Schema::enableForeignKeyConstraints();
    }
};
