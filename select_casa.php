<?php
session_start();

if (isset($_SESSION["loginUser"])) {
    $usuario = $_SESSION["loginUser"];
} else {
    echo "No has iniciado sesión.";
}

//BD
$user = 'root';
$pass = '';
$bd = 'tfg';
$conexion = new mysqli('localhost', $user, $pass, $bd);

function getProvincias() {
    global $conexion;
    $provincias = [];
    $sql_select = $conexion->query("SELECT DISTINCT provincia FROM casas_rurales");
    if ($sql_select->num_rows > 0) {
        while ($fila = $sql_select->fetch_assoc()) {
            $p["provincia"] = $fila["provincia"];
            $provincias[] = $p;
        }
        return json_encode($provincias);
    } else {
        return json_encode(["error" => "No data found"]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Casas rurales</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="alert alert-primary" role="alert">
            Bienvenido <?php echo htmlspecialchars($usuario); ?>
        </div>
        <h1 class="text-center mb-4">Casas rurales</h1>
        <form method="post" action="">
            <div class="form-group">
                <label for="provincias">Seleccione una provincia</label>
                <select id="provincias" class="form-control">
                    <option value="" selected disabled>Seleccione una provincia</option>
                    <?php
                    $provincias = json_decode(getProvincias(), true);
                    foreach ($provincias as $p) {
                        echo "<option>" . htmlspecialchars($p['provincia']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="selector">Seleccione una casa rural</label>
                <select id="selector" class="form-control" style="display: none;">
                </select>
            </div>
            <div class="form-group">
                <label for="fecha">Introduzca la fecha prevista para viajar</label>
                <input type="date" id="fecha" name="fecha" class="form-control">
            </div>
            <input type="hidden" id="selectedCasa" name="selectedCasa">
            <button type="submit" id="enviar" name="enviar" class="btn btn-dark btn-block">Enviar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        let btn = document.getElementById("enviar");
        let selector = document.getElementById("selector");
        let selectorProvincias = document.getElementById("provincias");

        selectorProvincias.addEventListener("change", (ev) => {
            let op = ev.target.value;
            let xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("selector").innerHTML = this.responseText;
                    selector.style.display = "block";
                }
            };
            xhttp.open("GET", `Carga_Selectores.php?prov=${op}`, true);
            xhttp.send();
        });

        document.getElementById('selector').addEventListener('change', function() {
            document.getElementById('selectedCasa').value = this.value;
        });

        document.getElementById('selectedCasa').value = document.getElementById('provincias').value;
    </script>
</body>
</html>

<?php
if (isset($_POST["enviar"])) {
    $selectedCasa = $_POST['selectedCasa'];
    $fecha = $_POST['fecha'];
    header("Location: Result.php?casa=" . urlencode($selectedCasa) . "&fecha=" . urlencode($fecha));
}
?>