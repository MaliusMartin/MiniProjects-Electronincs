<?php
$conn = mysqli_connect("127.0.0.1", "root", "", "bs18d20");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to get the latest temperature value
$query = "SELECT Temp FROM temperature ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $query);
$latestTemp = 0;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $latestTemp = $row['Temp'];
}

mysqli_close($conn);

echo $latestTemp;
?>

