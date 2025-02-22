<?php
chdir("../../");
require_once "clases/Cliente.php";
require_once "config/config.php";
require_once "clases/DB.php";

class ClienteService {
    private $db;

    public function __construct() {
        $base = new Base();
        $this->db = $base->__get("link");
    }

    public function registrar($datos) {
        $cliente = new Cliente($datos["dniCliente"],$datos["nombre"],$datos["direccion"],$datos["email"],password_hash($datos["password"], PASSWORD_BCRYPT));

        if ($cliente->registrar($this->db)) {
            return json_encode(["status" => "success", "message" => "Cliente registrado correctamente"]);
        } else {
            return json_encode(["status" => "error", "message" => "Error al registrar cliente"]);
        }
    }

    public function validar($datos) {
        $cliente = Cliente::validar($this->db, $datos["email"], $datos["password"]);
        if ($cliente) {
            session_start();
            $_SESSION["nombre"] = $cliente->__get("nombre");
            $_SESSION["dniCliente"] = $cliente->__get("dniCliente");
            return json_encode(["status" => "success", "message" => "Validación correcta"]);
        } else {
            return json_encode(["status" => "error", "message" => "Credenciales incorrectas"]);
        }
    }

    public function obtenerPorDNI($dniCliente) {
        $cliente = Cliente::obtenerPorDNI($this->db, $dniCliente);
        if ($cliente) {
            return json_encode(["status" => "success", "cliente" => $cliente]);
        } else {
            return json_encode(["status" => "error", "message" => "Cliente no encontrado"]);
        }
    }

    public function obtenerSesion(){
        session_start();
        if (isset($_SESSION["nombre"])) {
            return json_encode(["status" => "success", "usuario" => ["nombre" => $_SESSION["nombre"]] ]);
        } else{
            return json_encode(["status" => "error", "message" => "Sesion no iniciada"]);
        }
    }

    public function obtenerTodosLosDatosDeSesion(){
        session_start();
        if(isset($_SESSION["dniCliente"])) {
            $cliente = Cliente::obtenerPorDNI($this->db, $_SESSION["dniCliente"]);
            return json_encode(["status" => "success", "usuario" => ["dnicliente" => $cliente->__get('dniCliente'),"nombre" => $cliente->__get('nombre'), "direccion" => $cliente->__get('direccion'), "email" => $cliente->__get('email')]]);
        }
    }


    public function cerrarSesion(){
        session_start();
        session_unset();
        session_destroy();
        return json_encode(["status" => "success", "message" => "Sesión cerrada correctamente"]);
    }
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, application/json");
header("Content-Type: application/json");

$service = new ClienteService();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    $action = $input["action"] ?? null; 

    switch ($action) {
        case "registrar":
            echo $service->registrar($input);
            break;
        case "validar":
            echo $service->validar($input);
            break;
        case "obtenerSesion":
            echo $service->obtenerSesion();
            break;
        case "cerrarSesion":
            echo $service->cerrarSesion();
            break;
        case "obtenerTodosLosDatosDeSesion":
            echo $service->obtenerTodosLosDatosDeSesion();
            break;
        default:
            echo json_encode(["status" => "error", "message" => "Acción no válida"]);
    }
} elseif ($_SERVER["REQUEST_METHOD"] === "GET") {
    $dni = $_GET["dniCliente"] ?? null;
    echo $service->obtenerPorDNI($dni);
} else {
    echo json_encode(["status" => "error", "message" => "Método no soportado"]);
}




?>      