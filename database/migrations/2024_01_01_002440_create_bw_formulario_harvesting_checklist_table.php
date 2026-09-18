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

        Schema::create('bw_formulario_harvesting_checklist', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario', true, false);
            $table->integer('cod_detalle');
            $table->integer('cod_plantacion')->nullable()->default(null);
            $table->date('fecha_checklist');
            $table->tinyInteger('loose_bunches');
            $table->tinyInteger('conventional_organic');
            $table->tinyInteger('question1');
            $table->tinyInteger('question2');
            $table->tinyInteger('question3');
            $table->tinyInteger('question4');
            $table->tinyInteger('question5');
            $table->tinyInteger('question6');
            $table->tinyInteger('question7');
            $table->tinyInteger('question8');
            $table->tinyInteger('question9');
            $table->tinyInteger('question10');
            $table->tinyInteger('question11');
            $table->tinyInteger('question12');
            $table->tinyInteger('question13');
            $table->tinyInteger('question14');
            $table->tinyInteger('question15');
            $table->tinyInteger('question16');
            $table->tinyInteger('question17');
            $table->integer('question18');
            $table->integer('question19');
            $table->tinyInteger('question20');
            $table->tinyInteger('question21');
            $table->tinyInteger('question22');
            $table->text('actions');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_detalle', 'fk_form_harv_check_bw_det_blo_pla_idx');
            $table->index('user_insert', 'fk_bw_form_harv_check_usu_usuarios_idx');
            $table->index('cod_plantacion', 'fk_bw_form_harv_check_bw_plantaciones_idx');

            $table->foreign('cod_detalle', 'fk_bw_form_harv_check_bw_det_blo_pla')->references('cod_detalle')->on('bw_detalle_bloques_plantaciones');
            $table->foreign('cod_plantacion', 'fk_bw_form_harv_check_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_form_harv_check_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_formulario_harvesting_checklist');
        Schema::enableForeignKeyConstraints();
    }
};
