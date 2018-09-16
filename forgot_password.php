<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 20/08/2018
 * Time: 13:36
 */

// define variables and set to empty values
$email = $pwd =$rememberMe="";
$emailErr = $pwdErr ="";
$ErrTab=[];

// when submitting check if the different fields are not empty and each field has a valid format.
// at the end creat an error msg for each field and push them in array
require_once 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // validation champ email ------------------------------------------------------>
    if (empty($_POST["email"])) {
        $emailErr = "* Veuillez saisir votre email";
    } else {
        $email = htmlspecialchars($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "* Invalid email";
        }
    }
    //*******if email field has valid verif the existance of the email in the DB***************************************************************************
    if (empty($emailErr)){// if email is valid communicate with BD and verify if the email already exist if not show an error msg
        try {
            //0//instancier objet pdo (qui va nous epermettre de connecter et gérer ànotre bd )
            $cnxPDO  = new PDO('mysql:host=localhost;dbname=memberarea', 'root','', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
            //1// verfif que l'email exist or no
            $reponse= $cnxPDO-> prepare("SELECT * FROM  membres WHERE email= ?");
            $reponse->execute(array($email));
            if ($reponse->rowCount() > 0){//1 si email existe
                //creat a token
                $token= creatToken(20);
                //update pwd-token and its expiration date
                $reponse = $cnxPDO->prepare( "UPDATE membres SET pwd_token_expire= DATE_ADD(NOW(),INTERVAL  5 MINUTE ), 
                                                                            pwd_token= :token 
                                                                        WHERE email = :email");
                $reponse->execute(array(
                    'token' => $token,
                    'email' => $email
                ));
                //envoyer un mail de réinitiasation de mot de passe
                $to= $email;
                $subject= "Réinitialisation de mot de passe de compte Member Area";
                $message='<h1> Bonjour Eya,</h1>
                            Vous avez demandé de modifier votre mot de passe.<br>
                            Pour modifier votre mot de passe Member Area, cliquez sur le lien suivant :<br>';
                $message .= "<a href='http://localhost:63342/gomyphp/MEMBER_AREA/resetPassword_Controller.php?email=$email&token=$token'>Cliquez ici</a>";
                $message.='<br><br>Ce lien expirera dans 5 minutes, assurez-vous de l’utiliser bientôt.<br> <br>
                            Cordialement,<br>L’équipe Member Area';
                $headers=  'From: "équipe Member area"<eyabensaid20@gmail.com>'."\n".
                    'Reply-To:eyabensaid20@gmail.com'."\r\n".
                    'Content-type: text/html; chaset="UTF-8"'."\n".
                    'Content-Transfert-Encoding: 8bit';
                if(mail($to,$subject,$message,$headers)){// mail envoyé avec success
                    $SuccessMsg = '* Verifiez votre compte email. Un mail de réinitialisation mot de passe a été envoyé ';
                    $email="";
                } else {//le mail n'a pas été envoyé
                    $emailErr='* erreur s\'est produit, essaye une autre fois';
                }
            } else {//email does'nt exist
                $emailErr = " * Réessayez ";
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}

?>
<?php include_once 'header.php'?>
<!--**************form of rest password***************************************************************************************-->
<div class="sections">
    <div class="section_img">
        <img src="./images/member.jpg" alt="phpto"  width="100%"  >
    </div>
    <div class="section_form">
        <!--------  affichage de Error msg  ou de sucess message s'ils ne sont pas vides-->
        <?php
        if(!empty($emailErr)){
        ?>
            <div class="Msg alert alert-danger" role="alert">
                <span> <?php echo $emailErr;?><br></span>
            </div>
        <?php
        }
        if (!empty($SuccessMsg)){
        ?>
            <div class="Msg alert alert-success" role="alert">
                <span> <?php echo $SuccessMsg;?><br></span>
            </div>
            <?php
         }
         ?>

        <div class="form-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <h1 class="h3 mb-3 font-weight-normal">Mot de passe oublié</h1>
                <!-- champ email ------------------------------------------------------>
                <div class=" form-group ">
                    <label for="inputEmail" >adresse mail:</label>
                    <input type="email" id="inputEmail" name="email" class="form-control form-control-md" placeholder="Email"  autofocus="">
                </div>
                <!-- ---button-------------------------------------------------------->
                <div class="form-group" style="display: flex; flex-direction: column; align-items: center ">
                    <button class="btn btn-md btn-success btn-block" type="submit">Réinitialiser mot de passe</button>
                </div>
            </form>
        </div>

    </div>
</div>

<?php include_once 'footer.php'?>
