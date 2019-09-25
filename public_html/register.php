<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 5:46 PM
 */

require_once ('../includes/initialize.php');

if ($session->is_logged_in()) {
    redirect_to("./");
}

$username = "";
$email = "";
$password = "";

if (isset($_POST['submit'])){

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //Make User, validation is done also
    $user = User::make($username, $email, $password, "user");

    if (empty($errors)) {

        //see if any unique fields for this new user have been taken
        $user->check_field_exists(["username" => $username, "email" => $email]);


        if (empty($errors)) {
            //save user to the db
            $user->save();
            $user->id = $database->inserted_id();
            $session->login($user);
            $session->message("Account created for ".$user->username);
            redirect_to("index.php");
        }


    }

}



?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container" style="padding-top: 40px; padding-bottom: 40px;">

    <?php show_message($message) ?>

    <div class="jumbotron">
        <h2>Register</h2>
        <br/>

        <form action="register.php" method="post">
            <input type="text" class="form-control" name="username" value="<?php echo htmlentities($username)?>" placeholder="username" required/>
            <i style="color: red"><?php if (isset($errors['username'])) echo $errors['username']; ?></i>
            <br/><br/>

            <input type="email" class="form-control" name="email" value="<?php echo htmlentities($email); ?>" placeholder="e-mail" required/>
            <i style="color: red"><?php if (isset($errors['email'])) echo $errors['email']; ?></i>
            <br/><br/>

            <input type="password" class="form-control" name="password" placeholder="password" required/>
            <i style="color: red"><?php if (isset($errors['password'])) echo $errors['password']; ?></i>
            <br/><br/>

            <button type="submit" class="btn btn-primary" name="submit">Create Profile</button><br/><br/>

            <small class="text-center"><p><a href="login.php">Already a user? Login</a></p></small>

        </form>


    </div>

</div>





<?php include_layout_template("footer.php"); ?>