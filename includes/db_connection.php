<?php
ob_start();
//date_default_timezone_set('Asia/Kolkata');
date_default_timezone_set('Europe/Dublin');
$present=date('Y-m-d H:i');
function currentDateTime(){
  $present=date('Y-m-d H:i');
}

// echo $present;

function getFilterUrl($paramName, $paramValue) {
    $params = $_GET;
    if ($paramValue === '' || $paramValue === null) {
        unset($params[$paramName]);
    } else {
        $params[$paramName] = $paramValue;
    }
    if ($paramName !== 'page') {
        $params['page'] = 1;
    }
    $pageName = basename($_SERVER['PHP_SELF']);
    return $pageName . '?' . http_build_query($params);
}

//define("PRIMARY_CONTACT",1);
//define("SECONDARY_CONTACT",2);
//define('RAZORPAY_KEY_ID', 'MGTNlydR6fHxdfZtQIWsllVG'); //test payment api
//define('RAZORPAY_KEY_ID', 'rzp_live_TBgUtLpfXVBOI0'); // live payment api


$statusarray=array("ACTIVE"=>"Active","DRAFT"=>"Draft","SOLDOUT"=>"SoldOut");
$productlabelarray=array("NEW"=>"New");
$contactarray=array("PRIMARY"=>"PRIMARY","SECONDARY"=>"SECONDARY");

//local server
$servername = "localhost";
$username = "root";
$password ="";
$dbname = "db_dabamagic";

//production server

/*$servername = "Localhost";
$username = "n754b65_root";
$password ="root@edendesigns.in";
$dbname = "n754b65_db_donswebadmin"; */


// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
  die("Connection failed: DB Server not responding" );
}


?>