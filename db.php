<?php
$serverName = "localhost\\SQLEXPRESS";
$database = "jogodavelha";
$username = "ana";      // O nome que você criou
$password = "180505";       // A senha que você definiu

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=Yes;", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexão OK!";
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>
