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

        Schema::create('bw_plantaciones_aplicar_quimicos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_aplicacion', true, false);
            $table->integer('cod_plantacion');
            $table->text('cod_bloques_aplicacion');
            $table->date('fecha_aplicacion_supervisor')->nullable()->default(null);
            $table->integer('cod_inventario_maquinaria')->nullable()->default(null);
            $table->integer('cod_tipo_aplicacion')->nullable()->default(null);
            $table->date('fecha_aplicacion_operador')->nullable()->default(null);
            $table->time('hora_inicial')->nullable()->default(null);
            $table->time('hora_final')->nullable()->default(null);
            $table->decimal('viento', 7, 3)->nullable()->default(null);
            $table->decimal('temperatura', 7, 3)->nullable()->default(null);
            $table->integer('cod_operador')->nullable()->default(null);
            $table->text('descripcion_aplicar_quimico')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->integer('user_update');
            $table->timestamp('updated_at')->nullable()->default(null);
            $table->timestamp('date_insert')->useCurrent();

            $table->fullText('cod_bloques_aplicacion', 'fk_bw_plan_apli_qui_bw_bloques');

            $table->index('cod_plantacion', 'fk_bw_plan_apli_qui_bw_plantaciones_idx');
            $table->index('cod_inventario_maquinaria', 'fk_bw_plan_apli_qui_bw_inventario_maquinaria_idx');
            $table->index('user_insert', 'fk_bw_plan_apli_qui_usu_usuarios_idx');
            $table->index('user_update', 'fk_bw_plan_apli_qui_usu_usuarios_id_update');

            $table->foreign('cod_inventario_maquinaria', 'fk_bw_plan_apli_qui_bw_inventario_maquinaria')->references('cod_inventario')->on('bw_inventario_maquinaria');
            $table->foreign('cod_plantacion', 'fk_bw_plan_apli_qui_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_plan_apli_qui_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_aplicar_quimicos');
        Schema::enableForeignKeyConstraints();
    }
};
