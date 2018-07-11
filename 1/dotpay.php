<?php
session_start();
require_once dirname(dirname(__FILE__)).'/config/config.php';
require_once dirname(dirname(__FILE__)).'/classes/Indent.php';
require_once dirname(dirname(__FILE__)).'/classes/Users.php';
require_once dirname(dirname(__FILE__)).'/functions/images.php';

$database = dbCon();
    $user_obrazomat = unserialize(base64_decode($_SESSION['user_obrazomat']));

    $info =  unserialize(base64_decode($_SESSION['zamowienie_info']));
    $dane =  unserialize(base64_decode($_SESSION['zamowienie_dane']));
    $wysylka = unserialize(base64_decode($_SESSION['zamowienie_wysylka']));
    $coupon = $_SESSION['zamowienie_coupon'];

    $indent = new Indent();
    $idIndent =  $indent->newIndent($user_obrazomat->getId(), $info, $dane, $wysylka, $coupon);
    $indent = new Indent($idIndent);

    $database->insert("obrazomat_dotpay",[
        "indent" => $idIndent,
        "status" => 0,
        "date_send" => date("Y-m-d H:i:s"),
        "amount" => $indent->getPriceAll(),
    ]);

   $control = $database->id();
   $array = [
        "status" => 1, 
        "opis" => "Płatność za zakupy",
        "link" => "https://". $_SERVER['HTTP_HOST'] ."/zamowienie-zlozone.php",
        "cena" => $indent->getPriceAll(), 
        "control" => $control,
    ];
    
    echo json_encode($array);
