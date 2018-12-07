<?php

session_start();
if(!isset($_SESSION["user"])){
    header("Location: login.php?message=Inicia Sesion");
}

if($_SESSION["user"]["rol"] == 0){
    include_once("alumnoView.php");
}else{
    include_once("adminView.php");
}

?>