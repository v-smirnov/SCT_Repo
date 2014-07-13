<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 11.03.14
 * Time: 21:57
 */


interface DataSource {

    //for probability algotithm
    function getUsersWithGivenBrandAndItemGroup($brand_id, $item_group_id, $gender);
    function getUsersWithGivenBrandItemGroupAndItemSize($brand_id, $item_group_id, $item_size, $gender);
    function getNumberOfOrdersForBrandItemGroupAndUsers($brand_id, $item_group_id, $users, $gender);
    function getNumberOfOrdersForBrandItemGroupAndUsersGroupedBySize($brand_id, $item_group_id, $users, $gender);

    //for mapping algorithm
    function getSizeFromMappingTableBasedOnDirectMatching($current_brand_id, $desired_brand_id, $item_group_id, $current_brand_size, $gender);
    function getRecordsWithCurrentBrandAndSize($current_brand_id, $item_group_id, $current_brand_size, $gender);
    function getRecordsWithDesiredBrand($desired_brand_id, $item_group_id, $gender);

    //for statistics algorithm
    function getSizeForGivenUserBrandAndItemGroup($user_id, $brand_id, $item_group_id, $gender);
    function getOrdersDataForGivenUser($user_id, $gender, $item_group_id);

    //for ethalon brand algorihm
    function getEthalonBrandForItemGroup($item_group_id, $gender);
} 