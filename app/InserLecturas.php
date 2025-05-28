<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
       header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

   if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
      header("Access-Control-Allow-Headers:     {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
 }

 // set variables from POST form
$input = file_get_contents('php://input');
if (isset($input)) {
  $request = json_decode($input);
  $fecha_lectura = $request->fecha_lectura;
  $id_cliente = $request->id_cliente;
  $id_medidor = $request->id_medidor;
  $id_periodo = $request->id_periodo;
  $lectura_anterior = $request->lectura_anterior;
  $lectura = $request->lectura;
  $observacion = $request->observacion;
  $activa = 1;
}

$rows = array();
// set db connection
$servername = "localhost"; //don't change this value
$user = "surlitcl_surlit_cl"; // put the user id with granted access to database
$pass = "DpZpYf&AdTa%"; // put here the password of the user granted to database
//$dbname = $user."_Q4data"; // database is identified as concatenation of username + "_" + Q2data string
$dbname = "surlitcl_agua"; // database is identified as concatenation of username + "_" + Q2data string

$conn = new mysqli($servername, $user, $pass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// add PIN request
if ($request === null) {
	echo "404";
} else {

			$sqlupdate = "insert into lectura_app (fecha_lectura,id_cliente,id_medidor,id_periodo,lectura_anterior,lectura,observacion,activa)
      VALUES ('".$fecha_lectura."',".$id_cliente.",".$id_medidor.",".$id_periodo.",".$lectura_anterior.",".$lectura.",'".$observacion."',".$activa.") ";
			error_log(print_r($sql, TRUE));
			if ($conn->query($sqlupdate) === TRUE) {
			     echo "200";
			} else {
			     echo "500". $sqlupdate . "<br>" . $conn->error;
			}
}

// close db connection
$conn->close();
?>
