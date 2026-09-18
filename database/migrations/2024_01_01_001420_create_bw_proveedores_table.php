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

        Schema::create('bw_proveedores', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_proveedor')->primary();
            $table->integer('cod_info_empresa');
            $table->string('nombre_empresa', 100);
            $table->string('nombre_contacto', 100);
            $table->string('correo_contacto', 100);
            $table->string('telefono_contacto', 45)->nullable()->default(null);
            $table->text('observaciones')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('user_insert');
            $table->timestamp('date_insert')->useCurrent();

            $table->index('user_insert', 'fk_bw_provvedores_usu_usuarios_idx');
            $table->index('cod_info_empresa', 'fk_bw_proveedores_usu_gerencias_idx');

            $table->foreign('cod_info_empresa', 'fk_bw_proveedores_bw_info_empresa')->references('cod_info_empresa')->on('bw_info_empresa');
            $table->foreign('user_insert', 'fk_bw_provvedores_usu_usuarios')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bw_proveedores');
        Schema::enableForeignKeyConstraints();
    }
};
