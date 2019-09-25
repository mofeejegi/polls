<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 24/03/2018
 * Time: 11:39 AM
 */
?>


    </div>
    <div id="footer">Copyright <?php echo date("Y", time()); ?>, Mofe Ejegi</div>
    </body>
</html>
<?php if (isset($database)) { $database->close_connection(); }?>