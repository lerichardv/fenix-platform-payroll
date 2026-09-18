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

        Schema::create('bw_inventario_grupos_siembra', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_grupo_siembra', true, false);
            $table->string('nombre', 250)->nullable()->default(null);
            $table->string('descripcion', 500)->nullable()->default(null);
            $table->tinyInteger('activo')->nullable()->default(null);
            $table->integer('user_insert')->nullable()->default(null);
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
        Schema::dropIfExists('bw_inventario_grupos_siembra');
        Schema::enableForeignKeyConstraints();
    }
};
