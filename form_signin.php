<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 17/08/2018
 * Time: 12:14
 */

session_start();
if (isset($_SESSION['member_id'])) {
    header ( 'Location: home.php');
    exit();
}
// define variables and set to empty values
$email = $pwd =$rememberMe="";
$emailErr = $pwdErr ="";
$ErrTab=[];
if (isset($_SESSION['member_id'])) {
    header ( 'Location: home.php');
    exit();
}
// when submitting check if the different fields are not empty and each field has a valid format.
// at the end creat an error msg for each field and push them in array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // validation champ email ------------------------------------------------------>
    if (empty($_POST["email"])) {
        $emailErr = "Veuillez saisir votre email";
        array_push($ErrTab,$emailErr) ;
    } else {
        $email = htmlspecialchars($_POST["email"]);
        // check if e-mail address is well-formed
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "le format de l'email est invalide";
        }
    }
    // validation champ pwd ------------------------------------------------------>
    if (empty($_POST["pwd"])) {
        $pwdErr= "Veuillez saisir votre mot de passe";
        array_push($ErrTab,$pwdErr) ;
    } else {
        $pwd = htmlspecialchars($_POST["pwd"]);
    }

    // validation champ checkbox remember me ------------------------------------------------------>
    if (!empty($_POST["rememberMe"])) {
        $rememberMe = $_POST["rememberMe"];
    }
    //*******if all fields are valid format Verif existance of the email and password is correct***************************************************************************
    if (empty($ErrTab)){// if all fields are valid communicate with BD and verify if the email already exist if not add the new user
        try {
            //0//instancier objet pdo (qui va nous epermettre de connecter et gérer ànotre bd )
            $cnxPDO  = new PDO('mysql:host=localhost;dbname=memberarea', 'root','', array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
            //1// verfif que l'email n'existe pas

            $reponse= $cnxPDO-> prepare("SELECT * FROM  membres WHERE email= ?");
            $reponse->execute(array($email));

            if ($reponse->rowCount() > 0){//1 si email existe
                // verif password
                $member= $reponse->fetch(PDO::FETCH_OBJ);

                $test = password_hash($pwd,PASSWORD_BCRYPT);
                //if (hash_equals($member->password, crypt($pwd, $member->password))){//2 pwd correcte
                if (password_verify($pwd,$member->password)){//2 pwd correcte
                //if ($pwd == $member->password){//2 pwd correcte
                    if ($member->isConfirmedEmail == 0 ) { // 3 cet email n'est pas activé
                        array_push($ErrTab,"Verifiez votre compte email");
                    } else { //3 cet email est activé
                            if (isset($_POST['rememberMe'])) {// if remember me checkbox is valid than set cookie
                                setcookie('email',$email,time()+60*60*24*30);
                            }
                             $_SESSION['member_id']= $member->id;
                             $_SESSION['member_nom']= $member->nom;
                             $_SESSION['member_prenom']= $member->prenom;
                             $_SESSION['member_email']= $member->email;
                             header ( 'Location: home.php?login=success');
                             exit();
                    }
                } else {//2pwd incorrect
                    array_push($ErrTab,"Mot de passe incorrect");
                }
            } else {//1 si email n'existe pas
                array_push($ErrTab,"Vous n'etes pas inscrit");
            }
            //

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}

?>
<?php include_once 'header.php'?>
<!--**************SIGN IN FORM**************************************************************************************-->

<div class="sections">
    <div class="section_img">
        <img src="./images/member.jpg" alt="phpto"  width="100%"  >
    </div>
    <div class="section_form">
        <?php
        if(!empty($ErrTab)){
            ?>
            <div class="alert alert-danger" style="font-size: 14px;" role="alert">

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

        ?>

        <div class="form-content">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
                <!-- champ email ------------------------------------------------------>
                <div class=" form-group ">
                    <label for="inputEmail" >adresse mail:</label>
                    <input type="email" id="inputEmail" value="<?php echo $email;?>" name="email" class="form-control form-control-md" placeholder="Email"  autofocus="">
                </div>
                <!-- champ pwd --------------------------------------------------------->

                <div class="form-group">
                    <label for="inputPassword" >mot de passe:</label>
                    <input type="password" id="inputPassword"  name="pwd" class="form-control form-control-md" placeholder="mot de passe" >
                </div>

                <!-- champ Remember me --------------------------------------------->
                <div class="form-row" style="justify-content: space-between">
                <div class="form-group custom-control custom-checkbox">
                    <div class="form-check">
                        <input type="checkbox" name='rememberMe' class="custom-control-input" id="m" value="1">
                        <label class="custom-control-label" for="m"><span style="color:black">Remember Me</span></span></label>
                    </div>

                </div>
                    <div><a href="http://localhost:63342/gomyphp/MEMBER_AREA/forgot_password.php" style="color:#28a745;">mot de passe oublié</a></div>
                </div>
                <!-- ------------------------------------------------------------------->
                <div class="form-group" style="display: flex; flex-direction: column; align-items: center ">
                    <button class="btn btn-md btn-success btn-block" type="submit">se connecter</button>
                    <h6 style="margin: 10px 0 0;">OU</h6>
                    <span ">Vous n'avez pas un compte <a href="" style="color:#28a745;">s'inscrire</a></span>

                </div>

            </form>
        </div>

    </div>
</div>
<?php include_once 'footer.php'?>
