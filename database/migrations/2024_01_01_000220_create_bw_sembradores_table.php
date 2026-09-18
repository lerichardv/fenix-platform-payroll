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

        Schema::create('bw_sembradores', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_sembrador', true, false);
            $table->string('nombre_contacto', 100)->nullable()->default(null);
            $table->string('correo_contacto', 100)->nullable()->default(null);
            $table->string('telefono_contacto', 45)->nullable()->default(null);
            $table->text('observaciones')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert')->nullable()->default(null);
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_sembrador', 'cod_sembrador_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_sembradores');
        Schema::enableForeignKeyConstraints();
    }
};
