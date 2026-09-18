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

        Schema::create('bw_formulario_harvesting_worksheet', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario', true, false);
            $table->integer('cod_detalle')->nullable()->default(null);
            $table->integer('cod_plantacion')->nullable()->default(null);
            $table->dateTime('harvest_date');
            $table->tinyInteger('phi');
            $table->tinyInteger('cellos');
            $table->string('increment_bunch_cello', 45);
            $table->tinyInteger('area_finished');
            $table->decimal('acres_harvested', 7, 3);
            $table->text('commments_harvesting_worksheet')->nullable();
            $table->string('orden_compra', 15)->nullable()->default(null);
            $table->string('crop_number', 10)->nullable()->default(null);
            $table->decimal('cantidad_cosechada', 11, 3)->nullable()->default(null);
            $table->decimal('cantidad_empacada', 11, 3)->nullable()->default(null);
            $table->date('date_packed')->nullable()->default(null);
            $table->decimal('totes_harvested', 11, 3)->nullable()->default(null);
            $table->decimal('totes_packed', 11, 3)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_detalle', 'fk_form_harv_work_bw_det_blo_pla_idx');
            $table->index('user_insert', 'fk_form_harv_work_usu_usuarios_idx');
            $table->index('cod_plantacion', 'fk_form_harv_work_bw_plantaciones_idx');

            $table->foreign('cod_detalle', 'fk_form_harv_work_bw_det_blo_pla')->references('cod_detalle')->on('bw_detalle_bloques_plantaciones');
            $table->foreign('cod_plantacion', 'fk_form_harv_work_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_form_harv_work_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_formulario_harvesting_worksheet');
        Schema::enableForeignKeyConstraints();
    }
};
