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

        Schema::create('bw_inventario_semilla', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_inventario')->primary();
            $table->integer('cod_info_empresa');
            $table->string('codigo_semilla', 45);
            $table->string('nombre_semilla', 100);
            $table->integer('cod_variedad');
            $table->integer('cod_sembrador')->default('1');
            $table->string('abreviatura_semilla', 10);
            $table->decimal('cantidad_semilla', 14, 3)->default('0.000');
            $table->integer('cod_unidad_medida');
            $table->decimal('cantidad_fisica_semilla', 11, 3)->default('0.000');
            $table->decimal('precio_unidad', 11, 3)->nullable()->default(null);
            $table->string('numero_lote', 15)->nullable()->default(null);
            $table->tinyInteger('flag_watercress')->default('0');
            $table->dateTime('fecha_sumar_restar')->nullable()->default(null);
            $table->tinyInteger('sumar_restar')->nullable()->default(null);
            $table->decimal('cantidad_sumar_restar', 11, 3)->nullable()->default(null);
            $table->text('razon_sumar_restar')->nullable();
            $table->integer('cod_categoria')->nullable()->default(null);
            $table->integer('cod_grupo_siembra')->nullable()->default(null);
            $table->integer('cod_rasgo')->nullable()->default(null);
            $table->integer('plants_acre')->nullable()->default(null);
            $table->integer('cod_familia')->nullable()->default(null);
            $table->string('red_zone', 100)->nullable()->default(null);
            $table->string('over_seed', 100)->nullable()->default(null);
            $table->string('semillas_por_plantaciones', 100)->default('0');
            $table->tinyInteger('paletizado')->nullable()->default(null);
            $table->tinyInteger('semilla_activa')->nullable()->default(null);
            $table->string('notas', 256)->nullable()->default(null);
            $table->string('og_supply', 256)->nullable()->default(null);
            $table->string('cod_vendedores', 256)->nullable()->default(null);
            $table->tinyInteger('germinacion_automatica')->default('0');
            $table->decimal('price', 10, 2)->default('0.00');
            $table->integer('cod_tipo_semilla')->default('1');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_variedad', 'fk_bw_inv_sembra_bw_variedad_sem_idx');
            $table->index('cod_unidad_medida', 'fk_bw_inv_sembra_ug_unidades_medida_idx');
            $table->index('user_insert', 'fk_bw_inv_sembra_usu_usuarios_idx');
            $table->index('cod_info_empresa', 'fk_bw_inv_sembra_usu_gerencias_idx');
            $table->index('cod_tipo_semilla', 'fk_bw_inv_sembra_bw_inventario_tipo_semillas_idx');
            $table->index('cod_tipo_semilla', 'fk_bw_inv_sembras_bw_inventario_tipo_semillas_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_inv_sembra_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_variedad', 'fk_bw_inv_sembra_bw_variedad_sem')->references('cod_variedad')->on('bw_variedad_sembradora');
            $table->foreign('cod_unidad_medida', 'fk_bw_inv_sembra_ug_unidades_medida')->references('cod_unidad_medida')->on('ug_unidades_medida');
            $table->foreign('user_insert', 'fk_bw_inv_sembra_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_semilla');
        Schema::enableForeignKeyConstraints();
    }
};
