<?php
session_start();
include_once 'classes/Users.php';

    include_once 'classes/Contact.php';
    $contact = new Contact(1);

    include_once 'classes/Social.php';
    $social = new Social("facebook");
    $facebook = $social->getSite();
    $social = new Social("instagram");
    $instagram = $social->getSite();

    if(isset($_SESSION['user_obrazomat'])) {
        header ('Location: historia-zamowien.php');
    }

    if (isset($_POST['action']) || isset($_GET['action'])) {
        $action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

        switch ($action) {
            case 'register':
                try {
                    $user= new Users();
                    $id = $user->register($_POST);
                    header ('Location: rejestracja.php?user='. $id);
                } catch (Exception $e) {
                    $_SESSION['errors'][] = $e -> getMessage();
                    include_once dirname(__FILE__).'/displayErrors.php';    
                    include dirname(__FILE__).'/templates/logowanie.html.php';
                }
                break;
            case 'login' :
                try {
                    $user = new Users();
                    $user-> login($_POST);
                    $user = new Users($user->getId());
                    $_SESSION['user_obrazomat']= base64_encode(serialize($user));
                    header("Location: historia-zamowien.php");
                } catch (Exception $e) {
                    $_SESSION['errors'][] = $e -> getMessage();
                    include_once dirname(__FILE__).'/displayErrors.php';    
                    include dirname(__FILE__).'/templates/logowanie.html.php';
                }
            break;
            case 'logout':
				session_destroy();
				setcookie('remember', $code, time() - 1, "/");
				header("Location: index.php");
			break;
        }
    } else {
        include dirname(__FILE__).'/templates/logowanie.html.php';
    }
