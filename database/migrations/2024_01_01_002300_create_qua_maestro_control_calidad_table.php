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

        Schema::create('qua_maestro_control_calidad', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_control_calidad', true, false);
            $table->date('fecha');
            $table->integer('cod_pais');
            $table->integer('cod_departamento');
            $table->integer('cod_producto');
            $table->string('num_orden_compra', 15);
            $table->time('tiempo');
            $table->decimal('temperatura_actual', 7, 3);
            $table->decimal('temperatura_establecida', 7, 3);
            $table->decimal('temperatura_minima', 7, 3);
            $table->decimal('temperatura_maxima', 7, 3);
            $table->decimal('temperatura_media', 7, 3);
            $table->time('tiempo_preshipment')->nullable()->default(null);
            $table->string('num_lote', 30)->nullable()->default(null);
            $table->integer('dias')->nullable()->default(null);
            $table->decimal('middle_temp1', 7, 3)->nullable()->default(null);
            $table->decimal('middle_temp2', 7, 3)->nullable()->default(null);
            $table->decimal('middle_temp3', 7, 3)->nullable()->default(null);
            $table->decimal('middle_temp4', 7, 3)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('cod_producto', 'fk_qua_maestro_qua_productos_idx');
            $table->index(['cod_pais', 'cod_departamento'], 'fk_qua_maestro_geo_departamentos_idx');
            $table->index('user_insert', 'fk_qua_maestro_usu_usuarios_idx');

            $table->foreign(['cod_pais', 'cod_departamento'], 'fk_qua_maestro_geo_departamentos')->references(['cod_pais', 'cod_departamento'])->on('geo_departamentos');
            $table->foreign('cod_producto', 'fk_qua_maestro_qua_productos')->references('cod_producto')->on('qua_productos');
            $table->foreign('user_insert', 'fk_qua_maestro_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('qua_maestro_control_calidad');
        Schema::enableForeignKeyConstraints();
    }
};
