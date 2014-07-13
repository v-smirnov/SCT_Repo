<?php

require_once("../models/config.php");
require_once("../models/class.database.php");

$db = new Database();
$errorFound = false;

if (isset($_REQUEST['user_brand'])){
    $userBrand = $db->DBEscapeString($_REQUEST['user_brand']);
    $userBrandID = $db->DBGetFirstElem("SELECT id FROM brands WHERE name='$userBrand' LIMIT 1");
}
else{
    $errorFound = true;
}

if (isset($_REQUEST['item'])){
    $item = $db->DBEscapeString($_REQUEST['item']);
    $itemID = $db->DBGetFirstElem("SELECT id FROM items_types WHERE name='$item' LIMIT 1");
    $itemGroupID = $db->DBGetFirstElem("SELECT group_id FROM items_types WHERE name='$item' LIMIT 1");
}
else{
    $errorFound = true;
}
if (isset($_REQUEST['gender'])){
    $userGender = $db->DBEscapeString($_REQUEST['gender']);
}
else{
    $errorFound = true;
}

if ($errorFound){
    $sizes = Array();
    $sizes[] = "?";
    echo json_encode($sizes);
}
else{
    if ($userGender == "male"){
        $queryText = "SELECT item_size FROM male_chart where brand_id='$userBrandID' AND item_group_id='$itemGroupID' ";
    }
    elseif ($userGender == "female"){
        $queryText = "SELECT item_size FROM female_chart where brand_id='$userBrandID' AND item_group_id='$itemGroupID'";
    }

    $sizes = Array();
    if (isset($queryText)){
        if ($r1 = $db->DbQuery($queryText)){
            while ($row = $r1->fetch_object()) {
                foreach ($row as $k => $v) {
                    if (!in_array($v, $sizes)){
                        $sizes[]  = $v;
                    }
                }
            }
        }
    }

    if (count($sizes) == 0){
        $sizes[] = "?";
    }

    echo json_encode($sizes);
}

?>
