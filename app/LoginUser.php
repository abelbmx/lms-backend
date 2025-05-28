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
//$request = 0;
$request = json_decode($input);
$table = "users";
//$table = " users as a,user_comunity as b,comunity as c "
//$timestamp = $request->timestamp;
//$email = "admin@admin.com";
//echo password_hash($request->password, PASSWORD_DEFAULT)."\n";
//$password = "password";

$email = $request->email;
//echo password_hash($request->password, PASSWORD_DEFAULT)."\n";
$password = $request->password;

//echo $password;
//$password = password_hash($request->password, PASSWORD_DEFAULT);

//$hashed_password = password_hash($request->password, PASSWORD_DEFAULT);

//var_dump($hashed_password);
//
//echo $password."\n";

}

//set JSON array
$rows = array();

// set db connection
$servername = "localhost"; //don't change this value
$user = "surlitcl_surlit_cl"; // put the user id with granted access to database
$pass = "DpZpYf&AdTa%"; // put here the password of the user granted to database
//$dbname = $user."_Q4data"; // database is identified as concatenation of username + "_" + Q2data string
$dbname = "surlitcl_agua"; // database is identified as concatenation of username + "_" + Q2data string

// Create db connection
$conn = new mysqli($servername, $user, $pass, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8")) {
    //printf("Error cargando el conjunto de caracteres utf8: %s\n", $conn1->error);
    //exit();
} else {
    //printf("Conjunto de caracteres actual: %s\n", $conn1->character_set_name());
}

// query database
if ($request === null) {
	echo "404";
} else {
	$sql = "SELECT * FROM ";
	$sql = $sql .$table. " WHERE   email = '".$email."' ";
	$result = $conn->query($sql);
		if ($result->num_rows > 0) {
				while($row = $result->fetch_assoc()) {

					//$salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), TRUE));


					$hash_password = password_verify($password,$row["password"]);

					//$validatepassword = hash('sha512', $password . $salt);

          //echo $hash_password;
					//$hash_password = password_verify($validatepassword, $row["password"]);
					if($hash_password === true) {
					       $rows[] = $row;
					}

					/*f($salted_password == $row["password"]) {
						$rows[] = $row;
					}*/
				}
			print json_encode($rows);
			$rows[] = "";
		} else {
			$rows[] = null;
    		print json_encode($rows);
		}
}
// close db connection
$conn->close();
?>
