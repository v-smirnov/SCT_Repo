<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 27.02.14
 * Time: 22:13
 */

require_once("../models/config.php");
require_once("../models/class.database.php");

$db = new Database();

echo "<b>Man's mapping table</b>";

$queryText = "SELECT m.item_id, m.current_brand_size, m.desired_brand_size, m.size_probability, cb.name AS cb_name, db.name AS db_name
              FROM male_mapping_table m INNER JOIN brands cb
              ON m.current_brand_id = cb.id
              INNER JOIN brands db
              ON m.desired_brand_id = db.id";

if ($queryResult = $db->DbQuery($queryText)){
    echo "<table border = '1'>";
    echo "<td>item_id</td><td>current_brand</td><td>current_brand_size</td><td>desired_brand</td><td>desired_brand_size</td><td>size_probability</td>";
    while ($row = $queryResult->fetch_assoc()) {

        $c_brand_id = $row['cb_name'];
        $c_brand_size = $row['current_brand_size'];
        $d_brand_id = $row['db_name'];
        $d_brand_size = $row['desired_brand_size'];
        $item_id = $row['item_id'];
        $size_probability = $row['size_probability'];
        echo "<tr>";
        echo "<td>$item_id</td><td>$c_brand_id</td><td>$c_brand_size</td><td>$d_brand_id</td><td>$d_brand_size</td><td>$size_probability</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<b>Man's table</b>";

$queryText = "SELECT m.item_id, m.item_size, m.user_id, m.brand_id, b.name AS b_name, ct.name AS c_name
              FROM male_chart m LEFT JOIN brands b
              ON m.brand_id = b.id
              LEFT JOIN items_types ct
              ON m.item_id = ct.id";

if ($queryResult = $db->DbQuery($queryText)){
    echo "<table border = '1'>";
    echo "<td>user id</td><td>clothes</td><td>brand</td><td>size</td>";
    while ($row = $queryResult->fetch_assoc()) {

        $user_id = $row['user_id'];
        $clothes = $row['c_name'];
        $brand = $row['b_name'];
        $size = $row['item_size'];
        echo "<tr>";
        echo "<td>$user_id</td><td>$clothes</td><td>$brand</td><td>$size</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$db->DbDisconnect();