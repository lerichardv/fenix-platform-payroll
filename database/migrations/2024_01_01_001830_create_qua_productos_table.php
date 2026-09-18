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

        Schema::create('qua_productos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_producto', true, false);
            $table->string('nombre_producto', 100);
            $table->integer('cod_pais')->nullable()->default(null);
            $table->integer('cod_departamento')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index(['cod_pais', 'cod_departamento'], 'fk_qua_productos_geo_departamentos_idx');
            $table->index('user_insert', 'fk_qua_productos_usu_usuarios_idx');

            $table->foreign(['cod_pais', 'cod_departamento'], 'fk_qua_productos_geo_departamentos')->references(['cod_pais', 'cod_departamento'])->on('geo_departamentos');
            $table->foreign('user_insert', 'fk_qua_productos_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('qua_productos');
        Schema::enableForeignKeyConstraints();
    }
};
