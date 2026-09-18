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

        Schema::create('pay_bitacora_escaneos_realizados', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_escaneo', true, false);
            $table->integer('cod_crew');
            $table->decimal('pieces', 10, 3)->default('1.000');
            $table->integer('cod_estado_job')->default('1');
            $table->tinyInteger('terminado')->default('0');
            $table->time('hora_fuerza_terminado')->nullable()->default(null);
            $table->string('GPS', 300)->nullable()->default(null);
            $table->tinyInteger('registro_manual')->nullable()->default('0');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_escaneo', 'cod_escaneo_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('pay_bitacora_escaneos_realizados');
        Schema::enableForeignKeyConstraints();
    }
};
