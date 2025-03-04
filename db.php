<?php 

//                ip         db user, pwd, db name
$db = new mysqli('localhost', 'root', 'root', 'pos');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

?>