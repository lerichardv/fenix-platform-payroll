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

        Schema::create('pay_actividades_por_dia', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_actividad_por_dia', true, false);
            $table->string('actividad_por_dia', 255);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert')->default('1');
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
        Schema::dropIfExists('pay_actividades_por_dia');
        Schema::enableForeignKeyConstraints();
    }
};
