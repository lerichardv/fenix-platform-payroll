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

        Schema::create('ug_formularios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_formulario', true, false);
            $table->integer('cod_modulo');
            $table->integer('cod_menu');
            $table->integer('cod_info_empresa');
            $table->string('nombre_formulario', 100);
            $table->text('descripcion_formulario')->nullable();
            $table->tinyInteger('cod_periodo_notificacion')->default('1');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index(['cod_modulo', 'cod_menu'], 'fk_ug_formularios_ug_menus_idx');
            $table->index('user_insert', 'fk_ug_formularios_usu_usuarios_idx');
            $table->index('cod_info_empresa', 'fk_ug_formularios_bw_info_empresa_idx');

            $table->foreign('cod_info_empresa', 'fk_ug_formularios_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign(['cod_modulo', 'cod_menu'], 'fk_ug_formularios_ug_menus')->references(['cod_modulo', 'cod_menu'])->on('ug_menus');
            $table->foreign('user_insert', 'fk_ug_formularios_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_formularios');
        Schema::enableForeignKeyConstraints();
    }
};
