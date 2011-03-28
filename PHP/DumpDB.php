<?php
/**
 * @author Rickard Andersson <h05rikan@du.se>
 * @package AndroidLockScreen
 * 
 * After generating all the combinations of android lockscreen gestures, you can use this
 * script to dump them to other formats as needed. 
 *
 * This code is released without any license whatsoever, you're free to do what you want with it. 
 */

$dsn = "mysql:dbname=AndroidLockScreen;host=127.0.0.1"; // Change this if needed
$user = ""; // Change this
$pass = ""; // Change this

try {
    $dbh = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    die();
}

$fh = fopen('rtable.dat', 'a+');

foreach ($dbh->query('SELECT * FROM RainbowTable') as $row) {
  fwrite($fh, sprintf("%s|%s\n", $row['hash'], $row['combination']) );  
  echo ".";
}

fclose($fh);
