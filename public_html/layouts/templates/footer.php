<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 24/03/2018
 * Time: 11:40 AM
 */
?>

    <br/><br/>
    </main>
    <footer class="footer bg-dark">
        <div class="container">
            <span id="footer" class="text-white">Copyright (c) <?php echo date("Y", time()); ?>, Mofe Ejegi</span>
        </div>
    </footer>
    </body>
</html>
<?php if (isset($database)) { $database->close_connection(); }?>