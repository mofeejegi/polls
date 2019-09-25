<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 1:12 PM
 */

require_once ("../includes/initialize.php");

$session->logout();
$session->message("You successfully logged out");
redirect_to("login.php");