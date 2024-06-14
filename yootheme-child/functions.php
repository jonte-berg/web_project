<?php
/**
 * Recommended way to include parent theme styles.
 * (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
 *
 */

add_action( 'wp_enqueue_scripts', 'yootheme_child_style' );
				function yootheme_child_style() {
					wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
					wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
				}

/**
 * Your custom functions go below here
 **/

session_start();


add_action( 'phpmailer_init', 'my_phpmailer_smtp' );
function my_phpmailer_smtp( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host = SMTP_server;
    $phpmailer->SMTPAuth = SMTP_AUTH;
    $phpmailer->Port = SMTP_PORT;
    $phpmailer->Username = SMTP_username;
    $phpmailer->Password = SMTP_password;
    $phpmailer->SMTPSecure = SMTP_SECURE;
    $phpmailer->From = SMTP_FROM;
    $phpmailer->FromName = SMTP_NAME;
}
//deletes the original when a new edited form is submitted
function check_form_submission(): void
{

    if(isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        global $wpdb;
        $cfdb = apply_filters('cfdb7_database', $wpdb);
        $table_name = $cfdb->prefix . 'db7_forms';
        $sql = "Select count(user_id) as 'number'   from $table_name where user_id= $user_id ";
        $result = $cfdb->get_results($sql,ARRAY_N);
        
        if($result[0][0] > 1){
          

            $sql = "Delete from $table_name WHERE form_id = (Select form_id FROM $table_name where user_id=$user_id ORDER by form_date LIMIT 1)";
              $result = $cfdb->get_results($sql,ARRAY_N);
              
              
        
        }
       
           

        }

    else
        echo "error, no session";
}
add_action('cfdb7_after_save_data', 'check_form_submission', 10);


//load custom css files
function enqueue_custom_css() {
    $page_id = url_to_postid(home_url());

    //add slug as key and CSS as value to apply custom CSS to slug/page
    $conditions = array(
        'registrera' => 'registerStyle.css',
        'edit' =>'registerStyle.css',
        'applikanter' => 'applicantStyle.css'
    );



    foreach ($conditions as $slug => $stylesheet) {
        if (strpos($_SERVER['REQUEST_URI'], '/' . $slug . '/') !== false) {
            wp_enqueue_style('custom-style-' . $slug, get_stylesheet_directory_uri() . '/custom_css/' . $stylesheet, array(), '1.0', 'all');
        }
    }
}


add_action('wp_enqueue_scripts', 'enqueue_custom_css');
//tvinga wpcf7 automatiska JS att inte ladda...så att edit form fungerar...kanske breakar form submits?
add_filter('wpcf7_load_js', '__return_false');
add_action('parse_request', 'request_handling');

//session handling (logout)
function request_handling ($wp) {


    //$target_url = 'localhost/jobb' .'/logga-ut/';
    $target_url = home_url() .'/logga-ut/';
    //echo 'target: '. $target_url;

    //$requested_url = 'https://www.kulturskolan.info'. $_SERVER['REQUEST_URI'];
    $requested_url = 'https://localhost' . $_SERVER['REQUEST_URI'];
    //echo 'requested: ' . $requested_url;
    if ($requested_url === $target_url) {

        if(isset($_SESSION['user_id'])) {
            //echo 'curent session id : '. session_id();
            //echo $_SESSION['user_id'];
            session_destroy();

            // echo 'post destroy: ' . session_id();
            header('Location: '.home_url());
            exit();
        }

        else {

            header('Location: ' . home_url());
            exit();
        }
    }

}


add_filter( 'wp_nav_menu_args', 'modify_nav_menu_args' );

//menu handling (depending on session)
function modify_nav_menu_args( $args = '' ) {


    if(isset($_SESSION['user_id']) && isset($_SESSION['admin'])) {

       // echo '|  user_id: '. $_SESSION['user_id'];
        // echo '| admin: '.$_SESSION['admin'].'  ';


        if($_SESSION['admin']==='1')
            $args['menu'] = 'Main-admin';
        else
            $args['menu'] = 'Main-in';
    }
    else
        $args['menu'] = 'Main';

    return $args;



}