<?php
//BD
$user = 'root';
$pass = '';
$bd = 'tfg';
$conexion = new mysqli('localhost', $user, $pass, $bd);

function insertCasa($provincia)
{
    global $conexion;
    $csvData = file_get_contents("../ficheros_Casas/castellon.csv");
    $rows = str_getcsv($csvData, "\n");
    array_shift($rows); 

    $success = true;

    foreach ($rows as $row) {
        $values = str_getcsv($row, ";");
        $sql_insert = $conexion->query("INSERT INTO casas_rurales (id, municipio, cod_mun, nombre, telefono, provincia, direccion, cod_postal, email, web, ubicacion) values ('$values[0]','$values[1]','$values[2]','$values[3]','$values[4]','$provincia','$values[6]','$values[7]','$values[8]','$values[9]','$values[10]') ");
        if (!$sql_insert) {
            $success = false;
            echo "Error: " . $conexion->error;
            break;
        }
    }

    return $success;
}

$result = insertCasa("Castellon");
if ($result) {
    echo "Data inserted successfully.";
} else {
    echo "Failed to insert data.";
}