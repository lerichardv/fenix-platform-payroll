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

        Schema::create('ug_modulos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_modulo', true, false);
            $table->string('nombre', 100);
            $table->string('nombre_english', 100)->nullable()->default(null);
            $table->string('descripcion', 200)->nullable()->default(null);
            $table->integer('cod_tipo_modulo');
            $table->string('ruta', 300);
            $table->string('tabla_principal', 60)->nullable()->default(null);
            $table->string('llave_primaria', 60)->nullable()->default(null);
            $table->integer('orden')->nullable()->default(null);
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_ug_modulos_usu_usuarios1_idx');
            $table->index('cod_tipo_modulo', 'fk_ug_modulos_ug_tipo_modulo1_idx');

            $table->foreign('cod_tipo_modulo', 'fk_ug_modulos_ug_tipo_modulo1')->references('cod_tipo_modulo')->on('ug_tipo_modulos');
            $table->foreign('user_insert', 'fk_ug_modulos_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_modulos');
        Schema::enableForeignKeyConstraints();
    }
};
