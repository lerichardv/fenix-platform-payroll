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

        Schema::create('bw_plantaciones_rociado', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_rociado', true, false);
            $table->integer('cod_plantacion');
            $table->text('cod_zona');
            $table->text('cod_bloques');
            $table->text('cod_tipo_zona');
            $table->integer('cod_inventario_quimico');
            $table->decimal('cantidad_quimico', 10, 3)->default('1.000');
            $table->integer('cod_unidad_medida');
            $table->dateTime('fecha_rociado')->useCurrent();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_plantacion', 'fk_bw_plantaciones_rociado_bw_plantaciones_idx');
            $table->index('user_insert', 'fk_bw_plantaciones_rociado_usu_usuarios_idx');
            $table->index('cod_inventario_quimico', 'fk_bw_plantaciones_rociado_bw_inventario_quimicos_idx');
            $table->index('cod_unidad_medida', 'fk_bw_plantaciones_rociado_ug_unidades_medida_idx');

            $table->foreign('cod_inventario_quimico', 'fk_bw_plantaciones_rociado_bw_inventario_quimicos')->references('cod_inventario')->on('bw_inventario_quimicos');
            $table->foreign('cod_plantacion', 'fk_bw_plantaciones_rociado_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('cod_unidad_medida', 'fk_bw_plantaciones_rociado_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_plantaciones_rociado_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_rociado');
        Schema::enableForeignKeyConstraints();
    }
};
