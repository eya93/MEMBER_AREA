<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 23/08/2018
 * Time: 09:42
 */?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MEMBER AREA</title>
    <link rel="stylesheet" type="text/css" href="assets/bootstrap.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="memberAreaCss.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-dark">
    <a class="navbar-brand brand" style="color:ghostwhite" href="#">Espace Membre</a>
    <?php
    if (isset($_SESSION['member_id'])) {
        ?>
        <div class="logout-btn"  >
            <form action="logout.php" method="post">
                <button class="btn btn-md btn-success btn-block"  type="submit" name="submit">Déconnexion</button>
            </form>
        </div>
        <?php
    }
    else {
        ?>
        <div class="collapse navbar-collapse" id="navbarNav" style="float: right">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item ml">
                    <a class="nav-link" style="color:#28a745" href="form_signup.php">S'inscrire</a>
                </li>
                <li class="nav-item ml">
                    <a class="nav-link" style="color:#28a745;" href="form_signin.php">Se connecter</a>
                </li>
            </ul>
        </div>
        <?php
    }
    ?>

</nav>