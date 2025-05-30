<?php

$conn = new mysqli('localhost', 'root', '', 'mental_health');

if($conn->connect_error){
    die("cant connect".$conn->connect_error);
}
?>