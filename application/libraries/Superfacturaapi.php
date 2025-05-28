<?php
error_reporting( error_reporting() & ~E_NOTICE );

class SuperFacturaAPI {
	var $version = '1.6-php';

	function __construct($login, $password) {
		$this->login = $login;
		$this->password = $password;
		$this->inSASM = function_exists('ktkError');
	}

	function SendDTE($data, $ambiente, $options = NULL, $debug = false) {
		$debug = 0;

		if(!$data) $this->Error('Se pas� un arreglo vac�o a la funci�n SendDTE()');

		$options['ambiente'] = $ambiente;

		if($savePDF = $options['savePDF']) {
			$options['getPDF'] = true;
		}

		if($saveXML = $options['saveXML']) {
			$options['getXML'] = true;
		}

		if($saveEscPos = $options['saveEscPos']) {
			$options['getEscPos'] = true;
		}

		if($options['devel']) {
			$host = 'http://dte.devel.imatronix.com';
		} else {
			$host = 'https://superfactura.cl';
		}

		$options['version'] = $this->version . $GLOBALS['SF_API_PLATFORM'];

		$rpcRes = SASM_RPC2($host, array(
			'user' => $this->login,
			'pass' => $this->password,
			'dte-data' => array(
				'options' => $options, // Enviar options antes, ya que data podr�a venir con problemas
				'data' => $data,
			)
		), true);

		$appRes = $rpcRes['response'];
		$ack = $rpcRes['ack'];

		if($appRes['code'] == 'user-error') {
			$this->Warning('Error en API SuperFactura (client-side)');
			$this->Error($appRes['message'] .'<br>Contacte a soporte.', $appRes['title']);
		}

		if($savePDF) {
			ktkWriteFile("{$savePDF}.pdf", $appRes['pdf']);
			if($cedible = $appRes['pdfCedible']) {
				ktkWriteFile("{$savePDF}-cedible.pdf", $cedible);
			}
			unset($appRes['pdf'], $appRes['pdfCedible']);
		}

		if($saveXML) {
			ktkWriteFile("{$saveXML}.xml", $appRes['xml']);
			unset($appRes['xml']);
		}

		if($saveEscPos) {
			ktkWriteFile($saveEscPos, $appRes['escpos']);
			unset($appRes['escpos']);
		}

		return $appRes;
	}

	function GetDocsRecibidos($rut, $periodo, $getXML = false) {
		$host = 'https://superfactura.cl';
		$rpcRes = SASM_RPC2($host . '?cmd=get-docs-recibidos', array(
			'user' => $this->login,
			'pass' => $this->password,
			'rut' => $rut,
			'periodo' => $periodo,
			'get-xml' => $getXML,
		), true);

		$appRes = $rpcRes['response'];
		$ack = $rpcRes['ack'];

		if($ack == 'error') {
			$this->Error($appRes['message'], $appRes['title'], 'SendDTE Error'); // Ej: Error de Schema
		}

		if($appRes['code'] == 'user-error') {
			$this->Warning('Error en API SuperFactura (client-side)');
			$this->Error($appRes['message'] . '<br>Contacte a soporte.', $appRes['title']);
		}

		return $appRes;
	}

	// --- Aux ---

	function Message($prefix, $msg, $title) {
		return "$prefix: " . ($title ? "$title - $msg" : $msg) . "<br>";
	}

	function Warning($msg, $title = NULL) {
		if($this->inSASM) {
			ktkUserWarning($msg, $title);
		} else {
			echo $this->Message('WARNING', $msg, $title);
		}
	}

	function Error($msg, $title = NULL) {
		if($this->inSASM) {
			ktkUserError($msg, $title);
		} else {
			// throw new Exception($this->Message('ERROR', $msg, $title));
			if($title) {
				die("ERROR: $title. $msg");
			} else {
				die("ERROR: $msg");
			}
		}
	}
}

function SF_Curl($arr) {
	$url = $arr['url'];
	$post = $arr['post'];
	$maxRetries = 3;

	$options = array();

    $options[CURLOPT_SSL_VERIFYPEER] = false;
    $options[CURLOPT_SSL_VERIFYHOST] = 2;

	$options[CURLOPT_RETURNTRANSFER] = 1;
	$options[CURLOPT_TIMEOUT] = 30000;
	$options[CURLE_OPERATION_TIMEOUTED] = 30000;
	$options[CURLOPT_CONNECTTIMEOUT] = 1000;
	$options[CURLOPT_URL] = $url;
	$options[CURLOPT_AUTOREFERER] = 1;

	if(isset($post)) {
		$options[CURLOPT_POST] = true;
		if(is_array($post)) {
			$options[CURLOPT_POSTFIELDS] = http_build_query($post);

		} else {
			$options[CURLOPT_POSTFIELDS] = $post;
		}
	}

	$retries = 0;

	for(;;) {
		$ch = curl_init();

		foreach(array_keys($options) as $key) {
			$val = $options[$key];
			$res = curl_setopt($ch, $key, $val);
			if(!$res) {
				if($key == CURLOPT_WRITEHEADER) {
					// Genera error en windows (bug de PHP?)
				} else if($key == CURLOPT_SSLCERT) {
					die('CURL ERROR: Certificado inv�lido.');
				} else {
					die("CURL ERROR: curl_setopt $key.");
				}
			}
		}

		$res = curl_exec($ch);

		set_time_limit(0);

		$errorMsg = curl_error($ch);
		$errorNumber = curl_errno($ch);

		curl_close($ch);

		if($errorMsg) {
			if($retries++ < $maxRetries) {
				continue; // RETRY
			}
			die("CURL ERROR: $errorNumber: $errorMsg");
		}
		break;
	}

	return $res;
}

function SASM_RPC2($url, $data = NULL, $showError = true) {
	$rawResponse = SF_Curl(array('url' => $url, 'post' => $data));
	$rpcResponse = @unserialize(gzuncompress($rawResponse));

	$ack = $rpcResponse['ack'];

	if($ack == 'ok' || $ack == 'error') {
		// Capa 1 - Protocolo ok.
		// Protocolo de comunicaci�n funcion� (independiente de otros errores)

		if($showError && $ack == 'error') {	 // Se produjo un error enviado por el servidor (no de protocolo). Capa 2. No siempre se querr� mostrar. A veces se necesita interceptar y tratar para seguir procesando otros requests.
			$response = $rpcResponse['response'];
			$code = $response['code'];
			die("RPC Error: " . $response['title'] . ' - ' . $response['message']);
		}
		return $rpcResponse;

	} else {
		// Error del protocolo
		die("RPC Error: Unknown Error (no se recibi� respuesta)");
	}
}

if(!function_exists('http_build_query')) {
	function makeQueryString($params, $prefix = '', $removeFinalAmp = true) {
		$queryString = '';
		if (is_array($params)) {
			foreach (array_keys($params) as $key) {
				$value = $params[$key];
				$correctKey = $prefix;
				if ('' === $prefix) {
					$correctKey .= $key;
				} else {
					$correctKey .= "[" . $key . "]";
				}
				if (!is_array($value)) {
					$queryString .= urlencode($correctKey) . "=" . urlencode($value) . "&";

				} else {
					$queryString .= makeQueryString($value, $correctKey, false);
				}
			}
		}
		if ($removeFinalAmp === true) {
			return substr($queryString, 0, strlen($queryString) - 1);
		} else {
			return $queryString;
		}
	}

	function http_build_query($params, $prefix = '') {
		return makeQueryString($params, $prefix);
	}
}

if(!function_exists('ktkWriteFile')) {
	function ktkWriteFile($file, $data, $append = false) {
		$f = fopen($file, $append ? 'a' : 'w');
		if(!$f) {
			echo "WARNING: No se pudo crear archivo '$file'.";
			return false;
		} else {
			$res = fwrite($f, $data);
			if($res === false) return false;
			fclose($f);
		}
		return true;
	}
}
