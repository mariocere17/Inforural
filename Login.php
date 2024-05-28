<?php
session_start();
//BD
$user = 'root';
$pass = '';
$bd = 'tfg';
$conexion = new mysqli('localhost', $user, $pass, $bd);

//Funciones BBDD
function insertUser(){
    global $conexion;
    $user = $_POST["registerUser"];
    $password = $_POST["registerPassword"];

    $sql_insert = $conexion->query("INSERT INTO usuarios (nombre, password, rol) VALUES ('$user','$password', '2')");
    if (!$sql_insert) {
        return false;
    } else {
        return true;
    }
}
function getUsuario($nombre, $password){
    global $conexion;
    $sql_select = "SELECT nombre FROM usuarios WHERE nombre = '$nombre' AND password = '$password'";
    $result = $conexion->query($sql_select);
    if ($result->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

//Redireccionamiento
if (isset($_POST["login"])) {
    $user = $_POST["loginUser"];
    $password = $_POST["loginPassword"];
    if(getUsuario($user,$password)){
        $_SESSION["loginUser"] = $user;
        header("Location: select_casa.php");
    }else{
        header("Location: login.php");
    }
    exit();
} elseif (isset($_POST["register"])) {
    insertUser();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login y Registro</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="login_style.css">
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- Login Form -->
                <div class="card mb-4" id="loginCard">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="form-group">
                                <label for="loginUser">Usuario</label>
                                <input type="text" id="loginUser" name="loginUser" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="loginPassword">Contraseña</label>
                                <input type="password" id="loginPassword" name="loginPassword" class="form-control" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-dark btn-block">Login</button>
                            <div class="text-center mt-3">
                                <a href="#" id="showRegisterForm">¿Aún no tienes cuenta? ¡Regístrate aquí!</a>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Register Form -->
                <div class="card hidden" id="registerCard">
                    <div class="card-header">Registro</div>
                    <div class="card-body">
                        <form method="post" action="Login.php">
                            <div class="form-group">
                                <label for="registerUser">Usuario</label>
                                <input type="text" id="registerUser" name="registerUser" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="registerEmail">Email</label>
                                <input type="email" id="registerEmail" name="registerEmail" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="registerPassword">Contraseña</label>
                                <input type="password" id="registerPassword" name="registerPassword" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="registerConfirmPassword">Confirmar Contraseña</label>
                                <input type="password" id="registerConfirmPassword" name="registerConfirmPassword" class="form-control" required>
                            </div>
                            <button type="submit" name="register" class="btn btn-dark btn-block">Registrarse</button>
                            <div class="text-center mt-3">
                                <a href="#" id="showLoginForm">¿Ya tienes cuenta? Inicia sesión</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('showRegisterForm').addEventListener('click', function() {
            document.getElementById('registerCard').classList.remove('hidden');
            document.getElementById('loginCard').classList.add('hidden');
        });
        document.getElementById('showLoginForm').addEventListener('click', function() {
            document.getElementById('registerCard').classList.add('hidden');
            document.getElementById('loginCard').classList.remove('hidden');
        });
    </script>
</body>