<?php
session_start();
include_once ("functions.php");
include_once ("mysqlconn.php");
if(isset($_POST["login"])){
    $user = getUser($_POST["email"], $_POST["password"]);
    if(!empty($user)){
        $_SESSION["user"] = $user;
        header("Location: index.php");
    }else{
        header("Location: login.php?message=Usuario no encontrado");
    }
}
if(isset($_POST["card-section"])){
    echo buildCardContainer();
}
if(isset($_POST["tableData"])){
    echo getTableData($_POST["table"]);
}
if(isset($_POST["createForm"])){
    echo createForm($_POST["model"]);
}
if(isset($_POST["editForm"])){
    echo editForm($_POST["model"], $_POST["id"]);
}
if(isset($_POST["formType"])){
    if($_POST["formType"] == "insert"){
        echo json_encode(array("message" => insertRegister(), "table" => $_POST["table_model"]));
    }else{
        echo json_encode(array("message" => editRegister(), "table" => $_POST["table_model"]));
    }
}
if(isset($_POST["arrendarForm"])){
    echo userHistorialForm($_POST["model"], $_POST["id"], $_SESSION["user"]["id"], 1);
}
if(isset($_POST["returnForm"])){
    echo userHistorialForm($_POST["model"], null, $_SESSION["user"]["id"],0 );
}
?>