<?php
$servername = 'db';
$username = 'myuser';
$password = 'mypassword';
$database = 'myapp_db';

echo $servername . "<br />";
echo $username   . "<br />";
echo $password   . "<br />";
echo $database   . "<br />";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

echo "<h1>Connessione riuscita a MySQL!</h1>";

$result = $conn->query("SHOW TABLES;");
echo "<pre>";
while ($row = $result->fetch_array()) {
    print_r($row);
}
echo "</pre>";

$conn->close();
?>
