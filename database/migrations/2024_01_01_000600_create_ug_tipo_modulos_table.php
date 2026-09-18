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

        Schema::create('ug_tipo_modulos', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_tipo_modulo', true, false);
            $table->string('tipo_modulo', 45);
            $table->string('module_type', 45)->nullable()->default(null);
            $table->string('descripcion', 45)->nullable()->default(null);
            $table->string('activo', 45)->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_ug_tipo_modulo_usu_usuarios1_idx');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ug_tipo_modulos');
        Schema::enableForeignKeyConstraints();
    }
};
