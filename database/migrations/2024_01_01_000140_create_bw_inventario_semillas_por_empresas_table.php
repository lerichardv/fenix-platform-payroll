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

        Schema::create('bw_inventario_semillas_por_empresas', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->increments('cod_inventario');
            $table->integer('cod_inventario_semilla');
            $table->integer('cod_empresa');
            $table->decimal('cantidad_semilla', 11, 0)->default('0');
            $table->timestamp('ultima_actualizacion')->useCurrent();
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
        Schema::dropIfExists('bw_inventario_semillas_por_empresas');
        Schema::enableForeignKeyConstraints();
    }
};
