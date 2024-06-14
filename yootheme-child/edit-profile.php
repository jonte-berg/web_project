<?php

/*
Template Name: edit-profile
*/

/*
 * This File handles the editing of submission (edit profile)
 *
 */


if (!isset($_SESSION['user_id'])) {
    header('Location: ' . home_url() . '/logga-in/');
    exit;
}
else if ($_SESSION['admin'] === '0') {
    $user_form_id = $_SESSION['user_id'];
}
else if ($_SESSION['admin'] === '1') {
    $user_form_id = $_GET['ufid'];
    require __DIR__ . '/custom_classes/CFDB7_Form_Details.php';
    $formDetails = new \custom_classes\CFDB7_Form_Details($user_form_id);
    exit;
}
else {
    header('Location: ' . home_url() . '/logga-in/');
    exit;
}


if (!defined('ABSPATH')) exit;

get_header();


global $wpdb;
$cfdb = apply_filters('cfdb7_database', $wpdb);
$table_name = $cfdb->prefix . 'db7_forms';
$form_post_id = 1483; // Replace with the actual form post ID

$query = "SELECT form_value FROM $table_name WHERE form_post_id = $form_post_id AND user_id = $user_form_id LIMIT 1";

$result = $cfdb->get_row($query, OBJECT);

if($result) {
    $test = (array)unserialize($result->form_value);

    echo do_shortcode('[contact-form-7 id="a71d292" title="multi"]');






?>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Your JavaScript code here

            const form = document.getElementById("wpcf7-f1483-o1");
            <?php foreach ($test as $key => $value):
            if (is_array($value)) {
                $encodedValue = json_encode($value[0]);
            } else {
                $encodedValue = json_encode($value);
            }?>


            const <?php echo str_replace('-', '_', $key); ?>Inputs = form.querySelectorAll('input[name="<?php echo $key; ?>[]"]');

            if (<?php echo str_replace('-', '_', $key); ?>Inputs.length > 0) {
                // For checkboxes, set matching checkboxes as checked
                <?php echo str_replace('-', '_', $key); ?>Inputs.forEach((checkbox) => {
                    if (checkbox.value === <?php echo $encodedValue; ?>) {
                        checkbox.checked = true;
                    }
                });
            } else {
                // For regular inputs, set the placeholder
                const <?php echo str_replace('-', '_', $key); ?>Input = form.querySelector('input[name="<?php echo $key; ?>"]');
                if (<?php echo str_replace('-', '_', $key); ?>Input) {
                    <?php echo str_replace('-', '_', $key); ?>Input.value = <?php echo json_encode($value); ?>;
                }
            }
            <?php endforeach; ?>
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const accordion = document.getElementsByClassName('label');

            for (i = 0; i < accordion.length; i++) {
                accordion[i].addEventListener('click', function () {
                    console.log("Clicked!");
                    const cont = document.getElementsByClassName('content');
                    this.parentElement.classList.toggle('active');
                });
            }
        });
    </script>

<?php
}

get_footer();