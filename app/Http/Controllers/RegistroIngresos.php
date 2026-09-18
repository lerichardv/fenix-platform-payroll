<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelpController;
use App\Models\RegistroEntradaYSalida;
use App\Models\RegistroIngreso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroIngresos extends Controller
{
    /** @var  userRepository */
    private $userRepository;
    /** @var  registroIngresoRepository */
    private $registroIngresoRepository;

    /** @var  registroIngresoRepository */
    private $registroEntradaYSalidaRepository;

    public function __construct(User $userRepo, RegistroIngreso $registroIngresoRepo, RegistroEntradaYSalida $registroEntradaYSalidaRepo)
    {
        $this->userRepository = $userRepo;
        $this->registroIngresoRepository = $registroIngresoRepo;
        $this->registroEntradaYSalidaRepository = $registroEntradaYSalidaRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return "Prueba de conexión con RegistroIngresos";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return "RegistroIngresos@create";
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $body = $request->all();
        $horaDelServidor = date('Y-m-d H:i:s');

        if (!isset($body['pin'])) {
            return response()->json(['message' => 'Falta propiedad PIN', 'data' => $request->all()], 404);
        }
        // return $request;
        // die();
        $currentDate = date('Y-m-d');
        $pin =  $body['pin'];
        $TipoUsuario =  $body['tipo_usuario'];
        $latitud =  $body['latitud'];
        $longitud =  $body['longitud'];

        $datosUsuarios = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'pin', 'qcpin', 'cod_tipo_usuario')
            ->where('pin', $pin)->first();
        if ($datosUsuarios == null) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'No employee associated with this PIN', 'data' => $datosUsuarios], 404);
        }

        $datosUsuarios->horaRegistro = $currentDate;
        $datosUsuarios->metodo =  $body['metodo_ingreso'] ?? "escaneo";
        $registroIngreso = new RegistroIngreso();

        $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
            ->where('cod_usuario', $datosUsuarios->cod_usuario)
            ->orderBy('date_insert', 'desc')->first();

        if ($TipoUsuario == 'admin') {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador != "admin_general" && $resultDatosTipoUsuario->identificador != "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'The user type is not an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        } else {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador == "admin_general" || $resultDatosTipoUsuario->identificador == "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You must log in as an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        }

        if ($resultRegistroIngreso == null) {
            $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
            $registroIngreso->latitud = $latitud;
            $registroIngreso->longitud = $longitud;
            $registroIngreso->save();
            $registroIngreso->tipo_usuario = $TipoUsuario;

            $lastInsertedId = $registroIngreso->cod_inicio_sesion;
            $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
            $registroIngreso->id = $lastInsertedId;
            $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $registroIngreso->pin = $pin;
            $registroIngreso->fecha_ingreso = $horaDelServidor;
            $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
        } else {

            if ($TipoUsuario == 'admin') {
                $resultRegistroIngreso->tipo_usuario = "admin";
            }
            $resultRegistroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            $fechaIngreso = $resultRegistroIngreso->fecha_ingreso;
            $resultRegistroIngreso->id = $resultRegistroIngreso->cod_inicio_sesion;
            $resultRegistroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $resultRegistroIngreso->pin = $pin;
            $diaDiferente =  $this->comprobarQueHayUnaDiaDeDiferencia($horaDelServidor, $fechaIngreso);

            if ($diaDiferente) {
                $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
                $registroIngreso->latitud = $latitud;
                $registroIngreso->longitud = $longitud;
                $registroIngreso->save();
                $registroIngreso->tipo_usuario = $TipoUsuario;
                $lastInsertedId = $registroIngreso->cod_inicio_sesion;
                $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
                $registroIngreso->id = $lastInsertedId;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
            }

            $minutos =  $this->calcularMinutosDiferencias($horaDelServidor, $fechaIngreso);
            $resultRegistroIngreso->minutosDiferenciaRegistro = $minutos;
            $resultRegistroIngreso->horaDelServidor = $horaDelServidor;

            if ($minutos < 30) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You already had the entry marked', 'data' => $resultRegistroIngreso], 200);
            } else if ($minutos > 30 && $TipoUsuario != 'admin') {
                $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion)->update(['fecha_egreso' => $horaDelServidor]);
                $resultado =  $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            } else if ($TipoUsuario == 'admin') {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            }

            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Operation undetermined', 'data' => $resultRegistroIngreso], 200);
        }
        HelpController::desconectarBaseDatos();
    }
    public function IniciarSesion(Request $request)
    {
        // Inicio de sesión de empleado normal

        $body = $request->all();

        $hora_dispositivo_movil = $body['hora_dispositivo_movil'] ?? date('Y-m-d H:i:s');

        //$horaActual = date('H:i:s');
        $horaActual = date('H:i:s', strtotime($hora_dispositivo_movil));
        $horaInicio = '04:00:00';
        $horaFin = '23:00:00';
        if ($horaActual <= $horaInicio || $horaActual >= $horaFin) {
            return response()->json(['message' => 'Clock in or clock out can only be done between 4 am and 11 pm '], 404);
        }
        $body = $request->all();
        $horaDelServidor = date('Y-m-d H:i:s');

        if (!isset($body['pin'])) {
            return response()->json(['message' => 'Falta propiedad PIN', 'data' => $request->all()], 404);
        }

        $currentDate = date('Y-m-d');
        $pin =  $body['pin'];
        $TipoUsuario =  $body['tipo_usuario'];
        $latitud =  $body['latitud'];
        $longitud =  $body['longitud'];
        $cod_farm =  $body['cod_farm'];
        $cod_location =  $body['cod_location'];
        // $hora_dispositivo_movil =  $body['hora_dispositivo_movil'] ?? date('Y-m-d H:i:s');

        $horaDelServidor = $hora_dispositivo_movil;

        $datosUsuarios = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'pin', 'qcpin', 'cod_tipo_usuario')
            ->where('pin', $pin)->first();
        if ($datosUsuarios == null) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'No employee associated with this PIN', 'data' => $datosUsuarios], 404);
        }

        $datosUsuarios->horaRegistro = $currentDate;
        $datosUsuarios->metodo =  $body['metodo_ingreso'] ?? "escaneo";
        $registroIngreso = new RegistroIngreso();

        $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
            ->where('cod_usuario', $datosUsuarios->cod_usuario)
            ->whereNull('fecha_egreso')
            ->whereDate('fecha_ingreso', '=', date('Y-m-d'))
            ->orderBy('fecha_ingreso', 'desc')->first();

        if ($TipoUsuario == 'admin') {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador != "admin_general" && $resultDatosTipoUsuario->identificador != "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'The user type is not an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        } else {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador == "admin_general" || $resultDatosTipoUsuario->identificador == "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You must log in as an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        }

        if ($resultRegistroIngreso == null) {
            $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
                ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
                ->where('cod_usuario', $datosUsuarios->cod_usuario)
                ->whereDate('fecha_egreso', '=', date('Y-m-d'))
                ->orderBy('fecha_ingreso', 'desc')->first();
            if ($resultRegistroIngreso == null) {
                $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
                $registroIngreso->latitud = $latitud;
                $registroIngreso->longitud = $longitud;
                $registroIngreso->cod_farm_ci = $cod_farm;
                $registroIngreso->cod_location_ci = $cod_location;
                $registroIngreso->fecha_ingreso = $horaDelServidor;

                $registroIngreso->save();
                $registroIngreso->tipo_usuario = $TipoUsuario;

                $lastInsertedId = $registroIngreso->cod_inicio_sesion;
                $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
                $registroIngreso->id = $lastInsertedId;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '1']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
            } else {
                $registroIngreso->id = "0";
                $registroIngreso->tipo_usuario = $TipoUsuario;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You have already marked Clock Out for today.', 'data' => $registroIngreso], 200);
            }
        } else {

            if ($TipoUsuario == 'admin') {
                $resultRegistroIngreso->tipo_usuario = "admin";
            }
            $resultRegistroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            $fechaIngreso = $resultRegistroIngreso->fecha_ingreso;
            $resultRegistroIngreso->id = $resultRegistroIngreso->cod_inicio_sesion;
            $resultRegistroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $resultRegistroIngreso->pin = $pin;
            $diaDiferente =  $this->comprobarQueHayUnaDiaDeDiferencia($horaDelServidor, $fechaIngreso);

            if ($diaDiferente) {
                $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
                $registroIngreso->latitud = $latitud;
                $registroIngreso->longitud = $longitud;
                $registroIngreso->cod_farm_ci = $cod_farm;
                $registroIngreso->cod_location_ci = $cod_location;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->save();
                $registroIngreso->tipo_usuario = $TipoUsuario;
                $lastInsertedId = $registroIngreso->cod_inicio_sesion;
                $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
                $registroIngreso->id = $lastInsertedId;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '1']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock In checkedaaaaa', 'data' => $registroIngreso], 200);
            }

            $minutos =  $this->calcularMinutosDiferencias($horaDelServidor, $fechaIngreso);
            $resultRegistroIngreso->minutosDiferenciaRegistro = $minutos;
            $resultRegistroIngreso->horaDelServidor = $horaDelServidor;

            if ($minutos < 30) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You already had the entry marked', 'data' => $resultRegistroIngreso], 200);
            } else if ($minutos > 30 && $TipoUsuario != 'admin') {
                $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion)->update([
                    'fecha_egreso' => $horaDelServidor,
                    'cod_farm_co' => $cod_farm,
                    'cod_location_co' => $cod_location
                ]);
                $resultado =  $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion);
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '0']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            } else if ($TipoUsuario == 'admin') {

                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            }

            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Operation undetermined', 'data' => $resultRegistroIngreso], 200);
        }
        HelpController::desconectarBaseDatos();
    }

    public function registrarIngresoMasivo(Request $request)
    {

        $todosLosUsuariosDisponibles = DB::table('usu_usuarios')
            ->select('cod_usuario', 'pin', 'qcpin')
            ->whereNotIn('cod_tipo_usuario', [1, 2, 3])
            ->get();

        $contadorRegistrosExitosos = 0;

        foreach ($todosLosUsuariosDisponibles as $usuario) {
            $horaDelServidor = date('Y-m-d H:i:s');
            $currentDate = date('Y-m-d');
            $pin =  $usuario->pin;
            $TipoUsuario =  "empleado";
            $latitud =  0000;
            $longitud =  11111;
            $cod_farm =  1;
            $cod_location =  3;

            $datosUsuarios = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'pin', 'qcpin', 'cod_tipo_usuario')
            ->where('pin', $pin)->first();

            if (!$datosUsuarios) {
            continue;
            }

            $datosUsuarios->horaRegistro = $currentDate;
            $datosUsuarios->metodo =  "escaneo";
            $registroIngreso = new RegistroIngreso();

            $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
            ->where('cod_usuario', $datosUsuarios->cod_usuario)
            ->whereNull('fecha_egreso')
            ->whereDate('fecha_ingreso', '=', date('Y-m-d'))
            ->orderBy('fecha_ingreso', 'desc')->first();

            if ($resultRegistroIngreso == null) {
            $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
            $registroIngreso->latitud = $latitud;
            $registroIngreso->longitud = $longitud;
            $registroIngreso->cod_farm_ci = $cod_farm;
            $registroIngreso->cod_location_ci = $cod_location;
            $registroIngreso->fecha_ingreso = $horaDelServidor;
            $registroIngreso->save();
            $registroIngreso->tipo_usuario = $TipoUsuario;

            DB::table('usu_usuarios')
                ->where('cod_usuario', $datosUsuarios->cod_usuario)
                ->update(['disponible' => '1']);

            $contadorRegistrosExitosos++;
            } else {
            $minutos = $this->calcularMinutosDiferencias($horaDelServidor, $resultRegistroIngreso->fecha_ingreso);

            if ($minutos > 30) {
                $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion)->update([
                'fecha_egreso' => $horaDelServidor,
                'cod_farm_co' => $cod_farm,
                'cod_location_co' => $cod_location
                ]);

                DB::table('usu_usuarios')
                ->where('cod_usuario', $datosUsuarios->cod_usuario)
                ->update(['disponible' => '0']);

                // $contadorRegistrosExitosos++;
            }
            }
        }

        HelpController::desconectarBaseDatos();
        return response()->json(['message' => 'Mass registration completed', 'successful_records' => $contadorRegistrosExitosos], 200);
    }

    public function iniciarSesionEmpleado(Request $request)
    {

        $body = $request->all();

        $hora_dispositivo_movil = $body['hora_dispositivo_movil'] ?? date('Y-m-d H:i:s');

        //$horaActual = date('H:i:s');
        $horaActual = date('H:i:s', strtotime($hora_dispositivo_movil));
        $horaInicio = '04:00:00';
        $horaFin = '23:00:00';
        if ($horaActual <= $horaInicio || $horaActual >= $horaFin) {
            return response()->json(['message' => 'Clock in or clock out can only be done between 4 am and 11 pm '], 404);
        }

        $body = $request->all();
        $horaDelServidor = date('Y-m-d H:i:s');

        if (!isset($body['pin'])) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Falta propiedad PIN', 'data' => $request->all()], 404);
        }

        $currentDate = date('Y-m-d');
        $pin =  $body['pin'];
        $TipoUsuario =  $body['tipo_usuario'];
        $latitud =  $body['latitud'];
        $longitud =  $body['longitud'];

        $datosUsuarios = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'pin', 'qcpin', 'cod_tipo_usuario')
            ->where('pin', $pin)->first();
        if ($datosUsuarios == null) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'No employee associated with this PIN', 'data' => $datosUsuarios], 404);
        }



        $datosUsuarios->horaRegistro = $currentDate;
        $datosUsuarios->metodo =  $body['metodo_ingreso'] ?? "escaneo";
        $registroIngreso = new RegistroIngreso();

        $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
            ->where('cod_usuario', $datosUsuarios->cod_usuario)
            ->orderBy('date_insert', 'desc')->first();

        if ($TipoUsuario == 'admin') {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador != "admin_general" && $resultDatosTipoUsuario->identificador != "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'The user type is not an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        } else {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador == "admin_general" || $resultDatosTipoUsuario->identificador == "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You must log in as an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        }

        if ($resultRegistroIngreso == null) {
            $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
            $registroIngreso->latitud = $latitud;
            $registroIngreso->longitud = $longitud;
            $registroIngreso->save();
            $registroIngreso->tipo_usuario = $TipoUsuario;

            $lastInsertedId = $registroIngreso->cod_inicio_sesion;
            $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
            $registroIngreso->id = $lastInsertedId;
            $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $registroIngreso->pin = $pin;
            $registroIngreso->fecha_ingreso = $horaDelServidor;
            $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            DB::table('usu_usuarios')
                ->where('cod_usuario', $datosUsuarios->cod_usuario)
                ->update(['disponible' => '1']);
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
        } else {

            if ($TipoUsuario == 'admin') {
                $resultRegistroIngreso->tipo_usuario = "admin";
            }
            $resultRegistroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            $fechaIngreso = $resultRegistroIngreso->fecha_ingreso;
            $resultRegistroIngreso->id = $resultRegistroIngreso->cod_inicio_sesion;
            $resultRegistroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $resultRegistroIngreso->pin = $pin;
            $diaDiferente =  $this->comprobarQueHayUnaDiaDeDiferencia($horaDelServidor, $fechaIngreso);

            if ($diaDiferente) {
                $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
                $registroIngreso->latitud = $latitud;
                $registroIngreso->longitud = $longitud;
                $registroIngreso->save();
                $registroIngreso->tipo_usuario = $TipoUsuario;
                $lastInsertedId = $registroIngreso->cod_inicio_sesion;
                $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
                $registroIngreso->id = $lastInsertedId;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '1']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
            }

            $minutos =  $this->calcularMinutosDiferencias($horaDelServidor, $fechaIngreso);
            $resultRegistroIngreso->minutosDiferenciaRegistro = $minutos;
            $resultRegistroIngreso->horaDelServidor = $horaDelServidor;

            if ($minutos < 30) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You already had the entry marked', 'data' => $resultRegistroIngreso], 200);
            } else if ($minutos > 30 && $TipoUsuario != 'admin') {
                $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion)->update(['fecha_egreso' => $horaDelServidor]);
                $resultado =  $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion);
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '0']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            } else if ($TipoUsuario == 'admin') {
                DB::table('usu_usuarios')
                    ->where('cod_usuario', $datosUsuarios->cod_usuario)
                    ->update(['disponible' => '0']);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            }

            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Operation undetermined', 'data' => $resultRegistroIngreso], 200);
        }
    }

    public function inicioSesionEmpleadoSimulado()
    {


        try {
            $insertData = [
                'cod_farms' => 1,
                'cod_empleado' => 1,
                'clock_in' => 1,
                'clock_out' => 0,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
                'GPS' => '154654,54154'
            ];
            $pay_clocks_creado = $this->registroEntradaYSalidaRepository->create($insertData);
            HelpController::desconectarBaseDatos();
            return  response()->json(['message' => 'Clock In checked', 'data' => $pay_clocks_creado], 200);
        } catch (\Throwable $th) {

            throw $th;
        }
    }

    private function verificarClockInPrevio(array $datosRegistro)
    {
        $result = $this->registroEntradaYSalidaRepository->select('cod_clock', 'cod_farms', 'cod_empleado', 'fecha', 'hora', 'gps')
            ->where('cod_empleado', 1)
            ->where('clock_in', 1)
            ->where('clock_out', 0)
            ->orderBy('date_insert', 'desc')
            ->limit(1)
            ->get();
        $result = DB::table('pay_clocks')
            ->select('cod_clock', 'cod_farms', 'cod_empleado', 'fecha', 'hora', 'gps')
            ->where('cod_empleado', 1)
            ->where('clock_in', 1)
            ->where('clock_out', 0)
            ->orderBy('date_insert', 'desc')
            ->limit(1)
            ->get();
    }
    private function registrarClockIn(array $datosRegistro)
    {
        try {
            $insertData = [
                'cod_farms' => 1,
                'cod_empleado' => 1,
                'clock_in' => 1,
                'clock_out' => 0,
                'fecha' => date('Y-m-d'),
                'hora' => date('H:i:s'),
                'GPS' => '154654,54154'
            ];
            $pay_clocks_creado = $this->registroEntradaYSalidaRepository->create($datosRegistro);
        } catch (\Throwable $th) {

            throw $th;
        }
    }

    private function registrarClockOut(array $datosRegistro) {}

    public function iniciarSesionAdmin(Request $request)
    {
        // Inicio de sesión de administrador
        $body = $request->all();

        $hora_dispositivo_movil = $body['hora_dispositivo_movil'] ?? date('Y-m-d H:i:s');

        //$horaActual = date('H:i:s');
        $horaActual = date('H:i:s', strtotime($hora_dispositivo_movil));
        $horaInicio = '04:00:00';
        $horaFin = '23:00:00';
        if ($horaActual <= $horaInicio || $horaActual >= $horaFin) {
            return response()->json(['message' => 'Clock in or clock out can only be done between 4 am and 11 pm '], 404);
        }


        $body = $request->all();
        $horaDelServidor = date('Y-m-d H:i:s');

        if (!isset($body['pin'])) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Falta propiedad PIN', 'data' => $request->all()], 404);
        }

        $currentDate = date('Y-m-d');
        $pin =  $body['pin'];
        $TipoUsuario =  $body['tipo_usuario'];
        $latitud =  $body['latitud'];
        $longitud =  $body['longitud'];

        $datosUsuarios = DB::table('usu_usuarios')
            ->select('cod_usuario', 'usuario', 'pin', 'qcpin', 'cod_tipo_usuario')
            ->where('pin', $pin)->first();
        if ($datosUsuarios == null) {
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'No employee associated with this PIN', 'data' => $datosUsuarios], 404);
        }

        $datosUsuarios->horaRegistro = $currentDate;
        $datosUsuarios->metodo =  $body['metodo_ingreso'] ?? "escaneo";
        $registroIngreso = new RegistroIngreso();

        $resultRegistroIngreso = DB::table('pay_bitacora_inicio_sesion')
            ->select('cod_inicio_sesion', 'cod_usuario', 'fecha_ingreso', 'fecha_egreso', 'registro_modificado')
            ->where('cod_usuario', $datosUsuarios->cod_usuario)
            ->whereNull('fecha_egreso')
            ->orderBy('date_insert', 'desc')->first();

        if ($TipoUsuario == 'admin') {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador != "admin_general" && $resultDatosTipoUsuario->identificador != "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'The user type is not an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        } else {
            $resultDatosTipoUsuario = DB::table('usu_tipo_usuario')
                ->select('cod_tipo_usuario', 'descripcion', 'etiqueta', 'identificador')
                ->where('cod_tipo_usuario', $datosUsuarios->cod_tipo_usuario)
                ->orderBy('date_insert', 'desc')->first();
            if ($resultDatosTipoUsuario == null) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'User type not found. Unable to determine the type', 'data' => $resultRegistroIngreso], 404);
            }
            if ($resultDatosTipoUsuario->identificador == "admin_general" || $resultDatosTipoUsuario->identificador == "admin_app") {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You must log in as an administrator', 'data' => $resultRegistroIngreso], 404);
            }
        }

        if ($resultRegistroIngreso == null) {
            $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
            $registroIngreso->latitud = $latitud;
            $registroIngreso->longitud = $longitud;
            $registroIngreso->save();
            $registroIngreso->tipo_usuario = $TipoUsuario;

            $lastInsertedId = $registroIngreso->cod_inicio_sesion;
            $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
            $registroIngreso->id = $lastInsertedId;
            $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $registroIngreso->pin = $pin;
            $registroIngreso->fecha_ingreso = $horaDelServidor;
            $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
        } else {

            if ($TipoUsuario == 'admin') {
                $resultRegistroIngreso->tipo_usuario = "admin";
            }
            $resultRegistroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
            $fechaIngreso = $resultRegistroIngreso->fecha_ingreso;
            $resultRegistroIngreso->id = $resultRegistroIngreso->cod_inicio_sesion;
            $resultRegistroIngreso->id_empleado = $datosUsuarios->cod_usuario;
            $resultRegistroIngreso->pin = $pin;
            $diaDiferente =  $this->comprobarQueHayUnaDiaDeDiferencia($horaDelServidor, $fechaIngreso);

            if ($diaDiferente) {
                $registroIngreso->cod_usuario = $datosUsuarios->cod_usuario;
                $registroIngreso->latitud = $latitud;
                $registroIngreso->longitud = $longitud;
                $registroIngreso->save();
                $registroIngreso->tipo_usuario = $TipoUsuario;
                $lastInsertedId = $registroIngreso->cod_inicio_sesion;
                $lastInsertedRecord = $this->registroIngresoRepository->find($lastInsertedId);
                $registroIngreso->id = $lastInsertedId;
                $registroIngreso->id_empleado = $datosUsuarios->cod_usuario;
                $registroIngreso->pin = $pin;
                $registroIngreso->fecha_ingreso = $horaDelServidor;
                $registroIngreso->metodo_ingreso = $body['metodo_ingreso'] ?? "escaneo";
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock In checked', 'data' => $registroIngreso], 200);
            }

            $minutos =  $this->calcularMinutosDiferencias($horaDelServidor, $fechaIngreso);
            $resultRegistroIngreso->minutosDiferenciaRegistro = $minutos;
            $resultRegistroIngreso->horaDelServidor = $horaDelServidor;

            if ($minutos < 30) {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'You already had the entry marked', 'data' => $resultRegistroIngreso], 200);
            } else if ($minutos > 30 && $TipoUsuario != 'admin') {
                $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion)->update(['fecha_egreso' => $horaDelServidor]);
                $resultado =  $this->registroIngresoRepository->find($resultRegistroIngreso->cod_inicio_sesion);
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            } else if ($TipoUsuario == 'admin') {
                HelpController::desconectarBaseDatos();
                return response()->json(['message' => 'Clock Out checked', 'data' => $resultRegistroIngreso], 200);
            }

            HelpController::desconectarBaseDatos();
            return response()->json(['message' => 'Operation undetermined', 'data' => $resultRegistroIngreso], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        return "RegistroIngresos@show";
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {

        //
        // return "RegistroIngresos@edit: $id";
        return response()->json(['message' => 'RegistroIngresos@edit', 'data' => $request], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function calcularMinutosDiferencias($horaActual, $horaSecundaria)
    {

        $diferencia = abs(strtotime($horaActual) - strtotime($horaSecundaria));
        $minutos = floor($diferencia / 60);
        return $minutos;
    }

    private function calcularCambioDeHora($horaActual, $horaSecundaria)
    {
        $horaInicio = strtotime('04:00:00');
        $horaFin = strtotime('23:00:00');
        $horaSecundaria = strtotime($horaSecundaria);

        if ($horaSecundaria >= $horaInicio && $horaSecundaria <= $horaFin) {
            $diferencia = abs(strtotime($horaActual) - $horaSecundaria);
            $minutos = floor($diferencia / 60);
            return $minutos;
        } else {
            return 0;
        }
    }
    private function comprobarQueHayUnaDiaDeDiferencia($horaActual, $fechaIngreso)
    {
        $horaInicio = strtotime('04:00:00');
        $horaFin = strtotime('23:00:00');
        $fechaIngreso = strtotime($fechaIngreso);

        if ($fechaIngreso >= $horaInicio && $fechaIngreso <= $horaFin) {
            $diferencia = abs(strtotime($horaActual) - $fechaIngreso);
            $dias = floor($diferencia / (60 * 60 * 24)); // Calculate the difference in days
            return $dias >= 1; // Check if the difference is at least 1 day
        } else {
            return false;
        }
    }
}
