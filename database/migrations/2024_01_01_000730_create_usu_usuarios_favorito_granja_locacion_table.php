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

        Schema::create('usu_usuarios_favorito_granja_locacion', function (Blueprint $table) {
            $table->engine = 'MyISAM';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_farm');
            $table->integer('cod_locacion');
            $table->integer('cod_usuario');
            $table->integer('user_insert')->nullable()->default(null);
            $table->timestamp('date_insert')->useCurrent();

            $table->primary(['cod_farm', 'cod_usuario', 'cod_locacion']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_usuarios_favorito_granja_locacion');
        Schema::enableForeignKeyConstraints();
    }
};
