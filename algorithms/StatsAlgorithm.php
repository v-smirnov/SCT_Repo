<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 13.03.14
 * Time: 22:50
 */

require_once("BaseAlgorithm.php");

class StatsAlgorithm extends BaseAlgorithm{
    private $user_id;
    private $matched_orders;
    private $non_matched_orders;

    function __construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender, $user_id)
    {
        parent::__construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender);

        $this->user_id = $user_id;
        $this->matched_orders = Array();
        $this->non_matched_orders = Array();

    }

    private function getUsersFromArray($users_array)
    {
        // getting all ID's
        $users = '';
        $last_user_index = count($users_array);
        $i = 0;
        foreach ($users_array as $user) {
            $i++;
            // we don't need comma after last element
            if ($i == $last_user_index){
                $users = $users.'\''.$user.'\'';
            }
            else{
                $users = $users.'\''.$user.'\',';
            }
        }
        return $users;
    }

    private function putSizesInCommonArray($all_sizes, $sizes)
    {
        foreach ($sizes as $size){
            if (array_key_exists($size['item_size'], $all_sizes)){
                $currentOrdersCount = $all_sizes[$size['item_size']];
                $currentOrdersCount = $currentOrdersCount + $size['orders_count'];
                $all_sizes[$size['item_size']] = $currentOrdersCount;
            }
            else{
                $all_sizes[$size['item_size']] = $size['orders_count'];
            }
        }

        return $all_sizes;
    }


    private function getSizesBasedOnDirectMatching($user_orders, $desired_brand_id){
        //get all users that bought desired brand
        $users_with_desired_brand = $this->data_source->getUsersWithGivenBrandAndItemGroup($desired_brand_id, $this->item_group_id, $this->user_gender);

        //if we have users that ordered desired brand
        if (count($users_with_desired_brand) != 0) {


            if (count($user_orders) != 0) {

                $all_sizes_for_desired_brand = Array();


                foreach ($user_orders as $order_data) {
                    //get all users with given brand and size
                    $users_with_given_brand = $this->data_source->getUsersWithGivenBrandItemGroupAndItemSize($order_data['brand_id'], $this->item_group_id, $order_data['item_size'], $this->user_gender);

                    //searching intersection between $users_with_given_brand and $users_with_desired_brand
                    $intersection = array_intersect($users_with_given_brand, $users_with_desired_brand);

                    //if we found users with both given and desired brand
                    //we defining the size for desired brand
                    if (count($intersection) > 0) {
                        $users = $this->getUsersFromArray($intersection);

                        $sizes_for_desired_brand = $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsersGroupedBySize($desired_brand_id, $this->item_group_id, $users, $this->user_gender);
                        $all_sizes_for_desired_brand = $this->putSizesInCommonArray($all_sizes_for_desired_brand, $sizes_for_desired_brand);

                        $this->matched_orders[] = Array("brand_id" => $order_data['brand_id'], "item_size" => $order_data['item_size']);
                    }
                    else{
                        if (count($this->non_matched_orders) < 5){
                            $this->non_matched_orders[] = Array("brand_id" => $order_data['brand_id'], "item_size" => $order_data['item_size']);
                        }
                    }
                }

                return $all_sizes_for_desired_brand;

            } else {
                return Array();
            }

        } else {
            return Array();
        }
    }

    private function getSizesBasedOnNonDirectMatching() {

        $matched_orders = $this->matched_orders;
        $non_matched_orders = $this->non_matched_orders;

        if ((count($matched_orders) == 0) or (count($non_matched_orders) == 0)) {
            return Array();
        }

        //looking for a matching percent with desired brand
        $matching_with_desired_brand = Array();

        $users_with_desired_brand = $this->data_source->getUsersWithGivenBrandAndItemGroup($this->desired_brand_id, $this->item_group_id, $this->user_gender);

        foreach ($matched_orders as $order_data) {

            $users_with_given_brand = $this->data_source->getUsersWithGivenBrandAndItemGroup($order_data['brand_id'], $this->item_group_id, $this->user_gender);
            $users_with_given_brand_and_size = $this->data_source->getUsersWithGivenBrandItemGroupAndItemSize($order_data['brand_id'], $this->item_group_id, $order_data['item_size'], $this->user_gender);

            $number_of_users_with_given_and_desired_brand = count(array_intersect($users_with_given_brand, $users_with_desired_brand));
            $number_of_users_with_given_brand_size_and_desired_brand = count(array_intersect($users_with_given_brand_and_size, $users_with_desired_brand));

            if ($number_of_users_with_given_and_desired_brand != 0) {
                $matching_with_desired_brand[$order_data['brand_id']] = $number_of_users_with_given_brand_size_and_desired_brand / $number_of_users_with_given_and_desired_brand;
            } else {
                $matching_with_desired_brand[$order_data['brand_id']] = 0;
            }
        }

        //looking for a matched brands that match best of all for non-matched brands
        $mapping_array = Array();

        foreach ($non_matched_orders as $nm_order_data){


            $matching_with_given_brand = Array();
            $users_with_desired_brand = $this->data_source->getUsersWithGivenBrandAndItemGroup($nm_order_data['brand_id'], $this->item_group_id, $this->user_gender);

            foreach ($matched_orders as $order_data) {

                $users_with_given_brand = $this->data_source->getUsersWithGivenBrandAndItemGroup($order_data['brand_id'], $this->item_group_id, $this->user_gender);
                $users_with_given_brand_and_size = $this->data_source->getUsersWithGivenBrandItemGroupAndItemSize($order_data['brand_id'], $this->item_group_id, $order_data['item_size'], $this->user_gender);

                $number_of_users_with_given_and_desired_brand = count(array_intersect($users_with_given_brand, $users_with_desired_brand));
                $number_of_users_with_given_brand_size_and_desired_brand = count(array_intersect($users_with_given_brand_and_size, $users_with_desired_brand));

                if ($number_of_users_with_given_and_desired_brand != 0) {
                    $matching_with_given_brand[$order_data['brand_id']] = ($number_of_users_with_given_brand_size_and_desired_brand / $number_of_users_with_given_and_desired_brand) * $matching_with_desired_brand[$order_data['brand_id']];
                } else {
                    $matching_with_given_brand[$order_data['brand_id']] = 0;
                }

            }

            $max_match_value = max($matching_with_given_brand);

            //taking first matching brand
            foreach ($matching_with_given_brand as $brand_id => $match_value){
                if ($match_value == $max_match_value){
                    $mapping_array[$nm_order_data['brand_id']] = Array("desired_brand_id" => $brand_id, "item_size" => $nm_order_data['item_size']);
                    break;
                }
            }

        }

        //converting non-matched brands to matched in compliance with the mapping array
        $user_orders = Array();

        foreach ($mapping_array as $brand_id => $data){


            $sizes = $this->getSizesBasedOnDirectMatching(Array(Array("brand_id" => $brand_id, "item_size" => $data['item_size'])), $data['desired_brand_id']);

            if (count($sizes) > 0 ){
                $maxOrdersCount = max($sizes);

                $item_size = null;
                foreach ($sizes as $size => $ordersCount) {
                    if ($ordersCount == $maxOrdersCount) {
                        $item_size = $size;
                        break;
                    }
                }
                if (!is_null($item_size)){

                    $user_orders[] = Array("brand_id" => $data['desired_brand_id'], "item_size" => $item_size);
                }
            }
        }

        return $this->getSizesBasedOnDirectMatching($user_orders, $this->desired_brand_id);

    }

    private function getSizeBasedOnUserOrders($user_orders, $desired_brand_id){


        $all_sizes_for_desired_brand = Array();

        $sizes_defined_in_compliance_with_direct_matching = $this->getSizesBasedOnDirectMatching($user_orders, $desired_brand_id);

        $sizes_defined_in_compliance_with_non_direct_matching = $this->getSizesBasedOnNonDirectMatching();

        foreach ($sizes_defined_in_compliance_with_direct_matching as $size => $orders_count){
            $all_sizes_for_desired_brand[$size] =  $orders_count;
        }


        foreach ($sizes_defined_in_compliance_with_non_direct_matching as $size => $orders_count){
            $all_sizes_for_desired_brand[$size] =  $orders_count;
            if (array_key_exists($size, $all_sizes_for_desired_brand)){
                $currentOrdersCount = $all_sizes_for_desired_brand[$size];
                $currentOrdersCount = $currentOrdersCount + $orders_count;
                $all_sizes_for_desired_brand[$size] = $currentOrdersCount;
            }
            else{
                $all_sizes_for_desired_brand[$size] = $orders_count;
            }
        }


        if (count($all_sizes_for_desired_brand) > 0) {
            $sizes = Array();
            $maxOrdersCount = max($all_sizes_for_desired_brand);

            foreach ($all_sizes_for_desired_brand as $size => $ordersCount) {
                if ($ordersCount == $maxOrdersCount) {
                    $sizes[] = $size;
                }
            }

            return implode(";", array_unique($sizes));
        }
        else{
            return "-";
        }

    }

    function getSize()
    {
        if (!is_null($this->user_id)){
            //checking if user already bought this brand and item
            $resultSize = $this->data_source->getSizeForGivenUserBrandAndItemGroup($this->user_id, $this->desired_brand_id, $this->item_group_id, $this->user_gender);

            //if he didn't then we try to find other users that bought brands like our user and desired brand
            if (is_null($resultSize)) {

                //get all orders made by user
                $user_orders = $this->data_source->getOrdersDataForGivenUser($this->user_id, $this->user_gender, $this->item_group_id);
                return $this->getSizeBasedOnUserOrders($user_orders, $this->desired_brand_id);

            } else {
                return $resultSize;
            }
        }
        //this is new user
        else{
            $user_orders = Array();

            $user_data = Array();
            $user_data['brand_id'] = $this->current_brand_id;
            $user_data['item_size'] = $this->current_brand_size;

            $user_orders[] = $user_data;
            return $this->getSizeBasedOnUserOrders($user_orders, $this->desired_brand_id);
        }
    }

} 