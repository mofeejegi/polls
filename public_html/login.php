<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 1:10 PM
 */

require_once ("../includes/initialize.php");


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

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container" style="padding-top: 40px; padding-bottom: 40px;">

    <?php show_message($message) ?>

    <div class="jumbotron">
        <h2>Login</h2>
        <br/>

        <form action="login.php" method="post">

            <input type="text" class="form-control" name="username" value="<?php echo htmlentities($username); ?>" placeholder="username or email" required autofocus/>
            <text style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></text>
            <br/><br/>

            <input type="password" class="form-control" name="password" placeholder="password" required/>
            <text style="color: red"><?php if (isset($errors['password'])) echo $errors['password']; ?></text>
            <br/><br/>

            <button type="submit" class="btn btn-primary" name="submit">Login</button><br/><br/>

            <small class="text-center"><p><a href="register.php">First time here? Register now</a></p></small>

        </form>
    </div>

</div>
<?php include_layout_template('footer.php'); ?>