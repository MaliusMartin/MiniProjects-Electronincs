<!DOCTYPE html>
<html>
<body>

<?php
$dbname = 'bs18d20';
$dbuser = 'root';
$dbpass = '';
$dbhost = '127.0.0.1';

$connect = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

if (!$connect) {
    echo "Error: " . mysqli_connect_error();
    exit();
}

echo "Connection Success!<br><br>";



$celsius = isset($_GET["celsius"]) ? $_GET["celsius"] : "";

$query = "INSERT INTO temperature(Temp) VALUES ('$celsius')";
$result = mysqli_query($connect, $query);

if ($result) {
    echo "Insertion Success!<br>";
} else {
    echo "Error: " . mysqli_error($connect) . "<br>";
}

mysqli_close($connect);

?>

</body>
</html>
