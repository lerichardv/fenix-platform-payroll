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

        Schema::create('bw_inventario_plantaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_plantacion', true, false);
            $table->integer('cod_inventario');
            $table->integer('cod_info_empresa');
            $table->integer('cod_estado')->nullable()->default(null);
            $table->string('numero_orden', 68)->nullable()->default(null);
            $table->string('cantidad', 10)->nullable()->default(null);
            $table->string('overseed', 10)->nullable()->default(null);
            $table->string('total', 10)->nullable()->default(null);
            $table->string('per_planting', 10)->nullable()->default(null);
            $table->timestamp('fecha_de_orden')->nullable()->default(null);
            $table->string('item', 45)->nullable()->default(null);
            $table->unsignedInteger('edad')->default('1');
            $table->timestamp('fecha_inicial')->nullable()->default(null);
            $table->timestamp('fecha_final')->nullable()->default(null);
            $table->timestamp('fecha_de_entrega')->nullable()->default(null);
            $table->tinyInteger('estado_entrega')->default('0');
            $table->integer('completado')->default('0');
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_plantaciones');
        Schema::enableForeignKeyConstraints();
    }
};
