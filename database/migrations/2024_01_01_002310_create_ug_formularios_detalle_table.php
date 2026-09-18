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

        Schema::create('ug_formularios_detalle', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_formulario');
            $table->integer('cod_estado');
            $table->text('observacion')->nullable();
            $table->tinyInteger('prioridad')->nullable()->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->integer('user_review')->nullable()->default(null);
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_formulario', 'fk_ug_formularios_detalle_ug_formularios_idx');
            $table->index('user_insert', 'fk_ug_formularios_detalle_usu_usuarios_idx');
            $table->index('cod_estado', 'fk_ug_formularios_detalle_ug_form_estados_idx');
            $table->index('user_review', 'fk_ug_formularios_detalle_usu_usuarios2_idx');

            $table->foreign('cod_estado', 'fk_ug_formularios_detalle_ug_form_estados')->references('cod_estado')->on('ug_formularios_estados');
            $table->foreign('cod_formulario', 'fk_ug_formularios_detalle_ug_formularios')->references('cod_formulario')->on('ug_formularios');
            $table->foreign('user_insert', 'fk_ug_formularios_detalle_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign('user_review', 'fk_ug_formularios_detalle_usu_usuarios2')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_formularios_detalle');
        Schema::enableForeignKeyConstraints();
    }
};
