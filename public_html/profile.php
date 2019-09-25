<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 23/07/2018
 * Time: 11:07 PM
 */

require_once ("../includes/initialize.php");


if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);


?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">

    <?php show_message($message); ?>

    <h2 class="mt-5 display-4 text-center">Welcome <?php echo $user->username ?></h2>

    <p class="text-center" style="font-size: x-large;">What would you like to do today?</p><br/>


    <div class="card-group">

        <div class="card bg-light justify-content-center profile-card">
            <a href="./" style="text-decoration: none">
                <div class="card-body text-primary text-center">

                    <i class="fa fa-6x fa-home"></i>
                    <h4>Home</h4>

                </div>

            </a>
        </div>

        <div class="card justify-content-center profile-card">
            <a href="create_poll.php" style="text-decoration: none">
                <div class="card-body text-success text-center">

                    <i class="fa fa-6x fa-plus"></i>
                    <h4>Create Poll</h4>

                </div>

            </a>
        </div>

        <div class="card bg-light justify-content-center profile-card">
            <a href="my_polls.php" style="text-decoration: none">
                <div class="card-body text-primary text-center">

                    <i class="fa fa-6x fa-chart-bar"></i>
                    <h4>View your polls</h4>

                </div>

            </a>
        </div>

        <div class="card justify-content-center profile-card">
            <a href="edit_profile.php" style="text-decoration: none">
                <div class="card-body text-success text-center">

                    <i class="fa fa-6x fa-user-edit"></i>
                    <h4>Edit Profile</h4>

                </div>

            </a>
        </div>

        <div class="card bg-light justify-content-center profile-card">
            <a href="logout.php" style="text-decoration: none">
                <div class="card-body text-primary text-center">

                    <i class="fa fa-6x fa-sign-out-alt"></i>
                    <h4>Logout</h4>

                </div>

            </a>
        </div>


    </div>

</div>


<?php include_layout_template("footer.php")  ?>
