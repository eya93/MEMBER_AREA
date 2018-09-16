<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 17/08/2018
 * Time: 14:55
 */

//*********validation des inputs et ajouter le nv utilisateur à la BD********************************
require_once 'functions.php';
if (isset($_SESSION['member_id'])) {
    header ( 'Location: home.php');
    exit();
}
// define variables and set to empty values
$nom = $prenom = $email = $pwd = $pwdConf =$rememberMe="";
$nomErr = $prenomErr = $emailErr = $pwdErr = $pwdConfErr =$rememberMeErr=$SuccessMsg="";
$ErrTab=[];

// when submitting check if the different fields are not empty and each field has a valid format.
// at the end creat an error msg for each field and push them in array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // validation champ nom ------------------------------------------------------>
    if (empty($_POST["nom"])) {
        $nomErr="*Veuillez saisir votre nom";
        array_push($ErrTab,$nomErr) ;
    } else {
        $nom = htmlspecialchars($_POST["nom"]);
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/",$nom)) {
            $nomErr = "*Le nom doit contenir slt des lettres ou espaces";
            array_push($ErrTab,$nomErr) ;
        }
    }
    // validation champ prenom ------------------------------------------------------>
    if (empty($_POST["prenom"])) {
        $prenomErr="*Veuillez saisir votre prenom";
        array_push($ErrTab,$prenomErr) ;
    } else {
        $prenom = htmlspecialchars($_POST["prenom"]);
        // check if name only contains letters and whitespace
        if (!preg_match("/^[a-zA-Z ]*$/",$prenom)) {
            $prenomErr = "*Le prenom doit contenir slt des lettres ou espaces";
            array_push($ErrTab,$prenomErr) ;
        }
    }
    // validation champ email ------------------------------------------------------>
    if (empty($_POST["email"])) {
        $emailErr = "*Veuillez saisir votre email";
        array_push($ErrTab,$emailErr) ;
    } else {
        $email = htmlspecialchars($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "*Le format de l'email est invalide";
            array_push($ErrTab,$emailErr);
        }
    }
    // validation champ pwd ------------------------------------------------------>
    if (empty($_POST["pwd"])) {
        $pwdErr= "*Veuillez saisir votre mot de passe";
        array_push($ErrTab,$pwdErr) ;
    } else {
        $pwd = htmlspecialchars($_POST["pwd"]);
        if (!preg_match("/[a-z]/",$pwd) || !preg_match("/[A-Z]/",$pwd) || !preg_match("/[0-9]/",$pwd)) {
            $pwdErr= "*Mot de passe doit contenir slt des lettres,au moins un chiffre et  au moins une lettre majuscule";
            array_push($ErrTab,$pwdErr) ;
        } elseif (strlen($pwd) < 8) {
            $pwdErr= "*Mot de passe doit contenir au moins 8 caractères";
            array_push($ErrTab,$pwdErr) ;
        } else {
            // validation champ pwd confirmation ------------------------------------------------------>
            if (empty($_POST['pwdConfirmation'])) {
                $pwdConfErr = "*Veuillez confirmer votre mot de passe";
                array_push($ErrTab,$pwdConfErr) ;
            } else {
                $pwdConf = htmlspecialchars($_POST['pwdConfirmation']);
                if ( (strcmp($pwd, $pwdConf) != 0) && $pwdErr == "") {
                    $pwdConfErr = "*Les deux mots de passe sont différents";
                    array_push($ErrTab,$pwdConfErr);
                }
            }
        }
    }

    // validation champ checkbox remember me ------------------------------------------------------>
    if (empty($_POST["rememberMe"])) {
        $rememberMeErr =  "*Veuillez accepter les termes et les conditions";
        array_push($ErrTab,$rememberMeErr);
    } else {
        $rememberMe = $_POST["rememberMe"];
    }
    //*******if all fields are valid add the new user to the BD****************************************************************************
    if (empty($ErrTab)){// if all fields are valid communicate with BD and verify if the email already exist if not add the new user
        try {
            //0//instancier objet pdo (qui va nous epermettre de connecter et gérer notre bd )
            $cnxPDO  = new PDO('mysql:host=localhost;dbname=memberarea', 'root','', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
            //1// verfif que l'email n'existe pas

            $reponse= $cnxPDO-> prepare("SELECT * FROM  membres WHERE email=:email");
            $reponse->execute(array(
                    'email' =>$email
            ));

            if ($reponse->rowCount() > 0){// si email existe de;a
                array_push($ErrTab," email existe deja");
            } else {// si email n'existe apas
                //2// preparation and execution of the insert requet
                $hashedPwd = password_hash($pwd,PASSWORD_BCRYPT);
                //$hashedPwd = crypt($pwd);
                //$hashedPwd=$pwd;
                $token= creatToken(20);
                $reponse1 = $cnxPDO->prepare("INSERT INTO membres (nom,prenom,email,password,isConfirmedEmail,user_token) VALUES (:nom,:prenom,:email,:pwd,:isConf,:token)");
                $reponse1->execute(
                    array(
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'pwd' => $hashedPwd,
                        'isConf' => 0,
                        'token' => $token
                    )
                );
                // //envoi d'un mail de confirmation
                $to= $email;
                $subject= "Bienvenu $nom sur Member Area";
                $message=  'Votre inscription a ete effectue avec succes :D.<br>';
                $message.= 'Merci d\'activer votre compte en cliquant sur le lien ci-dessous <br> <br>';
                $message .= "<a href='http://localhost:63342/gomyphp/MEMBER_AREA/confirmEmail.php?email=$email&token=$token'>Cliquez ici</a>";
                $headers=  'From: "Member area team"<eyabensaid20@gmail.com>'."\n".
                    'Reply-To:eyabensaid20@gmail.com'."\r\n".
                    'Content-type: text/html; chaset="UTF-8"'."\n".
                    'Content-Transfert-Encoding: 8bit';
                if(mail($to,$subject,$message,$headers)){// mail envoyé avec success
                    $SuccessMsg = 'Vous etes inscrit avec succes. Vérifier votre compte, un mail a été envoyé. ';
                    $nom=$prenom=$email="";
                } else {//le mail n'a pas été envoyé
                    array_push($ErrTab,'erreur s\'est produit, essaye une autre fois');
                }


            }
        } catch (PDOException $e) {
            die($e->getMessage());
          }
    }
}
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>

    <?php include_once 'header.php'?>
<!--**************notre formulaire d'incription***************************************************************************************-->
<div class="sections">
    <div class="section_img">
        <img src="./images/member.jpg" alt="phpto"  width="100%" >
    </div>
    <div class="section_form">
<!--------  affichage de tableau de Error msg  ou de sucess message s'ils ne sont pas vides-->
        <?php
        if(!empty($ErrTab)){
        ?>
            <div class="Msg alert alert-danger" role="alert">

                        <strong>Attention <br></strong>
                        <?php
                        foreach( $ErrTab as $ErrMsg){
                            ?>
                            <span class="error"> <?php echo $ErrMsg;?><br></span>
                         <?php
                        }?>
            </div>
        <?php
        }
        if (!empty($SuccessMsg)){
        ?>
            <div class="Msg alert alert-success" role="alert">
                <strong>incription avec succès<br></strong>
                <span> <?php echo $SuccessMsg;?><br></span>
            </div>
            <?php
         }
         ?>
 <!--------  affichage de formulaire -->
        <div class="form-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <h1 class="h3 mb-3 font-weight-normal">Please sign up</h1>
                <!-- champ nom ------------------------------------------------------>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputNom" >Nom:</label>
                        <input type="text" id="inputNom" name="nom" value="<?php echo $nom;?>" class="form-control form-control-sm" placeholder="Nom"  >
                    </div>
                    <!-- champ prenom ----------------------------------------------------->
                    <div class="form-group col-md-6">
                        <label for="inputPrenom" >Prenom:</label>
                        <input type="text" id="inputPrenom" name="prenom" value="<?php echo $prenom;?>" class="form-control form-control-sm" placeholder="Prenom"  autofocus="">
                    </div>
                </div>
                <!-- champ email ------------------------------------------------------>
                <div class=" form-group ">
                    <label for="inputEmail" >adresse mail:</label>
                    <input type="email" id="inputEmail" name="email" value="<?php echo $email;?>" class="form-control form-control-sm" placeholder="Email"  autofocus="">
                </div>
                <!-- champ pwd --------------------------------------------------------->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputPassword" >mot de passe:</label>
                        <input type="password" id="inputPassword"  name="pwd" class="form-control form-control-sm" placeholder="mot de passe" >
                    </div>
                    <!-- champ pwd Confirmation -------------------------------------------->
                    <div class="form-group col-md-6">
                        <label for="inputPasswordConf" >confirmation mot de passe:</label>
                        <input type="password" id="inputPasswordConf" name="pwdConfirmation" class="form-control form-control-sm" placeholder="confirmer mot de passe" >
                    </div>
                </div>
                <!-- champ accept condition --------------------------------------------->
                <div class="form-group custom-control custom-checkbox">
                    <div class="form-check">
                        <input type="checkbox" name='rememberMe[]' class="custom-control-input" id="m" value="remember-me">
                        <label class="custom-control-label" for="m"><span style="color:black">J'accepte les</span> termes et conditions</label>
                    </div>
                </div>
                <!-- ------------------------------------------------------------------->
                <div class="form-group" style="display: flex; flex-direction: column; align-items: center ">
                    <button class="btn btn-md btn-success btn-block" type="submit">s'incrire</button>
                    <h6 style="margin: 10px 0 0;">OU</h6>
                    <span ">Vous avez deja un compte <a href="" style="color:#28a745;">connectez-vous</a></span>

                </div>
            </form>
        </div>

    </div>
</div>

<?php include_once 'footer.php'?>
