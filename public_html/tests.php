<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 5:46 PM
 */

require_once ("../includes/initialize.php");

echo date_default_timezone_get();

echo "<br/><br/>";

echo "UTC: " . strftime("%H : %M : %S", strtotime('now'));

echo "<br/><br/>";

$date = 'now';

echo convert_timezone($date, date_default_timezone_get(), 'Africa/Lagos')->format("H : i : s");
echo "<br/><br/>";

echo convert_timezone_with_offset($date, 0, $tz_offset)->format("H : i : s");

echo "<br/><br/>";

?>


<?php
    echo "<br/><br/>";
    //echo timezone_name_from_abbr("", $_COOKIE["offset"], 0);
    echo timezone_name_from_abbr("WAT");


echo "<br/><br/>";

echo (true && true);

echo "<br/><br/>";

echo (int) "l2l.php";

echo "<br/><br/>";

echo "Mor: " . (bool) "0";
echo "<br/><br/>";

echo generateApiKey();

?>

