<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 12:41 AM
 */

require_once ('../../includes/initialize.php');

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

//if user is neither admin nor god user
if (! (($user->is_admin()) || ($user->is_god())) ){
    $session->message("Error: Authority not granted");
    redirect_to("../index.php");
}

$username = $user->username;


if (isset($_POST['submit_username'])){
    $new_username = trim($_POST['username']);
    $user->username = $new_username;

    validate_presences(["username"=>$new_username]);

    //Check Availability
    if (empty($errors)) {

        if (strtolower($username) !== strtolower($new_username)) {
            $user->check_field_exists(["username"=>$new_username]);
        }
    }


    if (empty($errors)){
        $user->save();
        $session->message("Successfully Updated Account");
        redirect_to("edit_admin.php");
    }

    $username = $new_username; //let the displayed username always be the last inputted one

} else if (isset($_POST['submit_password'])){
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];


    validate_presences(["old password"=>$old_pass, "new password"=>$new_pass], false);
    if (empty($errors)){
        validate_min_lengths(["old password"=>$old_pass, "new password"=>$new_pass], 8);
    }

    //Validate old password correctness
    if (empty($errors)){
        if (!password_verify($old_pass, $user->password)){
            $message = "Error: Password Incorrect";
        } else {
            $user->password = password_hash($new_pass, PASSWORD_DEFAULT);
            $user->save();
            $session->message("Password Successfully Updated");
            redirect_to("edit_admin.php");

        }
    }

}

?>

<?php include_layout_template("admin_header.php") ?>

    <a href="index.php">&laquo; Back</a>

    <h2>Edit Profile</h2>

    <?php show_message($message) ?>
    <h3>Change Username</h3>

    <form action="edit_admin.php" method="post">

        <input type="text" name="username" value="<?php echo htmlentities($username); ?>" placeholder="Enter new username"/>
        <i style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></i>
        <br/><br/>

        <input type="submit" name="submit_username" value="Change Username"/>
        <br/><br/>

    </form>

    <hr/>


    <h3>Change Password</h3>

    <form action="edit_admin.php" method="post">

        <label>Old Password</label><br/>
        <input type="password" name="old_pass"/>
        <i style="color: red"><?php if (isset($errors['old password'])) echo $errors['old password']; ?></i>
        <br/><br/>

        <label>New Password</label><br/>
        <input type="password" name="new_pass"/>
        <i style="color: red"><?php if (isset($errors['new password'])) echo $errors['new password']; ?></i>
        <br/><br/>

        <input type="submit" name="submit_password" value="Change Password"/>
        <br/><br/>

    </form>


<?php include_layout_template("admin_footer.php") ?>