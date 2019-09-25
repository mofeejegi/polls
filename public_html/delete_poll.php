<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 21/07/2018
 * Time: 10:25 AM
 */

require_once ('../includes/initialize.php');

if (!$session->is_logged_in()) {redirect_to("login.php");}

$user = User::find_by_id($session->user_id);

if (empty($_GET['poll_id'])){
    $session->message("Error: Could not find Poll to delete");
    redirect_to("./");
}

$poll = Poll::find_by_id($_GET['poll_id']);

if ($poll){
    //First confirm if it's the owner performing the delete
    if ($user->id != $poll->user_id){
        $session->message("Error: Authority not granted to delete this poll");
        redirect_to("./");
    }

    $database->begin_transaction();
    $is_success = true;

    foreach ($poll->get_poll_options() as $option){
        $is_success = $option->delete_associated_votes() && $is_success;
        $is_success = $option->delete() && $is_success;
    }

    $is_success = $poll->delete() && $is_success;

    if ($is_success) {
        $database->commit();
        $session->message("Successfully deleted Poll: {$poll->title}");
        redirect_to("my_polls.php");
    } else {
        $database->rollback();
        $session->message("Error: Failed to delete Poll: {$poll->title}");
        //redirect_to("my_polls.php");
        redirect_to("view_poll.php?poll_id={$poll->id}");
    }

} else {
    $session->message("Error: Could not find Poll to delete");
    redirect_to("my_polls.php");
}



//remember to remove close the connection, usually done in footer layout
if (isset($database)) {$database->close_connection(); }