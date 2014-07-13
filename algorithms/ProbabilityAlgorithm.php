<?php

require_once("BaseAlgorithm.php");

class ProbabilityAlgorithm extends  BaseAlgorithm {

    private $returned_size_probability;

    function __construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender){
        parent::__construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender);

        $this->getSizeData();
    }

    function getSize(){
        return $this->returned_size;
    }

    function getSizeProbability(){
        return $this->returned_size_probability;
    }



    private function getSizeData(){

        //1. searching all users with user brand and size
        $usersWithCurrentBrand = $this->data_source->getUsersWithGivenBrandItemGroupAndItemSize($this->current_brand_id, $this->item_group_id, $this->current_brand_size, $this->user_gender);
        $usersWithCurrentBrand = array_unique($usersWithCurrentBrand);

        //2.searching all users with desired brand and item
        $usersWithDesiredBrand = $this->data_source->getUsersWithGivenBrandAndItemGroup($this->desired_brand_id, $this->item_group_id, $this->user_gender);
        $usersWithDesiredBrand = array_unique($usersWithDesiredBrand);

        //3. searching intersection btw $usersWithCurrentBrand and $usersWithDesiredBrand
        $intersection = array_intersect($usersWithCurrentBrand, $usersWithDesiredBrand);

        // if $intersection array not empty then we have a user or users which have both current and desired brand
        $sizes = Array();

        if (count($intersection) > 0){

            // getting all ID's
            $usersID = '';
            $array_count = count($intersection);
            $i = 0;
            foreach ($intersection as $userID) {
                $i++;
                // we don't need comma after last element
                if ($i == $array_count){
                    $usersID = $usersID.'\''.$userID.'\'';
                }
                else{
                    $usersID = $usersID.'\''.$userID.'\',';
                }
            }

            $totalNumberOfOrdersForCurrentBrand = max(1, $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsers($this->current_brand_id, $this->item_group_id, $usersID, $this->user_gender));

            $totalNumberOfOrdersForDesiredBrand = max(1, $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsers($this->desired_brand_id, $this->item_group_id, $usersID, $this->user_gender));

            $totalProbabilityForEachSize = Array();

            foreach ($intersection as $userID) {
                //finding out how many orders of current brand and selected item user did
                $numberOfOrdersOfCurrentBrand = max(1, $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsers($this->current_brand_id, $this->item_group_id, $userID, $this->user_gender));

                //finding out how many orders of desired brand and selected item user did
                $numberOfOrdersOfDesiredBrand = max(1, $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsers($this->desired_brand_id, $this->item_group_id, $userID, $this->user_gender));

                //finding out % of orders for given size of current brand and selected  item
                $probabilityOfGivenSizeForCurrentUser = 0;
                $numberOfOrdersOfCurrentBrandGroupedBySize = $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsersGroupedBySize($this->current_brand_id, $this->item_group_id, $userID, $this->user_gender);

                foreach ($numberOfOrdersOfCurrentBrandGroupedBySize as $row) {
                    //remember info only about size selected by user
                    if (strcasecmp($row["item_size"], $this->current_brand_size) == 0) {
                        $probabilityOfGivenSizeForCurrentUser = ($row["orders_count"]/$numberOfOrdersOfCurrentBrand) * ($numberOfOrdersOfCurrentBrand/$totalNumberOfOrdersForCurrentBrand);
                    }
                }

                //finding out % of orders for each size of desired brand and selected item
                $probabilitiesOfSizesOfDesiredBrand = Array();
                $numberOfOrdersOfDesiredBrandGroupedBySize = $this->data_source->getNumberOfOrdersForBrandItemGroupAndUsersGroupedBySize($this->desired_brand_id, $this->item_group_id, $userID, $this->user_gender);

                foreach ($numberOfOrdersOfDesiredBrandGroupedBySize as $row) {
                    $probabilitiesOfSizesOfDesiredBrand[$row["item_size"]] = $probabilityOfGivenSizeForCurrentUser * ($row["orders_count"]/$numberOfOrdersOfDesiredBrand)*($numberOfOrdersOfDesiredBrand/$totalNumberOfOrdersForDesiredBrand);
                }


                foreach ($probabilitiesOfSizesOfDesiredBrand as $size => $probability){

                    if (array_key_exists($size, $totalProbabilityForEachSize)){
                        $currentProbability = $totalProbabilityForEachSize[$size];
                        $currentProbability = $currentProbability + $probability;
                        $totalProbabilityForEachSize[$size] = $currentProbability;
                    }
                    else{
                        $totalProbabilityForEachSize[$size] = $probability;
                    }
                }

            }

            /*
            foreach ($totalProbabilityForEachSize as $size=>$probability){
                echo "for size $size probability  = $probability <br>";
            }
            */

            //searching maximum probability
            //TODO think about rounding values
            $maxProbability = max($totalProbabilityForEachSize);
            $commonProbability = array_sum($totalProbabilityForEachSize);

            foreach ($totalProbabilityForEachSize as $size => $probability){
                if ($probability == $maxProbability){
                    $sizes[] = $size;
                }
            }

            $this->returned_size = implode(";", $sizes);
            $this->returned_size_probability = ($commonProbability == 0 ? 0 : ($maxProbability/$commonProbability) * 100);

            unset($this->data_source);
        }
    }

} 