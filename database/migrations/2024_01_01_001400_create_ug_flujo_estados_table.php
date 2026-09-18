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

        Schema::create('ug_flujo_estados', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_flujo', true, false);
            $table->integer('cod_estado_actual');
            $table->integer('cod_estado_ok');
            $table->integer('cod_estado_cancel');
            $table->tinyInteger('flag_flujo')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_estado_actual', 'fk_ug_flujo_estados_bw_estados_plantaciones1_idx');
            $table->index('cod_estado_ok', 'fk_ug_flujo_estados_bw_estados_plantaciones2_idx');
            $table->index('cod_estado_cancel', 'fk_ug_flujo_estados_bw_estados_plantaciones3_idx');
            $table->index('user_insert', 'fk_ug_flujo_estados_usu_usuarios_idx');

            $table->foreign('cod_estado_actual', 'fk_ug_flujo_estados_bw_estados_plantaciones1')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('cod_estado_ok', 'fk_ug_flujo_estados_bw_estados_plantaciones2')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('cod_estado_cancel', 'fk_ug_flujo_estados_bw_estados_plantaciones3')->references('cod_estado_plantacion')->on('bw_estados_plantacion');
            $table->foreign('user_insert', 'fk_ug_flujo_estados_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_flujo_estados');
        Schema::enableForeignKeyConstraints();
    }
};
