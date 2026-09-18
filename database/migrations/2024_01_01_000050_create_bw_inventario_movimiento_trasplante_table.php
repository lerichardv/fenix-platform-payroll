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

        Schema::create('bw_inventario_movimiento_trasplante', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_movimiento', true, false);
            $table->integer('cod_trasplante');
            $table->integer('cod_plantacion');
            $table->integer('cod_inventario');
            $table->string('numero_orden', 68)->nullable()->default(null);
            $table->string('cantidad_original', 10)->default('0');
            $table->string('cantidad_reducida', 10)->default('0');
            $table->string('cantidad_sobrante', 10)->default('0');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_movimiento', 'cod_movimiento_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_inventario_movimiento_trasplante');
        Schema::enableForeignKeyConstraints();
    }
};
