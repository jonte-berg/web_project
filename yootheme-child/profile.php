
<?php

/*
Template Name: Profile
*/

/*
 * This File handles the profile page
 * note: this happens through an instance of my custom class being created
 */

if(!isset($_SESSION['user_id'])){
    header('Location: ' . home_url() . '/logga-in/');
    exit;
}
else if($_SESSION['admin']==='0'){
    $user_form_id=$_SESSION['user_id'];
}
else if($_SESSION['admin']==='1'){
    $user_form_id=$_GET['ufid'];
    require __DIR__ . '/custom_classes/CFDB7_Form_Details.php';
    $formDetails = new \custom_classes\CFDB7_Form_Details($user_form_id);
    exit;
}
else{
    header('Location: ' . home_url() . '/logga-in/');
    exit;
}


if (!defined( 'ABSPATH')) exit;


get_header();
require __DIR__ . '/custom_classes/CFDB7_Form_Details.php';
$formDetails = new \custom_classes\CFDB7_Form_Details($user_form_id);

echo '<div> <button style="background-color: white;border: 1px solid black;"><a href="/public/my-account/edit/">  Ändra profil</a> </button> </div>';


  

get_footer();