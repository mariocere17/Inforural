<?php
session_start();
$usuario = $_SESSION["loginUser"];

// BD
$user = 'root';
$pass = '';
$bd = 'tfg';
$conexion = new mysqli('localhost', $user, $pass, $bd);

function getUserID($usuario){
    global $conexion;
    $sql_select = $conexion->query("SELECT id_usuario FROM usuarios WHERE nombre = '$usuario'");
    if ($sql_select->num_rows > 0) {
        $row = $sql_select->fetch_assoc();
        return $row['id_usuario'];
    } else {
        return null;
    }
}

function getCoords($casa) {
    global $conexion;
    $sql_select = $conexion->query("SELECT ubicacion FROM casas_rurales WHERE nombre = '$casa'");
    if ($sql_select->num_rows > 0) {
        while ($fila = $sql_select->fetch_assoc()) {
            return $fila['ubicacion'];
        }
    } else {
        return json_encode(["error" => "No data found"]);
    }
}

function getInfo($casa) {
    global $conexion;
    $sql_select = $conexion->query("SELECT web, email, telefono FROM casas_rurales WHERE nombre = '$casa'");
    if ($sql_select->num_rows > 0) {
        $result = "";
        while ($fila = $sql_select->fetch_assoc()) {
            $result .= "<p><i class='fas fa-globe'></i> <strong>Web:</strong> " . $fila['web'] . "</p>";
            $result .= "<p><i class='fas fa-envelope'></i> <strong>Email:</strong> " . $fila['email'] . "</p>";
            $result .= "<p><i class='fas fa-phone'></i> <strong>Teléfono:</strong> " . $fila['telefono'] . "</p>";
        }
        return $result;
    } else {
        return "<p>No data found</p>";
    }
}

function getInfoCasaRural($casa) {
    global $conexion;
    $sql_select = $conexion->query("SELECT municipio, cod_mun, nombre, direccion, cod_postal, telefono FROM casas_rurales WHERE nombre = '$casa'");
    if ($sql_select->num_rows > 0) {
        return $sql_select->fetch_assoc();
    } else {
        return null;
    }
}

function insertMeteorologia($casa){
    global $conexion;
    $coords = getCoords($casa);
    $coordsArray = explode(',', $coords);
    $latitude = trim($coordsArray[0]);
    $longitude = trim($coordsArray[1]);
    $fecha = $_GET['fecha'];
    $apiKey = 'cd34aa2dfa1441e686a123522242305';
    $url = "https://api.weatherapi.com/v1/history.json?key=$apiKey&q=$latitude,$longitude&dt=$fecha";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    $temperatura = $data['forecast']['forecastday'][0]['day']['avgtemp_c'];
    $condicion = $data['forecast']['forecastday'][0]['day']['condition']['text'];

    $sql_insert = $conexion->query("INSERT INTO meteorologia (temperatura, condicion, fecha) VALUES ('$temperatura', '$condicion', '$fecha')");
    if (!$sql_insert) {
        return false;
    } else {
        return $conexion->insert_id;
    }
}

function insertViaje($casa, $usuario, $id_meteorologia) {
    global $conexion;
    $infoCasa = getInfoCasaRural($casa);
    if ($infoCasa) {
        $municipio = $infoCasa['municipio'];
        $cod_mun = $infoCasa['cod_mun'];
        $nombre_casa = $infoCasa['nombre'];
        $direccion = $infoCasa['direccion'];
        $cod_postal = $infoCasa['cod_postal'];
        $telefono = $infoCasa['telefono'];

        $sql_insert = $conexion->query("INSERT INTO viajes (municipio, cod_mun, nombre_casa, direccion, cod_postal, contacto, id_meteorologia, id_usuario) VALUES ('$municipio', '$cod_mun', '$nombre_casa', '$direccion', '$cod_postal', '$telefono', '$id_meteorologia', '$usuario')");
        if (!$sql_insert) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
}

if (isset($_GET['casa'])) {
    $fecha = $_GET['fecha'];
    $casa = $_GET['casa'];
    $coords = getCoords($_GET['casa']);
    $coordsArray = explode(',', $coords);
    $latitude = trim($coordsArray[0]);
    $longitude = trim($coordsArray[1]);
}

if (isset($_POST["Volver"])) {
    header("Location: select_casa.php");
}

if (isset($_POST["GuardarViaje"])) {
    $id_usuario = getUserID($usuario);
    $id_meteorologia = insertMeteorologia($casa);
    insertViaje($casa, $id_usuario, $id_meteorologia);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Casa Rural</title>
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.js"></script>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.7.0/mapbox-gl.css" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="result_style.css">
</head>
<body>
    <div class="container">
        <div class="alert alert-primary" role="alert">
            Bienvenido <?php echo htmlspecialchars($usuario); ?>
        </div>
    <h1 class="my-4 text-center">Mapa de <?php echo htmlspecialchars($casa); ?></h1>
        <div id="map"></div>
        <form id="distanceForm" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="initialLatitude">Latitud Inicial:</label>
                    <input type="text" id="initialLatitude" name="initialLatitude" class="form-control" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="initialLongitude">Longitud Inicial:</label>
                    <input type="text" id="initialLongitude" name="initialLongitude" class="form-control" required>
                </div>
            </div>
            <button type="button" class="btn btn-dark btn-block" onclick="calculateDistance()">Calcular Distancia</button>
        </form>
        <div id="distanceResult" class="mb-4"></div>

        <div id="weather" class="mb-4"></div>

        <form method="post" class="text-center mb-4">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <input type="submit" id="Volver" name="Volver" value="Volver" class="btn btn-secondary btn-block">
                </div>
                <div class="form-group col-md-6">
                    <input type="submit" id="GuardarViaje" name="GuardarViaje" value="Guardar Viaje" class="btn btn-success btn-block">
                </div>
            </div>
        </form>
        
        <div class="mt-4 p-4 border rounded bg-light">
            <h2>Contacto</h2>
            <?php echo getInfo($_GET['casa']); ?>
        </div>
    </div>

    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoibWFyaW9jZXJlIiwiYSI6ImNsd2ozOXA4OTByYzcya2t4cTZqaXUxenYifQ.X0aL0BBv4C_36QKPpSHHJQ';

        let map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11',
            center: [<?php echo $longitude; ?>, <?php echo $latitude; ?>],
            zoom: 12
        });

        let marker = new mapboxgl.Marker()
            .setLngLat([<?php echo $longitude; ?>, <?php echo $latitude; ?>])
            .setPopup(
                new mapboxgl.Popup({ offset: 25 })
                    .setHTML('<h3>Casa Rural</h3><p><?php echo htmlspecialchars($_GET['casa']); ?></p><p><?php echo $latitude; ?><br><?php echo $longitude; ?></p>')
            )
            .addTo(map);

        marker.getElement().addEventListener('click', () => {
            marker.togglePopup();
        });

        function addMarker(latitude, longitude, popupText) {
            let newMarker = new mapboxgl.Marker()
                .setLngLat([longitude, latitude])
                .setPopup(
                    new mapboxgl.Popup({ offset: 25 })
                        .setHTML(popupText)
                )
                .addTo(map);

            newMarker.getElement().addEventListener('click', () => {
                newMarker.togglePopup();
            });
        }

        function calculateDistance() {
            let initialLatitude = document.getElementById('initialLatitude').value;
            let initialLongitude = document.getElementById('initialLongitude').value;

            let from = [initialLongitude, initialLatitude];
            let to = [<?php echo $longitude; ?>, <?php echo $latitude; ?>];

            let url = `https://api.mapbox.com/directions/v5/mapbox/driving/${from[0]},${from[1]};${to[0]},${to[1]}?access_token=${mapboxgl.accessToken}`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    let distance = data.routes[0].distance;
                    let distanceKm = (distance / 1000).toFixed(2);
                    document.getElementById('distanceResult').innerHTML = `<p><strong>Distancia:</strong> ${distanceKm} km</p>`;

                    // Añadir el nuevo marcador
                    addMarker(initialLatitude, initialLongitude, `<p>Ubicación inicial:<br>Latitud: ${initialLatitude}<br>Longitud: ${initialLongitude}</p>`);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function getWeather() {
            let latitude = <?php echo $latitude; ?>;
            let longitude = <?php echo $longitude; ?>;
            let date = '<?php echo $fecha; ?>';
            const apiKey = 'cd34aa2dfa1441e686a123522242305';
            const url = `https://api.weatherapi.com/v1/history.json?key=${apiKey}&q=${latitude},${longitude}&dt=${date}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('weather').innerHTML = `
                        <p><strong>Prediccion meteorológica para el ${date}:</strong></p>
                        <p><strong>Temperatura:</strong> ${data.forecast.forecastday[0].day.avgtemp_c}°C</p>
                        <p><strong>Condición:</strong> ${data.forecast.forecastday[0].day.condition.text}</p>
                    `;
                })
                .catch(error => {
                    console.error('Hubo un problema con la solicitud Fetch:', error);
                    document.getElementById('weather').innerHTML = `
                        <p>Hubo un problema al obtener los datos del clima. Por favor, intenta nuevamente más tarde.</p>
                    `;
                });
        }
        getWeather();
    </script>
</body>
</html>