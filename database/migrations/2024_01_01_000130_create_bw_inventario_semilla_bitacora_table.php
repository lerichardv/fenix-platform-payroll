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

        Schema::create('bw_inventario_semilla_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_inventario');
            $table->integer('cod_info_empresa');
            $table->string('codigo_semilla', 45);
            $table->string('nombre_semilla', 100);
            $table->integer('cod_variedad');
            $table->string('abreviatura_semilla', 2);
            $table->decimal('cantidad_semilla', 11, 3)->default('0.000');
            $table->integer('cod_unidad_medida');
            $table->decimal('cantidad_fisica_semilla', 11, 3)->default('0.000');
            $table->decimal('precio_unidad', 11, 3)->nullable()->default(null);
            $table->string('numero_lote', 15)->nullable()->default(null);
            $table->tinyInteger('flag_watercress')->default('0');
            $table->dateTime('fecha_sumar_restar')->nullable()->default(null);
            $table->tinyInteger('sumar_restar')->nullable()->default(null);
            $table->decimal('cantidad_sumar_restar', 11, 3)->nullable()->default(null);
            $table->text('razon_sumar_restar')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();
            $table->integer('user_update');
            $table->timestamp('date_update')->useCurrent();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_semilla_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
