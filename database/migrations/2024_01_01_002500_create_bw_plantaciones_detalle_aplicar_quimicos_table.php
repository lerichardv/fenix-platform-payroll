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

        Schema::create('bw_plantaciones_detalle_aplicar_quimicos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_aplicacion');
            $table->integer('cod_tipo_quimico');
            $table->integer('cod_inventario');
            $table->integer('cod_unidad_medida');
            $table->decimal('cantidad_sugerida', 11, 3);
            $table->decimal('cantidad_aplicada', 11, 3)->default('0.000');
            $table->integer('user_delete')->nullable()->default(null);
            $table->dateTime('date_delete')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_aplicacion', 'fk_bw_pla_det_apl_qui_bw_pla_apli_qui_idx');
            $table->index('cod_tipo_quimico', 'fk_bw_pla_det_apl_qui_bw_tipo_quimico_idx');
            $table->index('cod_inventario', 'fk_bw_pla_det_apl_qui_bw_inventario_quimicos_idx');
            $table->index('cod_unidad_medida', 'fk_bw_pla_det_apl_qui_ug_unidades_medida_idx');
            $table->index('user_insert', 'fk_bw_pla_det_apl_qui_usu_usuarios_idx');

            $table->foreign('cod_inventario', 'fk_bw_pla_det_apl_qui_bw_inventario_quimicos')->references('cod_inventario')->on('bw_inventario_quimicos');
            $table->foreign('cod_aplicacion', 'fk_bw_pla_det_apl_qui_bw_pla_apli_qui')->references('cod_aplicacion')->on('bw_plantaciones_aplicar_quimicos');
            $table->foreign('cod_tipo_quimico', 'fk_bw_pla_det_apl_qui_bw_tipo_quimico')->references('cod_tipo_quimico')->on('bw_tipo_quimico');
            $table->foreign('cod_unidad_medida', 'fk_bw_pla_det_apl_qui_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_pla_det_apl_qui_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_detalle_aplicar_quimicos');
        Schema::enableForeignKeyConstraints();
    }
};
