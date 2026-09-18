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

        Schema::create('bw_detalle_bloques_plantaciones_exploracion_adjuntos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_adjunto', true, false);
            $table->integer('cod_exploracion');
            $table->text('adjunto');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_exploracion', 'fk_bw_det_blo_pla_exp_adj_bw_det_blo_pla_exp_idx');
            $table->index('user_insert', 'fk_bw_det_blo_pla_exp_adj_usu_usuarios_idx');

            $table->foreign('cod_exploracion', 'fk_bw_det_blo_pla_exp_adj_bw_det_blo_pla_exp')->references('cod_exploracion')->on('bw_detalle_bloques_plantaciones_exploracion');
            $table->foreign('user_insert', 'fk_bw_det_blo_pla_exp_adj_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_bloques_plantaciones_exploracion_adjuntos');
        Schema::enableForeignKeyConstraints();
    }
};
