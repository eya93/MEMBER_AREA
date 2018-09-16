<?php
/**
 * Created by PhpStorm.
 * User: Eya'sPC
 * Date: 20/08/2018
 * Time: 20:55
 */
require_once 'functions.php';
if (isset($_POST['submit'])) {
    session_start();
    session_destroy();
    setcookie('email',$email,time()-1);
    redirect('signin');
    exit();
}