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

        Schema::create('bw_inventario_quimicos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_inventario')->primary();
            $table->integer('cod_info_empresa');
            $table->string('cod_quimico', 5)->nullable()->default(null);
            $table->string('nombre_quimico', 100);
            $table->integer('cod_unidad_medida');
            $table->decimal('cantidad_quimico', 11, 3);
            $table->decimal('cantidad_fisica_quimico', 11, 3)->default('0.000');
            $table->integer('cod_ingrediente_activo')->nullable()->default(null);
            $table->string('registro_ambiental', 50)->nullable()->default(null);
            $table->string('periodo_reingreso', 15)->nullable()->default(null);
            $table->integer('cod_tipo_periodo_reingreso')->nullable()->default(null);
            $table->string('periodo_precosecha', 15)->nullable()->default(null);
            $table->integer('cod_tipo_periodo_precosecha')->nullable()->default(null);
            $table->decimal('dosis_minima', 11, 3)->nullable()->default('0.000');
            $table->decimal('dosis_maxima', 11, 3)->nullable()->default('0.000');
            $table->decimal('cantidad_minima_alerta', 11, 3)->nullable()->default('1.000');
            $table->decimal('precio_quimico', 11, 3)->nullable()->default('1.000');
            $table->integer('cod_tipo_quimico')->nullable()->default(null);
            $table->text('razon_aplicacion')->nullable();
            $table->text('etiqueta')->nullable();
            $table->text('hoja_seguridad')->nullable();
            $table->dateTime('fecha_sumar_restar')->nullable()->default(null);
            $table->tinyInteger('sumar_restar')->nullable()->default(null);
            $table->decimal('cantidad_sumar_restar', 11, 3)->nullable()->default(null);
            $table->text('razon_sumar_restar')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_unidad_medida', 'fk_bw_inv_quimicos_ug_unidades-medida_idx');
            $table->index('cod_ingrediente_activo', 'fk_bw_inv_quimicos_bw_ingredientes_activos_idx');
            $table->index('cod_tipo_periodo_reingreso', 'fk_bw_inv_quimicos_bw_tipos_periodos_reingreso_idx');
            $table->index('cod_tipo_periodo_precosecha', 'fk_bw_inv_quimicos_bw_tipos_periodos_precosecha_idx');
            $table->index('user_insert', 'fk_bw_inv_quimicos_usu_usuarios_idx');
            $table->index('cod_tipo_quimico', 'fk_bw_inv_quimicos_bw_tipo_quimico_idx');
            $table->index('cod_info_empresa', 'fk_bw_inv_quimicos_usu_gerencias_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_inv_quimico_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_ingrediente_activo', 'fk_bw_inv_quimicos_bw_ingredientes_activos')->references('cod_ingrediente_activo')->on('bw_ingredientes_activos');
            $table->foreign('cod_tipo_quimico', 'fk_bw_inv_quimicos_bw_tipo_quimico')->references('cod_tipo_quimico')->on('bw_tipo_quimico');
            $table->foreign('cod_tipo_periodo_precosecha', 'fk_bw_inv_quimicos_bw_tipos_periodos_precosecha')->references('cod_tipo_periodo')->on('bw_tipos_periodos');
            $table->foreign('cod_tipo_periodo_reingreso', 'fk_bw_inv_quimicos_bw_tipos_periodos_reingreso')->references('cod_tipo_periodo')->on('bw_tipos_periodos');
            $table->foreign('cod_unidad_medida', 'fk_bw_inv_quimicos_ug_unidades-medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_inv_quimicos_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_quimicos');
        Schema::enableForeignKeyConstraints();
    }
};
