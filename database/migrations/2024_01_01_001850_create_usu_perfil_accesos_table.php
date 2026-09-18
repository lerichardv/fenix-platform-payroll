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

        Schema::create('usu_perfil_accesos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_perfil');
            $table->integer('cod_modulo');
            $table->integer('cod_menu');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();
            $table->tinyInteger('todas_opciones')->nullable()->default('0');

            $table->primary(['cod_perfil', 'cod_modulo', 'cod_menu']);

            $table->index(['cod_modulo', 'cod_menu'], 'fk_usu_perfil_accesos_ug_menus1_idx');
            $table->index('user_insert', 'fk_usu_perfil_accesos_usu_usuarios1_idx');

            $table->foreign(['cod_modulo', 'cod_menu'], 'fk_usu_perfil_accesos_ug_menus1')->references(['cod_modulo', 'cod_menu'])->on('ug_menus');
            $table->foreign('cod_perfil', 'fk_usu_perfil_accesos_usu_perfiles1')->references('cod_perfil')->on('usu_perfiles');
            $table->foreign('user_insert', 'fk_usu_perfil_accesos_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_perfil_accesos');
        Schema::enableForeignKeyConstraints();
    }
};
