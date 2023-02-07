<?php
require_once("info.php");
require_once 'lib/D2LAppContextFactory.php';
require_once 'doValenceRequest.php';

//read LTI tool Key and OrgUnitID passed by session from main page index.php

session_start();
$toolKey = $_SESSION['toolKey'];
$roleId = $_SESSION['RoleId'];
$userName = $_SESSION['UserName'];
session_write_close();

//Check the key is correct / wrap everything with LTI credentials
if(($lti_auth['key'] == $toolKey) && ($roleId == "faculty-staff" || $roleId == "Super Administrator")){
    $siteName = htmlspecialchars($_POST['siteName']);
    $explorerSite = htmlspecialchars($_POST['explorerSite']);
    $siteTerm = htmlspecialchars($_POST['siteTerm']);
    
    //getting UserId
    $userData = doValenceRequest('GET', '/d2l/api/lp/' . $config['LP_Version'] . '/users/?userName=' . $userName);
    if ($userData['Code']==200){
        $userId = $userData['response']->UserId;
        //$postOfferingData = array("OrgUnitId"=>(int)$orgUnitId,"UserId"=>$userId,"RoleId"=>(int)$_POST[userrole]);
        //$offerringEnroll = doValenceRequest('POST', '/d2l/api/lp/'. $config['LP_Version'] .'/enrollments/', $postOfferingData);
        echo json_encode('UserId' . $userId);
    }
    else{
        echo json_encode("No such user");
        return;
    }
    
}
else {
        echo "User has no permission to create a site";
}
?>
