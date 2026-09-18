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

        Schema::create('ug_menus', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_modulo');
            $table->integer('cod_menu');
            $table->string('menu', 45);
            $table->string('menu_english', 45)->nullable()->default(null);
            $table->string('descripcion', 200)->nullable()->default(null);
            $table->string('ruta', 300);
            $table->tinyInteger('principal')->default('0');
            $table->tinyInteger('sub_menu')->default('0');
            $table->integer('cod_modulo_depende')->nullable()->default(null);
            $table->integer('cod_menu_depende')->nullable()->default(null);
            $table->integer('orden')->nullable()->default(null);
            $table->integer('tipo_menu')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_modulo', 'cod_menu']);

            $table->index('user_insert', 'fk_ug_menus_usu_usuarios1_idx');

            $table->foreign('cod_modulo', 'fk_ug_menus_ug_modulos1')->references('cod_modulo')->on('ug_modulos');
            $table->foreign('user_insert', 'fk_ug_menus_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_menus');
        Schema::enableForeignKeyConstraints();
    }
};
