<?php
/**
 * Created by PhpStorm.
 * User: VladimirSmirnov
 * Date: 11.03.14
 * Time: 20:41
 */

require_once("../models/data_management/dataSourceFactory.php");

abstract class BaseAlgorithm {
    /*
    const CURRENT_BRAND_ID = "current_brand_id";
    const DESIRED_BRAND_ID = "desired_brand_id";
    const CURRENT_BRAND_SIZE = "current_brand_size";
    const ITEM_ID = "item_id";
    const USER_ID = "user_id";
    const USER_GENDER = "user_gender";
    */

    protected $current_brand_id;
    protected $desired_brand_id;
    protected $current_brand_size;
    protected $item_id;
    protected $item_group_id;
    protected $user_gender;

    //protected $input_params = Array();
    protected $returned_size;
    protected $data_source;

    abstract protected function getSize();

    function __construct($current_brand_id, $current_brand_size, $desired_brand_id, $item_id, $item_group_id, $user_gender)
    {
        $this->current_brand_id = $current_brand_id;
        $this->current_brand_size = $current_brand_size;
        $this->desired_brand_id = $desired_brand_id;
        $this->item_id = $item_id;
        $this->item_group_id = $item_group_id;
        $this->user_gender = $user_gender;

        $data_source_factory = new DataSourceFactory();
        $this->data_source = $data_source_factory->getDataSource();

    }
} 