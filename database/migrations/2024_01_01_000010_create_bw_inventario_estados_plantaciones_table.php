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

        Schema::create('bw_inventario_estados_plantaciones', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_estado')->default('0')->primary();
            $table->string('nombre', 64)->nullable()->default(null);
            $table->string('abreviatura', 18)->nullable()->default(null);
            $table->string('descripcion', 250)->nullable()->default(null);
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
        Schema::dropIfExists('bw_inventario_estados_plantaciones');
        Schema::enableForeignKeyConstraints();
    }
};
