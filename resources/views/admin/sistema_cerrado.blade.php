<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Cerrado</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="icon" href="https://lmfdata.com/libs/imgs/icons/favicon.ico" type="image/x-icon">
    @if(app()->environment('production'))
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment-with-locales.min.js"
        integrity="sha512-4F1cxYdMiAW98oomSLaygEwmCnIP38pb4Kx70yQYqRwLVCs3DbRumfBq82T08g/4LJ/smbFGFpmeFlQgoDccgg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- Sweet alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

    <!-- Bootstrap Select Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

    <!-- Bootstrap Select Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .mensaje-cerrado {
            text-align: center;
        }

        #contenedor_principal {
            max-height: 700px;
        }

        #cabecera {
            max-height: 100px;
        }

        .color_principal {
            background-color: #280058;
            color: white;
        }

        #titulo_seccion {
            font-size: 1.5em;
            font-weight: bold;
            color: #000;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .input-group-addon {
            padding: 6px 12px;
            font-size: 14px;
            font-weight: normal;
            line-height: 1;
            color: #555;
            text-align: center;
            background-color: #eee;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        /* Estilo para el form-select */
        .form-select {
            background-color: #280058;
            /* Fondo azul para el select */
            color: white;
            /* Texto blanco */
            border: none;
            /* Sin borde */
            padding: 8px;
            appearance: none;
            /* Elimina el estilo predeterminado */
            -webkit-appearance: none;
            /* Elimina el estilo predeterminado en Safari/Chrome */
            -moz-appearance: none;
            /* Elimina el estilo predeterminado en Firefox */
            position: relative;
            font-size: 16px;
            background-image: url('data:image/svg+xml;base64,PHN2ZyBmaWxsPSIjZmZmZmZmIiB2aWV3Qm94PSIwIDAgMTYgMTYiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNMTQgN2MwIC4zLS4xMy41LS4zNC43bC00IDRjLS4yLjItLjQuMy0uNjYuM2gtLjA4Yy0uMjIgMC0uNC0uMS0uNTktLjJsLTQtNGMtLjIyLS4yLS4zMy0uNC0uMzMtLjcgMC0uNi41LS45MSAxLS41OGwzLjQ5IDMuNDRMMTIuOTMgNi4yMWMuNDMtLjMyIDEtLjAyIDEtLjU4IDAgMCAuMDAxIDAgLjAwMSAwem0wIDAiLz48L3N2Zz4=');
            /* Flecha personalizada en SVG */
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }

        /* Estilo para la lista desplegable */
        .form-select option {
            background-color: white;
            /* Fondo blanco para la lista desplegable */
            color: black;
            /* Texto negro para la lista desplegable */
        }

        .select_enabled {
            color: white;
        }

        .select_disabled {
            color: #808080;
        }

        .selector_empleado {
            width: 100%;
            color: black;
            border-bottom: 1px solid black;
            margin: 0px;
            /* padding: 0px; */
            height: auto;
            padding-bottom: 8px;
            cursor: pointer;
        }

        .elemento_seleccionable {
            cursor: pointer;
        }

        #employee_list {
            font-size: 12px;
        }

        #contendor_lista_empleados {
            overflow: auto;
            height: 50vh;
        }

        @media (min-height: 1200px) {
            #contendor_lista_empleados {
                overflow-y: scroll;
                max-height: 800px;
                min-height: 450px;
            }
        }
    </style>
</head>

<body>
    @vite(['resources/js/common.js'])

    <div class="mensaje-cerrado">
        <h1>System Closed</h1>
        <p>The system is closed.</p>
        <a class="navbar-brand text-white" href="{{ env('PHP_APP_URL') }}/dashboard.php">
            <i class="fa-solid fa-home"></i>
        </a>
    </div>
</body>

</html>
