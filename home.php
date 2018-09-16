<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 20/08/2018
 * Time: 19:46
 */
session_start();
require_once 'functions.php';
if (!isset($_SESSION['member_id'])) {
    if(isset($_COOKIE['email'])) {
        $cnxPDO = new PDO('mysql:host=localhost;dbname=memberarea', 'root', '', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
        $reponse = $cnxPDO->prepare("SELECT * FROM  membres WHERE email= ?");
        $reponse->execute(array($_COOKIE['email']));
        $member = $reponse->fetch(PDO::FETCH_OBJ);
        $_SESSION['member_id'] = $member->id;
        $_SESSION['member_nom'] = $member->nom;
        $_SESSION['member_prenom'] = $member->prenom;
        $_SESSION['member_email'] = $member->email;
    }  else {
        redirect('signin');
    }
}
?>

<?php include_once 'header.php'?>
<section class="main-container ">
    <div class="welcome-section">
        <?php
        if (isset($_SESSION['member_id'])) {
            ?>
            <div class="welcome-part" style="background: rgba(0,0,0,0.5);border: 2px solid forestgreen;padding: 50px; justify-content: center; align-items: center;text-align: center;">
                <h2 style="color: forestgreen;"><?= strtoupper($_SESSION['member_nom']) ." ". ucfirst(strtolower($_SESSION['member_prenom']))?> </h2>
                <p style="color: white; font-size: 30px;"><?php echo "\"Bienvenu,vous etes maintenant connecté dans notre espace membre.\"" ?></p>
            </div>
        <?php
        }
        ?>
    </div>


</section>

<?php include_once 'footer.php'?>

