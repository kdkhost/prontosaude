<?php
// Setting up the time zone
setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

date_default_timezone_set('America/Sao_Paulo');

// Host Name
$dbhost = 'localhost';

// Database Name
$dbname = 'prontosaude_garden';

// Database Username
$dbuser = 'prontosaude_garden';

// Database Password
$dbpass = 'prontosaude_garden';

$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';

$dominio    = $protocol.$_SERVER['SERVER_NAME'].'/';
$url        = $dominio;

// Defining base url
define("BASE_URL", $url);

// Getting Admin url
define("ADMIN_URL", BASE_URL . "admin" . "/");

try {
	$pdo = new PDO("mysql:host={$dbhost};dbname={$dbname};charset=latin1", $dbuser, $dbpass, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES latin1"
	]);
}
catch( PDOException $exception ) {
	echo "Erro de Conexão: " . $exception->getMessage();
	exit();
}
?>