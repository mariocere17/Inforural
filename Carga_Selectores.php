<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
//BD
$user = 'root';
$pass = '';
$bd = 'tfg';
$conexion = new mysqli('localhost', $user, $pass, $bd);

$provAjax = isset($_GET["prov"]) ? $_GET["prov"] : "";

function getCasas($prov){
    global $conexion;
    $casas = [];
    $sql_select = $conexion->query("SELECT nombre FROM casas_rurales where provincia = '$prov'");
    if ($sql_select->num_rows > 0) {
        while ($fila = $sql_select->fetch_assoc()) {
            $casas[] = $fila["nombre"];
        }
        return json_encode($casas);
    } else {
        return "error";
    }
}
?>
<!-- Selector dinamico casas -->
<select id="selector">
<option value="" selected disabled>Seleccione una casa</option>
    <?php
        $casas = json_decode(getCasas($provAjax), true);
        foreach ($casas as $p) {         
            echo "<option>". $p ."</option>";                
        }
    ?>
</select>
