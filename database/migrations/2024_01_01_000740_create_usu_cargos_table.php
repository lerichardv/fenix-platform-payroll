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

        Schema::create('usu_cargos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_gerencia');
            $table->integer('cod_cargo');
            $table->string('cargo', 45);
            $table->string('descripcion', 200)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_gerencia', 'cod_cargo']);

            $table->index('cod_gerencia', 'fk_usu_cargos_usu_gerencias1_idx');
            $table->index('user_insert', 'fk_usu_cargos_usu_usuarios1_idx');

            $table->foreign('cod_gerencia', 'fk_usu_cargos_usu_gerencias1')->references('cod_gerencia')->on('usu_gerencias');
            $table->foreign('user_insert', 'fk_usu_cargos_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_cargos');
        Schema::enableForeignKeyConstraints();
    }
};
