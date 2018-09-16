<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 20/08/2018
 * Time: 15:57
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
        // define variables and set to empty values
        $pwd = $pwdConf = $ErrMsg = "";
        // when submitting check if the different fields are not empty and each field has a valid format.
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // validation champ pwd ------------------------------------------------------>
            if (empty($_POST["pwd"])) {
                $ErrMsg = "*Veuillez saisir votre mot de passe";;
            } else {
                $pwd = htmlspecialchars($_POST["pwd"]);
                if (!preg_match("/[a-z]/", $pwd) || !preg_match("/[A-Z]/", $pwd) || !preg_match("/[0-9]/", $pwd)) {
                    $ErrMsg = "*Mot de passe doit contenir slt des lettres,au moins un chiffre et  au moins une lettre majuscule";

                } elseif (strlen($pwd) < 8) {
                    $ErrMsg = "*Mot de passe doit contenir au moins 8 caractères";

                } else {
                    // validation champ pwd confirmation ------------------------------------------------------>
                    if (empty($_POST['pwdConfirmation'])) {
                        $ErrMsg = "*Veuillez confirmer votre mot de passe";

                    } else {
                        $pwdConf = htmlspecialchars($_POST['pwdConfirmation']);
                        if ((strcmp($pwd, $pwdConf) != 0) && $ErrMsg == "") {
                            $ErrMsg = "*Les deux mots de passe sont différents";
                        }
                    }
                }
            }
            //*******if  two are valid  ***************************************************************************
            if (empty($ErrMsg)) {
                try {//update the new value of pwd
                    //0//instancier objet pdo (qui va nous epermettre de connecter et gérer ànotre bd )
                    $cnxPDO = new PDO('mysql:host=localhost;dbname=memberarea', 'root', '', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
                    $hashedPwd = password_hash($pwd,PASSWORD_BCRYPT);
                    $reponse = $cnxPDO->prepare("UPDATE membres SET password= :pwd, pwd_token= :pwd_token WHERE email = :email");
                    $reponse->execute(array(
                        'pwd' => $hashedPwd,
                        'pwd_token' => '',
                        'email' => $email
                    ));
                    redirect('signin');
                } catch (PDOException $e) {
                    die($e->getMessage());
                }
            }
        }
    } else {//mail n'existe pas
        redirect('signin');
    }
} else {
    redirect('signin');
}

?>
<?php include_once 'header.php'?>
<!--**************reset form***************************************************************************************-->
<div class="sections">
    <div class="section_img">
        <img src="./images/member.jpg" alt="phpto"  width="100%"  >
    </div>
    <div class="section_form">
        <?php
        if (!empty($ErrMsg)){
            ?>
            <div class="Msg alert alert-danger" role="alert">
                <strong>Attention<br></strong>
                <span> <?php echo $ErrMsg;?><br></span>
            </div>
            <?php
        }
        ?>

        <div class="form-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]."?token=".$token."&email=".$email);?>">
                <h1 class="h3 mb-3 font-weight-normal">Réinitialisation de mot de passe</h1>
                <!-- champ pwd --------------------------------------------------------->
                <div class="form-group">
                    <label for="inputPassword" >mot de passe:</label>
                    <input type="password" id="inputPassword"  name="pwd" class="form-control form-control-md" placeholder="mot de passe" >
                </div>
                <!-- champ pwd Confirmation -------------------------------------------->
                <div class="form-group">
                    <label for="inputPasswordConf" >confirmation mot de passe:</label>
                    <input type="password" id="inputPasswordConf" name="pwdConfirmation" class="form-control form-control-md" placeholder="confirmer mot de passe" >
                </div>

                <!-- ------------------------------------------------------------------->
                <div class="form-group" >
                    <button class="btn btn-md btn-success btn-block" type="submit">Valider</button>

                </div>

            </form>
        </div>

    </div>
</div>

<?php include_once 'footer.php'?>
