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

        Schema::create('usu_usuarios', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'latin1';
            $table->collation = 'latin1_swedish_ci';

            $table->integer('cod_usuario', true, false);
            $table->string('usuario', 45);
            $table->string('pass', 60);
            $table->string('nombre_1', 45);
            $table->string('nombre_2', 45)->nullable()->default(null);
            $table->string('apellido_1', 45);
            $table->string('apellido_2', 45)->nullable()->default(null);
            $table->string('identidad', 20);
            $table->string('email', 90);
            $table->string('telefono_1', 20)->nullable()->default(null);
            $table->string('telefono_2', 20)->nullable()->default(null);
            $table->string('fotografia', 200)->default('/libs/fotografias/usuario.jpg');
            $table->string('direccion', 200)->nullable()->default(null);
            $table->integer('es_veterano')->default('0');
            $table->tinyInteger('disponible')->default('1');
            $table->integer('pin')->nullable()->default(null);
            $table->integer('qcpin')->nullable()->default('1');
            $table->integer('cod_estado')->nullable()->default('1');
            $table->integer('cod_gerencia')->nullable()->default(null);
            $table->integer('cod_cargo')->nullable()->default(null);
            $table->integer('cod_perfil')->nullable()->default(null);
            $table->integer('cod_jefe_inmediato')->nullable()->default(null);
            $table->integer('cod_pais')->nullable()->default(null);
            $table->integer('cod_departamento')->nullable()->default(null);
            $table->integer('cod_municipio')->nullable()->default(null);
            $table->tinyInteger('pass_pending')->nullable()->default('1');
            $table->tinyInteger('flag_traducir')->default('0');
            $table->text('cod_info_empresa')->nullable();
            $table->tinyInteger('activo')->default('1');
            $table->integer('cod_tipo_usuario')->nullable()->default('1');
            $table->decimal('pay_rate', 11, 2)->nullable()->default('0.00');
            $table->integer('user_insert')->nullable()->default(null);
            $table->timestamp('updated_at')->nullable()->useCurrent();
            $table->timestamp('date_insert')->useCurrent();

            $table->unique('email', 'email_UNIQUE');

            $table->index(['cod_gerencia', 'cod_cargo'], 'fk_usu_usuarios_usu_cargos1_idx');
            $table->index('user_insert', 'fk_usu_usuarios_usu_usuarios1_idx');
            $table->index('cod_perfil', 'fk_usu_usuarios_usu_perfiles2_idx');
            $table->index('cod_jefe_inmediato', 'fk_usu_usuarios_jefe_inmediato_idx');

            $table->foreign('cod_jefe_inmediato', 'fk_usu_usuarios_jefe_inmediato')->references('cod_usuario')->on('usu_usuarios');
            $table->foreign(['cod_gerencia', 'cod_cargo'], 'fk_usu_usuarios_usu_cargos1')->references(['cod_gerencia', 'cod_cargo'])->on('usu_cargos');
            $table->foreign('cod_perfil', 'fk_usu_usuarios_usu_perfiles2')->references('cod_perfil')->on('usu_perfiles');
            $table->foreign('user_insert', 'fk_usu_usuarios_usu_usuarios1')->references('cod_usuario')->on('usu_usuarios');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('usu_usuarios');
        Schema::enableForeignKeyConstraints();
    }
};
