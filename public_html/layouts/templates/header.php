<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 24/03/2018
 * Time: 11:40 AM
 */


?>


<html>
    <head>
        <title>Polls</title>
        <link href="stylesheets/bootstrap.css" media="all" rel="stylesheet" type="text/css" />
        <link href="stylesheets/all.css" rel="stylesheet" type="text/css" />
        <link href="stylesheets/sticky-footer.css" media="all" rel="stylesheet" type="text/css" />
        <link href="stylesheets/main.css" media="all" rel="stylesheet" type="text/css" />

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    </head>

    <body>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="js/bootstrap.js"></script>
    <script type='text/javascript'>
        // jQuery plugin to prevent double submission of forms
        window.onbeforeunload = function (ev) {
          $('button[type=submit]').attr("disabled", "disabled");
        };
    </script>
    <script type='text/javascript'>
        var offset = -new Date().getTimezoneOffset();
        document.cookie = 'tz_offset=' + offset + '; expires=Mon, 1 Jan 2024 12:00:00 UTC; path=/';
    </script>

    <nav class="navbar navbar-expand-md bg-success navbar-dark border-bottom border-success shadow-sm fixed-top">

        <a class="navbar-brand" href="./">Polls</a>

        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar links -->
        <div class="collapse navbar-collapse" id="collapsibleNavbar">
            <!-- Links -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbardrop" data-toggle="dropdown">
                        Download app
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item" href="#"><i class="fab fa-google-play text-success"></i> Android App</a>
                        <a class="dropdown-item" href="#"><i class="fab fa-app-store-ios text-primary"></i> iOS App</a>
                    </div>
                </li>

            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <form class="form-inline my-2 my-md-0 mr-sm-2" action="./list_polls.php" method="get">
                        <input class="form-control mr-sm-2" type="text" name="q" placeholder="Enter Poll Here">
                        <button class="btn btn-primary my-2 my-sm-0" type="submit">Find Poll</button>
                    </form>
                </li>

                <li class="nav-item dropdown">
                    <?php
                    if ($session->is_logged_in()) {
                        echo "<a class='nav-link dropdown-toggle text-white my-0' href='#' data-toggle='dropdown'>" . User::find_by_id($session->user_id)->username . "</a>";
                        echo "<div class='dropdown-menu'>";
                            echo "<a class='dropdown-item' href='profile.php'>Profile</a>";
                            echo "<a class='dropdown-item' href='logout.php'>Sign out</a>";
                        echo "</div>";

                    } else {
                        echo "<a class='btn btn-primary my-0' href='register.php'>Sign Up</a> &nbsp;";
                        echo "<a class='btn btn-primary my-0' href='login.php'>Login</a>";
                    }
                    ?>
                </li>
            </ul>


        </div>

    </nav>
    <main id="main" role="main">
