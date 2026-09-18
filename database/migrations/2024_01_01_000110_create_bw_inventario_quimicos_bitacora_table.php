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

        Schema::create('bw_inventario_quimicos_bitacora', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_bitacora', true, false);
            $table->integer('cod_inventario');
            $table->integer('cod_info_empresa');
            $table->string('cod_quimico', 5)->nullable()->default(null);
            $table->string('nombre_quimico', 100);
            $table->integer('cod_unidad_medida');
            $table->decimal('cantidad_quimico', 11, 3);
            $table->decimal('cantidad_fisica_quimico', 11, 3)->default('0.000');
            $table->integer('cod_ingrediente_activo')->nullable()->default(null);
            $table->string('registro_ambiental', 50)->nullable()->default(null);
            $table->string('periodo_reingreso', 15)->nullable()->default(null);
            $table->integer('cod_tipo_periodo_reingreso')->nullable()->default(null);
            $table->string('periodo_precosecha', 15)->nullable()->default(null);
            $table->integer('cod_tipo_periodo_precosecha')->nullable()->default(null);
            $table->decimal('dosis_minima', 11, 3)->nullable()->default('0.000');
            $table->decimal('dosis_maxima', 11, 3)->nullable()->default('0.000');
            $table->decimal('cantidad_minima_alerta', 11, 3)->nullable()->default('1.000');
            $table->decimal('precio_quimico', 11, 3)->nullable()->default('1.000');
            $table->integer('cod_tipo_quimico')->nullable()->default(null);
            $table->text('razon_aplicacion')->nullable();
            $table->text('etiqueta')->nullable();
            $table->text('hoja_seguridad')->nullable();
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
        Schema::dropIfExists('bw_inventario_quimicos_bitacora');
        Schema::enableForeignKeyConstraints();
    }
};
