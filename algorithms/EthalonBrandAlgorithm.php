<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 30.03.14
 * Time: 16:51
 */

require_once("StatsAlgorithm.php");


class EthalonBrandAlgorithm extends BaseAlgorithm{

    private $user_id;


    function __construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender, $user_id)
    {
        parent::__construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender);

        $this->user_id = $user_id;


    }


    function getSize()
    {

        $ethalon_brand_id = $this->data_source->getEthalonBrandForItemGroup($this->item_group_id, $this->user_gender);

        if (is_null($ethalon_brand_id)){
            return "couldn't find ethalon brand";
        }
        else{

            //1. convert current brand to ethalon brand
            $stats_algorithm = new StatsAlgorithm($this->current_brand_id, $this->current_brand_size, $ethalon_brand_id, $this->item_id, $this->item_group_id, $this->user_gender, $this->user_id);
            $size_for_ethalon_brand = $stats_algorithm->getSize();

            //2. convert ethalon brand to desired brand
            $stats_algorithm = new StatsAlgorithm($ethalon_brand_id, $size_for_ethalon_brand, $this->desired_brand_id, $this->item_id, $this->item_group_id, $this->user_gender, $this->user_id);
            $result_size = $stats_algorithm->getSize();

            return $result_size;
        }
    }

} 