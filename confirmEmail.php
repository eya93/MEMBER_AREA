<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 19/08/2018
 * Time: 17:35
 */
require_once 'functions.php';
if (!isset($_GET['email']) || !isset($_GET['token'])) {// pas de get dans le lien
    redirect('signup');
} else {// il ya un mail et un token dans l'url
    $cnxPDO  = new PDO('mysql:host=localhost;dbname=memberarea', 'root','', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
   // recupere les valeurs de token et de mail
    $email= htmlspecialchars ($_GET['email']);
    $token= htmlspecialchars ($_GET['token']);
    //recupere la ligne de BD qui a le mail et le token recuperés et il n'est pas activé
    $reponse = $cnxPDO->prepare( "SELECT id FROM membres WHERE email = :email AND user_token= :token AND isConfirmedEmail= :conf");
    $reponse ->execute(array(
        'email' => $email,
        'token' => $token,
        'conf' => 0
    ));

    if ($reponse->rowCount() > 0){//  si l'email existe avec le meme token et qu'il n'est pas activé
        $reponse = $cnxPDO->prepare( "UPDATE membres SET isConfirmedEmail = 1, user_token= '' WHERE email = :email");
        $reponse->execute(array('email' => $email));
        echo "email veifié";
        //redirect to signin
        redirect('signin');
    } else {
        redirect('signup');
        //verif si l'email est activé avec un token vide
//        $reponse1 = $cnxPDO->prepare( "SELECT id FROM membres WHERE email = :email AND user_token= :token AND isConfirmedEmail= :conf");
//        $reponse1 ->execute(array(
//            'email' => $email,
//            'token' => '',
//            'conf' => 1
//        ));
//        if ($reponse1->rowCount() > 0) {
//            //redirect to signin
//            redirect('in');
//        } else {
//            //redirect to signup
//           ;
//        }
    }
}
