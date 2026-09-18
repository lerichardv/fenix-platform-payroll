<?php

namespace App\Http\Controllers\Helpers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HelpController extends Controller
{
    //
    static function getRoutes($controller)
    {
        $routes = [];
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        $controller = new \ReflectionClass($controller);
    }
    static function getmessage($controller)
    {
        $controller = new \ReflectionClass($controller);
        $methods = $controller->getMethods();
        $message = '';
        foreach ($methods as $method) {
            $message .= $method->name . "\n";
        }
        return $message;
    }
    static function successResponse(int $code, string $message, $extra = null, $status = 200)
    {
        return response()->json(['code' => $code, 'message' => $message, 'extra' => $extra], $status);
    }

    static function failureResponse(int $code, string $message, $extra = null, $status = 400)
    {
        return response()->json(['code' => $code, 'message' => $message, 'extra' => $extra], $status);
    }

    //Funciones para la base de datos
    static function desconectarBaseDatos()
    {
        self::killAllProcesses();
        DB::disconnect('mysql');
    }

    static function killAllProcesses()
    {
        $processes = DB::select("SHOW PROCESSLIST");
        foreach ($processes as $process) {
            try {
                if ($process->Id != DB::connection()->getPdo()->query('SELECT CONNECTION_ID()')->fetchColumn()) {
                    DB::statement("KILL {$process->Id}");
                }
            } catch (\Exception $e) {
                // Handle the exception or log it
                error_log($e->getMessage());
            }
            // if ($process->Id != DB::connection()->getPdo()->query('SELECT CONNECTION_ID()')->fetchColumn()) {
            //     DB::statement("KILL {$process->Id}");
            // }
        }
    }

    static function setDatabaseModeParaAgrupacionesGrandes()
    {
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';");
    }

    static function setDatabaseModeParaGrandesQuerys()
    {
        DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';");
        DB::statement("SET SQL_BIG_SELECTS=1;");
    }

    static function setDatabaseModeALaNormalidad()
    {
        self::setDatabaseModeOnlyFullGroupBy();
        self::unsetDatabaseModeParaGrandesQuerys();
    }

    static function setDatabaseModeOnlyFullGroupBy()
    {
        DB::statement("SET sql_mode = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';");
    }

    static function unsetDatabaseModeParaGrandesQuerys()
    {
        DB::statement("SET SQL_BIG_SELECTS=0;");
    }
    //-------------------------------------------------------------------------------->
}
