<?php

$host = "localhost";
$userName = "root";
$password = "";
$charset = 'utf8mb4';
$db = "Inventario";

// Connection

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
    if(!isset($GLOBALS["pdo"])){
        $GLOBALS["pdo"] = new PDO($dsn, $userName, $password, $options);
    }
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

?>