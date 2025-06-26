<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "arco_bdd";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Query to fetch movements
$sql = "SELECT id, producto, cantidad, tipo, fecha, usuario FROM movimientos";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "0 resultados";
}
$conn->close();

// Convert data to JSON
echo json_encode($data);
?>