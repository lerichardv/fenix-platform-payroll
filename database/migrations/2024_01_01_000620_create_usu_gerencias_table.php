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

        Schema::create('usu_gerencias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_gerencia', true, false);
            $table->string('gerencia', 60);
            $table->string('descripcion', 200)->nullable()->default(null);
            $table->string('abreviatura', 4)->nullable()->default(null);
            $table->tinyInteger('flag_biblioteca')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_usu_gerencias_usu_usuarios1_idx');

            $table->foreign('user_insert', 'fk_usu_gerencias_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_gerencias');
        Schema::enableForeignKeyConstraints();
    }
};
