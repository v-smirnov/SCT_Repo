<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 19.03.14
 * Time: 23:01
 */
require_once("../models/config.php");
require_once("../models/class.database.php");
require_once("../algorithms/MappingTableAlgorithm.php");
require_once("../algorithms/StatsAlgorithm.php");
require_once("../algorithms/EthalonBrandAlgorithm.php");

define('USER_ID', 0);
define('GENDER', 1);
define('ITEM_NAME', 2);
define('DESIRED_BRAND', 3);
define('CURRENT_BRAND', 4);
define('USER_SIZE', 5);


set_time_limit(0);

$db = new Database();

$file_lines = file("test_data.txt");




echo "<table border = '1'>";
echo "<tr>";
echo "<td>user_id</td><td>gender</td><td>item</td><td>desired_brand</td><td>current_brand</td><td>user_size</td><td>math_size</td><td>db_size</td><td>ethalon_brand_size</td>";
echo "</tr>";

$start_time =  time();

foreach ($file_lines as $line) {

    $fields = explode(";",$line);

    $user_gender = trim($fields[GENDER]);
    $user_id = trim($fields[USER_ID]);

    $currentBrand = trim($fields[CURRENT_BRAND]);
    $currentBrandID = $db->DBGetFirstElem("SELECT id FROM brands WHERE name='$currentBrand' LIMIT 1");

    $desiredBrand = trim($fields[DESIRED_BRAND]);
    $desiredBrandID = $db->DBGetFirstElem("SELECT id FROM brands WHERE name='$desiredBrand' LIMIT 1");

    $clothesType = trim($fields[ITEM_NAME]);
    $clothesTypeID = $db->DBGetFirstElem("SELECT id FROM items_types WHERE name='$clothesType' LIMIT 1");
    $clothesGroupID = $db->DBGetFirstElem("SELECT group_id FROM items_types WHERE name='$clothesType' LIMIT 1");

    $userSize = trim($fields[USER_SIZE]);

    //$mapping_algorithm = new MappingTableAlgorithm($currentBrandID, $userSize, $desiredBrandID, $clothesTypeID, $clothesGroupID, $user_gender);
    $resultSizeBasedOnMathAlg = "-";//$mapping_algorithm->getSize();

    $stats_algorithm = new StatsAlgorithm($currentBrandID, $userSize, $desiredBrandID, $clothesTypeID, $clothesGroupID, $user_gender, $user_id);
    $resultSizeBasedOnDBAlg = $stats_algorithm->getSize();

    //$athalon_brand_algorithm = new EthalonBrandAlgorithm($currentBrandID, $userSize, $desiredBrandID, $clothesTypeID, $clothesGroupID, $user_gender, $user_id);
    $resultSizeBasedOnEthalonBrandAlg = "-";//$athalon_brand_algorithm->getSize();


    echo "<tr>";
    echo "<td>$user_id</td><td>$user_gender</td><td>$clothesType</td><td>$desiredBrand</td><td>$currentBrand</td><td>$userSize</td><td>$resultSizeBasedOnMathAlg</td><td>$resultSizeBasedOnDBAlg</td><td>$resultSizeBasedOnEthalonBrandAlg</td>";
    echo "</tr>";

}

echo "</table>";

echo time() - $start_time ;
echo " sec.";

$db->DbDisconnect();