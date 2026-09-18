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

        Schema::create('ug_formularios_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario_item', true, false);
            $table->integer('cod_formulario');
            $table->integer('cod_tipo_item');
            $table->string('id_item', 45);
            $table->string('nombre_item', 75);
            $table->text('descripcion_item')->nullable();
            $table->integer('orden')->default('1');
            $table->tinyInteger('flag_alerta')->default('0');
            $table->integer('caracteres_max')->default('50');
            $table->integer('cod_tipo_mascara')->default('3');
            $table->tinyInteger('requerido')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_formulario', 'fk_ug_form_items_ug_form_idx');
            $table->index('cod_tipo_item', 'fk_ug_form_items_ug_form_tipo_items_idx');
            $table->index('user_insert', 'fk_ug_form_items_usu_usuarios_idx');

            $table->foreign('cod_tipo_item', 'fk_ug_form_items_ug_form_tipo_items')->references('cod_tipo_item')->on('ug_formularios_tipo_items');
            $table->foreign('cod_formulario', 'fk_ug_form_items_ug_forms')->references('cod_formulario')->on('ug_formularios');
            $table->foreign('user_insert', 'fk_ug_form_items_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_formularios_items');
        Schema::enableForeignKeyConstraints();
    }
};
