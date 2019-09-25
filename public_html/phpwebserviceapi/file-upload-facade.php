<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 10/10/2018
 * Time: 10:01 PM
 */
header("Content-Type: application/json");
echo json_encode(array_merge($_POST, $_FILES), JSON_PRETTY_PRINT);