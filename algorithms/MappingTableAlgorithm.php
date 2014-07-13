<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 12.03.14
 * Time: 23:49
 */
require_once("BaseAlgorithm.php");

class MappingTableAlgorithm extends BaseAlgorithm{

    function __construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender)
    {
        parent::__construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender);

    }

    function getSize()
    {
        $resultSize = $this->data_source->getSizeFromMappingTableBasedOnDirectMatching($this->current_brand_id, $this->desired_brand_id, $this->item_group_id, $this->current_brand_size, $this->user_gender);

        if (is_null($resultSize)){
            $resultSize = "";

            //1. select all records with current brand and size
            $records = $this->data_source->getRecordsWithCurrentBrandAndSize($this->current_brand_id,  $this->item_group_id, $this->current_brand_size, $this->user_gender);

            if (count($records) > 0){
                //2. save all founded desired brands and sizes in array in the following format: desiredBrandID_size (for example 1_M or 2_S...)
                $founded_desired_brands = Array();
                foreach ($records as $row) {
                    $founded_desired_brands[] = $row['desired_brand_id'].'_'.$row['desired_brand_size'];
                }

                //3. get all records with given desired brand and save data about current brand and size in
                //the following format: currentBrandID_size (for example 1_M or 2_S...)
                $records = $this->data_source->getRecordsWithDesiredBrand($this->desired_brand_id, $this->item_group_id, $this->user_gender);
                if (count($records) > 0){

                    $founded_current_brands = Array();
                    foreach ($records as $row){
                        $index = $row['current_brand_id'].'_'.$row['current_brand_size'];
                        $founded_current_brands[$index] = Array($row['size_probability'], $row['desired_brand_size']);
                    }

                    // for every desired brand searching current brand intersection
                    $max_probability = 0;
                    for ($i = 0; $i < count($founded_desired_brands); $i++){
                        $current_key = $founded_desired_brands[$i];
                        if (array_key_exists($current_key, $founded_current_brands)){
                            $current_probability = $founded_current_brands[$current_key][0];
                            $current_size = $founded_current_brands[$current_key][1];
                            if ($current_probability > $max_probability) {
                                $resultSize = $current_size;
                                $max_probability = $current_probability;
                            }
                        }
                    }

                    return $resultSize;
                }
                else{
                   return "?";
                }
            }
            else{
                return "?";
            }
        }
        else{
           return $resultSize;
        }
    }
} 