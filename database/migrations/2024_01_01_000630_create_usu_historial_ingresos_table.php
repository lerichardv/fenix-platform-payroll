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

        Schema::create('usu_historial_ingresos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_bin';

            $table->integer('cod_historial_ingreso', true, false);
            $table->integer('cod_usuario');
            $table->timestamp('date_ingreso')->useCurrent()->useCurrentOnUpdate();
            $table->string('ip_ingreso', 15)->collation('latin1_bin');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_historial_ingresos');
        Schema::enableForeignKeyConstraints();
    }
};
