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
  //$request = 0;
  $n_cliente = $request->n_cliente;
  //$n_cliente = "406239";
}

//set JSON array
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

// query database
if ($request === null) {
	echo "404";
} else {
			$sql = "SELECT * FROM cliente as c, lectura as l, medidor as m where l.id_medidor = m.id and c.id = l.id_cliente and l.id_cliente = ".$n_cliente."  order by l.id DESC LIMIT 1";
			$result = $conn->query($sql);
      //echo $sql;
			//print_r($result);
			if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {
					$rows[] = $row;
				}
				print json_encode($rows);
			//$rows[] = "";
			} else {
				$rows[] = null;
				print json_encode($rows);
				//return null;
			}
}
// close db connection
$conn->close();

?>
