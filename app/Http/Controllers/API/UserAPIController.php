<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAPIController extends Controller
{

    /** @var  userRepository */
    private $userRepository;

    public function __construct(User $userRepo)
    {
        $this->userRepository = $userRepo;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->userRepository->find($id);
        $result = DB::table('usu_usuarios')
            ->select('usuario', 'pin', 'qcpin')
            ->where('pin', $id)
            ->first();
        if ($result == null) {
            return response()->json(['error' => 'Not Found', 'pin' => $id], 404);
        } else {

            // You can access the result using the object notation
            $usuario = $result->usuario;
            $pin = $result->pin;
            $qcpin = $result->qcpin;

            // Do something with the result

            // return $result;
            return $this->userRepository->all();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        return $request;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function crearPINTodosUsuarios()
    {
        $users = $this->userRepository->all();
        if ($users->isEmpty()) {
            return response()->json(['error' => 'Not Found', 'message' => 'No hay usuarios en la base de datos'], 404);
        } else {


            try {
                foreach ($users as $user) {
                    if ($user->pin == null || $user->qcpin == null) {
                        $user->pin = rand(1000, 9999);
                        $user->qcpin = rand(1000, 9999);
                        $user->save();
                    }
                }
            } catch (\Throwable $th) {
                // Handle the exception here
                return response()->json(['error' => 'Internal Server Error', 'message' => $th->getMessage()], 500);
            }
        }
        return "PIN creados para los usuarios que hacian falta";
    }
    public function asignarDatosAUsuarios(Request $request)
    {
        $users = new User();

        $users->pin = $request->pin;
        $users->qcpin = $request->qcpin;
        $users->qcpin = $request->qcpin;
        $users->nombre_1 = $request->nombre_1;
        $users->nombre_2 = "";
        $names = explode(" ", $request->nombre_1);
        $namesDos = explode(" ", $request->apellido_2);
        if (count($namesDos) > 0) {

            $usuarioAsignado = $names[0] . $namesDos[0];
        } else {

            $usuarioAsignado = $names[0] . $request->apellido_2;
        }
        $email = $names[0] . '@email.com';
        $users->email = $email;

        // $users->usuario = $request->usuario;
        $users->usuario = $usuarioAsignado;
        $users->pass = $request->pass;
        $users->nombre_1 = $request->nombre_1;
        $users->nombre_2 = $request->nombre_2;
        $users->apellido_1 = $request->apellido_1;
        $users->apellido_2 = $request->apellido_2;
        // $users->identidad = $request->identidad;
        $users->identidad = $usuarioAsignado;
        // $users->email = $request->email;
        $users->telefono_1 = $request->telefono_1;
        $users->telefono_2 = $request->telefono_2;
        $users->fotografia = $request->fotografia;
        $users->direccion = $request->direccion;
        $users->pin = $request->pin;
        $users->qcpin = $request->qcpin;
        $users->cod_gerencia = $request->cod_gerencia;
        $users->cod_cargo = $request->cod_cargo;
        $users->cod_perfil = $request->cod_perfil;
        $users->cod_jefe_inmediato = $request->cod_jefe_inmediato;
        $users->cod_pais = $request->cod_pais;
        $users->cod_departamento = $request->cod_departamento;
        $users->cod_municipio = $request->cod_municipio;
        $users->pass_pending = $request->pass_pending;
        $users->flag_traducir = $request->flag_traducir;
        $users->cod_info_empresa = $request->cod_info_empresa;
        $users->activo = $request->activo;
        $users->user_insert = $request->user_insert;
        $users->updated_at = $request->updated_at;
        $users->date_insert = $request->date_insert;
        $users->save();
        return $users;
    }
    public function buscarEmpleadosAsignados($id_jefe)
    {
        $empleados_asignados_filtrado = [];
        $empleados_asignados = $this->userRepository->where('cod_jefe_inmediato', $id_jefe)
            ->where(function ($query) use ($id_jefe) {
                $query->where('cod_usuario', '!=', $id_jefe)
                    ->whereNotIn('cod_tipo_usuario', [1, 2, 3]);
            })
            ->where('disponible', 1)
            ->orderBy('nombre_1')
            ->get();
        foreach ($empleados_asignados as $empleado) {
            $nombre = $empleado->nombre_1;
            $apellido = $empleado->apellido_1;
            $usuario = $empleado->usuario;
            $registroIngreso = DB::table('pay_bitacora_inicio_sesion')
                ->select('cod_inicio_sesion', 'fecha_egreso')
                ->where('cod_usuario', $empleado->cod_usuario)
                ->whereNull('fecha_egreso')
                ->get();
            $registroIngreso->isNotEmpty() ? $empleados_asignados_filtrado[] = $empleado : null;

            // $empleados_asignados_filtrado[] = $empleado;
        }
        return $empleados_asignados_filtrado;
    }
    public function listadoCrewPrevio($id_jefe)
    {
        $query = DB::table('pay_crews_ultimo_usado')
            ->join('usu_usuarios', 'usu_usuarios.cod_usuario', '=', 'pay_crews_ultimo_usado.cod_empleado')
            ->select('pay_crews_ultimo_usado.cod_crew_ultimo_usado', 'pay_crews_ultimo_usado.cod_harvest', 'pay_crews_ultimo_usado.cod_empleado', 'usu_usuarios.*')
            ->where('pay_crews_ultimo_usado.cod_supervisor', $id_jefe)
            ->get();

        return $query;
    }
    public function eliminarPersonaDelCrewPrevio($cod_crew_ultimo_usado)
    {
        try {
            DB::table('pay_crews_ultimo_usado')
                ->where('cod_crew_ultimo_usado', $cod_crew_ultimo_usado)
                ->delete();

            return response()->json(['message' => 'Eliminación exitosa'], 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Internal Server Error', 'message' => $th->getMessage()], 500);
        }
    }
}
