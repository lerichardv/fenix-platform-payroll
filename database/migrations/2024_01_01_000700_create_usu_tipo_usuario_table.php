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

        Schema::create('usu_tipo_usuario', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_tipo_usuario', true, false);
            $table->string('descripcion', 245)->nullable()->default(null);
            $table->string('etiqueta', 145)->nullable()->default(null);
            $table->string('etiqueta_english', 145)->nullable()->default(null);
            $table->string('identificador', 45)->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('cod_tipo_usuario', 'cod_tipo_usuario_UNIQUE');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_tipo_usuario');
        Schema::enableForeignKeyConstraints();
    }
};
