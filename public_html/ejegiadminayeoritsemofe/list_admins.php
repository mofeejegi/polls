<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 01/07/2018
 * Time: 6:33 PM
 */

require_once ('../../includes/initialize.php');

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

//if user is neither admin nor god user
if (! (($user->is_admin()) || ($user->is_god())) ){
    $session->message("Error: Authority not granted");
    redirect_to("../index.php");
}

$admins = User::find_all_admins();

?>

<?php include_layout_template("admin_header.php"); ?>

<a href="index.php">&laquo; Back</a>

<h2>Admins (<?php echo $user->username; ?>)</h2>
<?php show_message($message) ?>

<p><a href="new_admin.php">Add New Admin</a></p>

<table style="width: 100%;">

    <tr>
        <th>Username</th>
        <th>E-Mail</th>
        <th>&nbsp;</th>
    </tr>

    <?php

    foreach ($admins as $admin) {

        echo "<tr>";
        echo "<td>". $admin->username ."</td>";
        echo "<td>". $admin->email ."</td>";
        if ($user->is_god())
            echo "<td><a href='demote_admin.php?id={$admin->id}'>Demote</a></td>";
        echo "</tr>";

    }
    ?>



</table>




<?php include_layout_template('admin_footer.php'); ?>
