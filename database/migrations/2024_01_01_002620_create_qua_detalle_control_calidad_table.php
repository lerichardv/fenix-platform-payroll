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

        Schema::create('qua_detalle_control_calidad', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_detalle', true, false);
            $table->integer('cod_control_calidad');
            $table->integer('cod_cuarto_frio');
            $table->integer('cod_seccion');
            $table->time('tiempo');
            $table->string('valor', 75);
            $table->text('observaciones')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent()->useCurrentOnUpdate();

            $table->index('cod_control_calidad', 'fk_qua_detalle_qua_maestro_idx');
            $table->index('cod_seccion', 'fk_qua_detalle_control_calidad_qua_secciones_cuarto_frio_idx');
            $table->index('cod_cuarto_frio', 'fk_qua_detalle_control_calidad_qua_cuartos_frios_idx');

            $table->foreign('cod_cuarto_frio', 'fk_qua_detalle_control_calidad_qua_cuartos_frios')->references('cod_cuarto')->on('qua_cuartos_frios');
            $table->foreign('cod_seccion', 'fk_qua_detalle_control_calidad_qua_secciones_cuarto_frio')->references('cod_seccion')->on('qua_secciones_cuarto_frio');
            $table->foreign('cod_control_calidad', 'fk_qua_detalle_qua_maestro')->references('cod_control_calidad')->on('qua_maestro_control_calidad');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('qua_detalle_control_calidad');
        Schema::enableForeignKeyConstraints();
    }
};
