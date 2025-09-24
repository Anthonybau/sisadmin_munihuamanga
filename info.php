<?php

// phpinfo();

try {
    $pdo = new PDO("pgsql:host=localhost;dbname=sisadmin_mphuamanga", "postgres", "");
    echo "✅ Conexión PDO exitosa a PostgreSQL.";
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}



// define("DB_DATABASE", "sisadmin_mphuamanga");
// define("DB_HOST", "localhost");
// define("DB_PORT", "5432");
// define("DB_USER", "postgres");
// define("DB_PASSWORD", "");

// try {
//     $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE;
//     $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
//     echo "✅ Conexión PDO exitosa a PostgreSQL.";
// } catch (PDOException $e) {
//     echo "❌ Error de conexión: " . $e->getMessage();
// }
