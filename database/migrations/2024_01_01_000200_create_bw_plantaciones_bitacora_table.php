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

        Schema::create('bw_plantaciones_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_plantacion');
            $table->integer('cod_info_empresa');
            $table->integer('anio_plantacion');
            $table->string('num_plantacion', 11);
            $table->date('fecha_plantacion_planeada')->nullable()->default(null);
            $table->date('fecha_plantacion_ejecutada')->nullable()->default(null);
            $table->decimal('acres_plantados', 7, 3);
            $table->integer('cod_usuario_planificacion')->nullable()->default(null);
            $table->integer('cod_usuario_plantacion')->nullable()->default(null);
            $table->integer('cod_estado')->default('1');
            $table->integer('cod_temporada')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->dateTime('date_insert');
            $table->integer('user_update')->nullable()->default(null);
            $table->timestamp('date_update')->nullable()->useCurrent();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_plantaciones_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
