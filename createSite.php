<?php
require_once("info.php");
require_once 'lib/D2LAppContextFactory.php';
require_once 'doValenceRequest.php';

//read LTI tool Key, Role and Username passed by session from main page index.php

session_start();
$toolKey = $_SESSION['toolKey'];
$roleId = $_SESSION['RoleId'];
$userName = $_SESSION['UserName'];
session_write_close();

//Check the LTI key is correct and role of the user in organization
if(($lti_auth['key'] == $toolKey) && ($roleId == "faculty-staff" || $roleId == "Super Administrator")){
    //recieve the site title from the user
    $siteName = htmlspecialchars($_POST['siteName']);
    //remove special chars from the offering code
    $siteCode = str_replace(array('\\',':','*','?','"','<','>','|','\'','#',',','%','&'),'',$siteName);
    //default the course template to specific template 
    $siteTemplate = config['project_site_id'];
    //change template
    if($_POST['explorerSite'] == 'yes') 
        $siteTemplate = config['explorer_site_id'];
    //recive a term
    $siteTerm = htmlspecialchars($_POST['siteTerm']);
    
    //course offering properties, see https://docs.valence.desire2learn.com/res/course.html#Course.CreateCourseOffering for more deatils
    $courseParameters = array("Name"             => $siteName,
                              "Code"             => $siteCode,
                              "Path"             => '',
                              "CourseTemplateId" => $siteTemplate,
                              "SemesterId"       => null,
                              "StartDate"        => null,
                              "EndDate"          => null,
                              "LocaleId"         => null,
                              "ForceLocale"      => false,
                              "ShowAddressBook"  => false,
                              "Description"      => array("Content"=>$siteName, "Type"=>"Html"),
                              "CanSelfRegister"  => null);
    
    //create course offering    
    $createOffering = doValenceRequest('POST','/d2l/api/lp/' . $config['LP_Version'] . '/courses/', $courseParameters);
    
    //if course offering created successfully, then add user to the site as Instructor
    if ($createOffering['Code']==200){
        $userData = doValenceRequest('GET', '/d2l/api/lp/' . $config['LP_Version'] . '/users/?userName=' . $userName);
        $userId = $userData['response']->UserId;
        $postOfferingData = array("OrgUnitId"=>$createOffering['response']->Identifier,"UserId"=>$userId,"RoleId"=>109);
        $offerringEnroll = doValenceRequest('POST', '/d2l/api/lp/'. $config['LP_Version'] .'/enrollments/', $postOfferingData);
    }
    else {
        echo "Something went wrong while creating a course offering";
        return;
    }
    
    //Send back to js success code and new OrgUnitId    
    if ($offerringEnroll['Code']==200){
        $results = array("Code" => $offerringEnroll['Code'],
                         "OrgUnitId" => $offerringEnroll['response']->Identifier);
        echo json_encode($results);
    }
    else{
        echo "Something went wrong while creating a course offering"
    }
}
else {
        echo "User has no permission to create a site";
}
?>
