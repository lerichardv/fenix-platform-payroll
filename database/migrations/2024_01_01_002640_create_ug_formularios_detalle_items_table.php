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

        Schema::create('ug_formularios_detalle_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle_item', true, false);
            $table->integer('cod_formulario_item');
            $table->string('texto_detalle', 75);
            $table->string('valor_detalle', 150)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_formulario_item', 'fk_ug_form_det_items_ug_fomr_items_idx');
            $table->index('user_insert', 'fk_ug_form_det_items_usu_usuarios_idx');

            $table->foreign('cod_formulario_item', 'fk_ug_form_det_items_ug_fomr_items')->references('cod_formulario_item')->on('ug_formularios_items');
            $table->foreign('user_insert', 'fk_ug_form_det_items_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_formularios_detalle_items');
        Schema::enableForeignKeyConstraints();
    }
};
