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

        Schema::create('bw_detalle_items_respuestas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle_item', true, false);
            $table->integer('cod_info_empresa');
            $table->integer('cod_item');
            $table->integer('cod_plantacion');
            $table->integer('cod_detalle_item_checklist');
            $table->text('observacion')->nullable();
            $table->text('adjunto')->nullable();
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_item', 'fk_bw_detalle_items_bw_items_checklist_idx');
            $table->index('user_insert', 'fk_bw_detalle_items_usu_usuarios_idx');
            $table->index('cod_plantacion', 'fk_bw_detalle_items_bw_plantaciones_idx');
            $table->index('cod_info_empresa', 'fk_bw_detalle_items_checklist_usu_gerencias_idx');
            $table->index('cod_detalle_item_checklist', 'fk_bw_det_items_resp_bw_de_items_checklist_idx');

            $table->foreign('cod_detalle_item_checklist', 'fk_bw_det_items_resp_bw_de_items_checklist')->references('cod_detalle_item')->on('bw_detalle_items_checklist');
            $table->foreign('cod_info_empresa', 'fk_bw_detalle_items_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('cod_item', 'fk_bw_detalle_items_bw_items_checklist')->references('cod_item')->on('bw_items_checklist');
            $table->foreign('cod_plantacion', 'fk_bw_detalle_items_bw_plantaciones')->references('cod_plantacion')->on('bw_plantaciones');
            $table->foreign('user_insert', 'fk_bw_detalle_items_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_detalle_items_respuestas');
        Schema::enableForeignKeyConstraints();
    }
};
