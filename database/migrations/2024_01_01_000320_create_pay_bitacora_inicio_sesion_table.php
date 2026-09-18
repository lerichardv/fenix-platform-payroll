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

        Schema::create('pay_bitacora_inicio_sesion', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';

            $table->integer('cod_inicio_sesion', true, false);
            $table->integer('cod_usuario')->nullable()->default(null);
            $table->timestamp('fecha_ingreso')->nullable()->default(null);
            $table->timestamp('fecha_egreso')->nullable()->default(null);
            $table->string('metodo_ingreso', 45)->nullable()->default(null);
            $table->tinyInteger('registro_modificado')->default('0');
            $table->double('latitud')->nullable()->default(null);
            $table->double('longitud')->nullable()->default(null);
            $table->tinyInteger('lunch_acreditado')->nullable()->default('0');
            $table->tinyInteger('lunch_automatico')->nullable()->default('1');
            $table->integer('cod_farm_co')->nullable()->default(null);
            $table->integer('cod_location_co')->nullable()->default(null);
            $table->integer('cod_farm_ci')->nullable()->default(null);
            $table->integer('cod_location_ci')->nullable()->default(null);
            $table->integer('cod_actividad_por_dia')->default('1');
            $table->timestamp('date_insert')->nullable()->useCurrent();

            $table->unique('cod_inicio_sesion', 'cod_inicio_sesion_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_bitacora_inicio_sesion');
        Schema::enableForeignKeyConstraints();
    }
};
