<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 18/07/2018
 * Time: 6:04 PM
 */

require_once ('../../includes/initialize.php');

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

//if user is neither admin nor god user
if (! (($user->is_admin()) || ($user->is_god())) ){
    $session->message("Error: Authority not granted");
    redirect_to("../index.php");
}

?>

<?php include_layout_template("admin_header.php"); ?>

<?php show_message($message) ?>

<h2>Welcome <?php echo $user->username; ?></h2>
<p>What would you like to do?</p>

<ul>
    <li><a href="edit_admin.php">Edit Profile</a></li>
    <li><a href="list_admins.php">View All Admins</a></li>
    <li><a href="list_polls.php">View Polls</a></li>
    <li><a href="logout.php">Logout</a></li>
</ul>




<?php include_layout_template("admin_footer.php"); ?>