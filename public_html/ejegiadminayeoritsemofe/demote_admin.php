<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 18/07/2018
 * Time: 12:52 PM
 */

require_once ('../../includes/initialize.php');

if (!empty($_GET['id'])){

    if (!$session->is_logged_in()) { redirect_to("login.php"); }

    $current_user = User::find_by_id($session->user_id);

    //if user is not god user
    if (! $current_user->is_god() ){
        $session->message("Error: Authority not granted");
        redirect_to("../index.php");
    }

    //then perform admin removal
    $user = User::find_by_id($_GET['id']);

    if (!$user){
        $session->message("Error: Couldn't find user details");
        redirect_to("list_admins.php");
    }

    if ($user->is_admin()){
        $user->role = "user";
        $user->save();
        $session->message("User: {$user->username} successfully removed from admins");

    } else if ($user->is_god()){
         $session->message("Error: Cannot remove god");

    } else {
        $session->message("Error: User: {$user->username} is not an admin");
    }

    redirect_to("list_admins.php");

}

//remember to remove close the connection, usually done in footer layout
if (isset($database)) {$database->close_connection(); }