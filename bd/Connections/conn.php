<?php
ini_set('memory_limit', -1);
@ob_start('ob_gzhandler');
date_default_timezone_set('America/El_Salvador');
setlocale(LC_ALL, 'es_SV.UTF-8', 'esp');
setlocale(LC_TIME, 'es_SV.UTF-8', 'esp');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("MODULO", "EVALUACION 02 DSS404");
// Uso de la clase
define("DBHOST", "localhost:3305");
define("DBUSER", "root");
define("DBPASS", "");
define("DBDATA", "ev02");


spl_autoload_register(function($class) {
    // __DIR__ apunta a C:\xampp\htdocs\Catedra\bd\connection
    // subimos dos niveles hasta C:\xampp\htdocs\Catedra, y entramos a class/
    $f = __DIR__ . '/../../class/' . $class . '.class.php';
    if (! file_exists($f)) {
        die("Autoload error: no pude cargar $f");
    }
    require_once $f;
});


$database = new Database(DBHOST, DBUSER, DBPASS, DBDATA);
$db = $database->db; // Accede a la conexión MySQLi
