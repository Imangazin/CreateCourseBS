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

/**
 * Convert all applicable characters to HTML entities.
 * @param string $text The string being converted.
 * @return string The converted string.
 */
function html($text)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

//Check the LTI key is correct and role of the user in organization
if(($lti_auth['key'] == $toolKey) && ($roleId == "faculty-staff" || $roleId == "Super Administrator")){
    //recieve the site title from the user
    $siteName = trim($_POST['siteName']);
    //remove special chars from the offering code and restrict it to 50 chars
    $siteCode = str_replace(array('\\',':','*','?','"','<','>','|','\'','#',',','%','&'),'',substr($siteName, 0, 50));
    //default the course template to specific template 
    $siteTemplate = $config['project_site_id'];
    //recive a term
    $siteTerm = $_POST['siteTerm'];
    
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
        $orgUnitId = $createOffering['response']->Identifier;
        $postOfferingData = array("OrgUnitId"=>$orgUnitId,"UserId"=>$userId,"RoleId"=>109);
        $offerringEnroll = doValenceRequest('POST', '/d2l/api/lp/'. $config['LP_Version'] .'/enrollments/', $postOfferingData);
    }
    else {
        echo "Something went wrong while creating a course offering";
        return;
    }
    
    //if user selects a term
    if($siteTerm != "noterm"){
        //get OrgUnitId of the selected term
        $termProp = doValenceRequest('GET', '/d2l/api/lp/' . $config['LP_Version'] . '/orgstructure/?exactOrgUnitCode=' . $siteTerm);
        //add selected term (semester) as a parent to the new offering
        if($termProp['Code']==200){
            $postParent = $termProp['response']->Identifier;
            $addTerm = doValenceRequest('POST', '/d2l/api/lp/'. $config['LP_Version'] .'/orgstructure/'.$orgUnitId.'/parents/', $postParent);
        }
    }
        
    //Send back to js success code and new OrgUnitId    
    if ($offerringEnroll['Code']==200){
        $offeringName = html($siteName);
        $results = array("Code" => $offerringEnroll['Code'],
                         "OrgUnitId" => $orgUnitId,
                         "Name" => $offeringName);
        echo json_encode($results);
    }
    else{
        echo "Something went wrong while creating a course offering";
    }
}
else {
        echo "User has no permission to create a site";
} 
?>
