<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 18/07/2018
 * Time: 10:06 AM
 */

require_once ('../../includes/initialize.php');

$username = "";
$email = "";
$password = "";

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

//if user is neither admin nor god user
if (! (($user->is_admin()) || ($user->is_god())) ){
    $session->message("Error: Authority not granted");
    redirect_to("../index.php");
}

if (isset($_POST['submit'])){

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //Make User, validation is done also
    $user = User::make($username, $email, $password, "admin");

    if (empty($errors)) {

        //see if any unique fields for this new user have been taken
        $user->check_field_exists(["username" => $username, "email" => $email]);


        if (empty($errors)) {
            //save user to the db
            $user->save();
            $user->id = $database->inserted_id();
            $session->message("Admin account created for ".$user->username);
            redirect_to("list_admins.php");
        }


    }

}


?>




<?php include_layout_template("admin_header.php"); ?>

<a href="index.php">&laquo; Back</a>

<h2>Register Admin</h2>

<form action="new_admin.php" method="post">

    <input type="text" name="username" value="<?php echo htmlentities($username)?>" placeholder="username">
    <i style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></i>
    <br/><br/>

    <input type="email" name="email" value="<?php echo htmlentities($email); ?>" placeholder="e-mail"/>
    <i style="color: red"><?php if (isset($errors['email'])) echo $errors['email']; ?></i>
    <br/><br/>

    <input type="password" name="password" placeholder="password"/>
    <i style="color: red"><?php if (isset($errors['password'])) echo $errors['password']; ?></i>
    <br/><br/>

    <input type="submit" name="submit" value="Create Profile"/>


</form>





<?php include_layout_template("admin_footer.php"); ?>
