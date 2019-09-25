<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 26/07/2018
 * Time: 9:10 PM
 */

require_once ('../includes/initialize.php');

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

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
        redirect_to("profile.php");
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
            redirect_to("profile.php");

        }
    }

}

?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container" style="padding-top: 40px; padding-bottom: 40px;">

    <?php show_message($message) ?>

    <div class="jumbotron">

        <h2 class="my-2">Edit Profile</h2>

        <h4>Change Username</h4>

        <form action="edit_profile.php" method="post">

            <input type="text" class="form-control" name="username" value="<?php echo htmlentities($username); ?>" placeholder="Enter new username"/>
            <i style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></i>
            <br/>

            <button type="submit" class="btn btn-primary" name="submit_username">Change Username</button>
            <br/>

        </form>

        <hr/>


        <h4>Change Password</h4>

        <form action="edit_profile.php" method="post">

            <label>Old Password</label><br/>
            <input type="password" class="form-control" name="old_pass"/>
            <i style="color: red"><?php if (isset($errors['old password'])) echo $errors['old password']; ?></i>
            <br/>

            <label>New Password</label><br/>
            <input type="password" class="form-control" name="new_pass"/>
            <i style="color: red"><?php if (isset($errors['new password'])) echo $errors['new password']; ?></i>
            <br/>

            <button type="submit" class="btn btn-primary" name="submit_password">Change Password</button>
            <br/>

        </form>

    </div>

</div>


<?php include_layout_template("footer.php") ?>