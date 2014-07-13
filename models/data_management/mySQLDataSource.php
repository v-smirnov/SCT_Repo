<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 11.03.14
 * Time: 22:54
 */

require_once("dataSource.php");
require_once("../models/class.database.php");

class MySQLDataSource implements DataSource{

    private $db;

    function __construct()
    {
        $this->db = new Database();
    }

    //for probability algotithm

    function getUsersWithGivenBrandAndItemGroup($brand_id, $item_group_id, $gender)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT user_id FROM $tableName WHERE brand_id='$brand_id' AND item_group_id = '$item_group_id'";
        return $this->db->DbGetAll($queryText);
    }

    function getUsersWithGivenBrandItemGroupAndItemSize($brand_id, $item_group_id, $item_size, $gender)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT user_id FROM $tableName WHERE brand_id='$brand_id' AND item_group_id='$item_group_id' AND item_size='$item_size'";
        return $this->db->DbGetAll($queryText);
    }

    function getNumberOfOrdersForBrandItemGroupAndUsers($brand_id, $item_group_id, $users, $gender)
    {
        // we don_t use ' ' for $usersID because otherwise it will look like (''1,'2','3'') and that is wrong
        $tableName = $gender."_chart";
        $queryText = "SELECT COUNT(brand_id)
                      FROM $tableName
                      WHERE brand_id = '$brand_id' AND item_group_id = '$item_group_id' AND user_id IN ($users)
                      GROUP BY brand_id";
        return $this->db->DBGetFirstElem($queryText);
    }

    function getNumberOfOrdersForBrandItemGroupAndUsersGroupedBySize($brand_id, $item_group_id, $users, $gender)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT COUNT(item_size) AS orders_count, item_size
                      FROM $tableName
                      WHERE brand_id = '$brand_id' AND item_group_id = '$item_group_id' AND user_id IN ($users)
                      GROUP BY item_size ";
        $result_data = Array();
        if ($queryResult = $this->db->DbQuery($queryText)){
            while ($row = $queryResult->fetch_assoc()) {
                $result_data[] = $row;
            }
        }

        return $result_data;
    }

    //for mapping algorithm
    function getSizeFromMappingTableBasedOnDirectMatching($current_brand_id, $desired_brand_id, $item_group_id, $current_brand_size, $gender)
    {
        $tableName = $gender."_mapping_table";
        $queryText = "SELECT desired_brand_size FROM $tableName WHERE current_brand_id='$current_brand_id' AND desired_brand_id='$desired_brand_id' AND item_group_id='$item_group_id' AND current_brand_size='$current_brand_size'";
        return $this->db->DBGetFirstElem($queryText);
    }

    function getRecordsWithCurrentBrandAndSize($current_brand_id, $item_group_id, $current_brand_size, $gender)
    {
        $tableName = $gender."_mapping_table";
        $queryText = "SELECT * FROM $tableName WHERE current_brand_id='$current_brand_id'  AND item_group_id='$item_group_id' AND current_brand_size='$current_brand_size'";
        $queryResult = $this->db->DbQuery($queryText);

        $records = Array();
        if ($queryResult){
            while ($row = $queryResult->fetch_assoc()) {
                $records[] = $row;
            }
        }

        return $records;
    }

    function getRecordsWithDesiredBrand($desired_brand_id, $item_group_id, $gender)
    {
        $tableName = $gender."_mapping_table";
        $queryText = "SELECT * FROM $tableName WHERE  item_group_id='$item_group_id' AND desired_brand_id='$desired_brand_id'";
        $queryResult = $this->db->DbQuery($queryText);

        $records = Array();
        if ($queryResult){
            while ($row = $queryResult->fetch_assoc()) {
                $records[] = $row;
            }
        }

        return $records;
    }

    //for statistics algorithm
    function getSizeForGivenUserBrandAndItemGroup($user_id, $brand_id, $item_group_id, $gender)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT item_size FROM $tableName WHERE user_id = '$user_id' AND brand_id='$brand_id' AND item_group_id = '$item_group_id'";
        return $this->db->DBGetFirstElem($queryText);
    }

    function getOrdersDataForGivenUser($user_id, $gender, $item_group_id)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT brand_id, item_size FROM $tableName WHERE user_id = '$user_id' AND item_group_id = '$item_group_id'";
        $result_data = Array();
        if ($queryResult = $this->db->DbQuery($queryText)){
            while ($row = $queryResult->fetch_assoc()) {
                $result_data[] = $row;
            }
        }

        return $result_data;
    }

    //for ethalon brand algorihm
    function getEthalonBrandForItemGroup($item_group_id, $gender)
    {
        $tableName = $gender."_chart";
        $queryText = "SELECT COUNT(brand_id) AS orders_count, brand_id
                      FROM $tableName
                      WHERE item_group_id = '$item_group_id'
                      GROUP BY brand_id";

        $result_data = Array();
        if ($queryResult = $this->db->DbQuery($queryText)){
            while ($row = $queryResult->fetch_assoc()) {
                $result_data[$row['brand_id']] = $row['orders_count'];
            }
        }

        if (count($result_data) > 0){
            $max_orders_count = max($result_data);
            $result_brand_id = "";
            foreach ($result_data as $brand_id => $orders_count){

                if ($orders_count == $max_orders_count){
                    $result_brand_id = $brand_id;
                    break;
                }
            }

            return $result_brand_id;
        }
        else{
            return null;
        }
    }

    function __destruct()
    {
        $this->db->DbDisconnect();
    }


}