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

        Schema::create('bw_plantaciones_formulario_exploracion_observaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_observacion', true, false);
            $table->integer('cod_exploracion');
            $table->text('observacion');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_exploracion', 'fk_bw_pla_for_exp_obs_bw_pla_for_exp_idx');
            $table->index('user_insert', 'fk_bw_pla_for_exp_obs_usu_usuarios_idx');

            $table->foreign('cod_exploracion', 'fk_bw_pla_for_exp_obs_bw_pla_for_exp')->references('cod_exploracion')->on('bw_plantaciones_formulario_exploracion');
            $table->foreign('user_insert', 'fk_bw_pla_for_exp_obs_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_formulario_exploracion_observaciones');
        Schema::enableForeignKeyConstraints();
    }
};
