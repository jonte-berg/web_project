<?php
/*
Template Name: reset-success
*/


/*
 * This File handles the page that shows if a successfull password-reset has happened
 *
 * */

get_header();
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["Remail"])){


    $email = $_POST["Remail"];
    $password = $_POST["Rpassword"];
    $hashed_Password = password_hash($password,PASSWORD_DEFAULT);

    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix.'kult_users';
    $data = array('password' => $hashed_Password,'reset_token' => ' ');
    $where = array('email'=>$email);
    $result = $cfdb->update($table_name,$data,$where);

    if($result===false)
        exit("Error inserting into DB");
    else {
        echo '<div style="text-align: center">';
        echo '<h3> Your Password  on the account : ' . $email . '<br>has been updated <br> 
                    <br>You can now log in with the new password
                    <a href="../../logga-in" style="color: blue; text-decoration:underline;"> here</a> </h3>';
        echo '</div>';
    }
}
else{
    echo '<h1> Something went wrong..... </h1>';
}
get_footer();