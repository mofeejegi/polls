<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 01/07/2018
 * Time: 6:39 PM
 */

require_once ('../../includes/initialize.php');

if ($session->is_logged_in()){
    redirect_to("./");
}

$username = "";
$password = "";

if (isset($_POST['submit'])){

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    //check db to see if username/password exist
    $found_user = User::authenticate($username, $password);

    //if no errors in validation
    if (empty($errors)) {

        if ($found_user) {
            $session->login($found_user);
            $session->message("Logged in successfully as ".$found_user->username);
            redirect_to("./");
        } else {
            $message = "Error: Username/Password incorrect";
        }
    }

}

?>

<?php include_layout_template("admin_header.php"); ?>


<h2>Login</h2>

<form action="login.php" method="post">

    <?php show_message($message) ?>

    <input type="text" name="username" value="<?php echo htmlentities($username); ?>" placeholder="username or email"/>
    <i style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></i>
    <br/><br/>

    <input type="password" name="password" placeholder="password"/>
    <i style="color: red"><?php if (isset($errors['password'])) echo $errors['password']; ?></i>
    <br/><br/>

    <input type="submit" name="submit" value="Login"/>

</form>



<?php include_layout_template('admin_footer.php'); ?>