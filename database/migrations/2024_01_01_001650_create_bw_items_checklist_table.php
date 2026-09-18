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

        Schema::create('bw_items_checklist', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_item', true, false);
            $table->integer('cod_formulario');
            $table->integer('cod_tipo_item');
            $table->string('nombre_item', 150);
            $table->text('descripcion_item')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_items_checklist_usu_usuarios_idx');
            $table->index('cod_formulario', 'fk_bw_items_check_list_bw_formularios_idx');
            $table->index('cod_tipo_item', 'fk_bw_items_checklist_bw_tipo_item_checklist_idx');

            $table->foreign('cod_formulario', 'fk_bw_items_check_list_bw_formularios')->references('cod_formulario')->on('bw_formularios');
            $table->foreign('cod_tipo_item', 'fk_bw_items_checklist_bw_tipo_item_checklist')->references('cod_tipo_item')->on('bw_tipos_items');
            $table->foreign('user_insert', 'fk_bw_items_checklist_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_items_checklist');
        Schema::enableForeignKeyConstraints();
    }
};
