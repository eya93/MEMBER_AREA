<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 20/08/2018
 * Time: 15:09
 */
require_once 'functions.php';

if (isset($_GET['email']) && isset($_GET['token'])) {// il ya un mail et un token dans l'url
    $cnxPDO  = new PDO('mysql:host=localhost;dbname=memberarea', 'root','', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
    // recupere les valeurs de token et de mail
    $email= htmlspecialchars ($_GET['email']);
    $token= htmlspecialchars ($_GET['token']);
    $reponse = $cnxPDO->prepare( "SELECT id FROM membres WHERE email = :email AND pwd_token= :token AND pwd_token <> '' AND pwd_token_expire > NOW()");
    $reponse ->execute(array(
        'email' => $email,
        'token' => $token,
    ));
    if ($reponse->rowCount() > 0){
        $reponse = $cnxPDO->prepare( "UPDATE membres SET pwd_token = '' WHERE email = :email");
        $reponse->execute(array('email' => $email));
        //redirect to pwdModif
        header ( 'Location: resetPassword_Controller.php?reset=success');
        exit();
    } else {//someone trying to cheat
        redirect('signin');
    }
} else {
    redirect('signin');
}