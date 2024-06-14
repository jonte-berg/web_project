<?php

/*
Template Name: Custom resetPass
*/

/*
 * This File handles the page that allows you to send a password reset
 * note: the actual authentication and reset happend in authentication
 * note: the resetlink that is generated is a string with 10random characters + time()
 * if  current_time() - time() = >3600
 * that means an hour has expired and the link is invalid
 * */


echo'
<link rel="stylesheet" href="../../wp-content/themes/yootheme/style.css">';
echo'<script src="https://www.google.com/recaptcha/api.js"></script>';

get_header();
use PHPMailer\PHPMailer\PHPMailer;
?>

<body>
<div class="container" style="justify-content: center;max-height: 400px;">

    <div class="form-container sign-in-container " style="position: relative;margin-top: 25px;display: inline-block;">
        <form method="POST" action="">
            <h1>Återställ lösenord</h1>

            <span>Skriv in mailen du registrerade dig med så skickar vi ett mail med instruktioner.</span>
            <input type="email" required placeholder="Email" name="email"/>
            <div class="g-recaptcha" data-sitekey="6LfN2EkoAAAAABLVmmLBcXHl1weraFXSbt2qfZx4" style="margin-top: 10px;margin-bottom: 10px;">


            </div>
            <button type="submit" value="submit" name="reset_submit">Återställ</button>
            <p id="successText" style="color: green;visibility: hidden">Ett mail har nu skickats till den angivna mailen med instruktioner för hur du byter lösenord</p>
            <p id="errorText" style="color: red;visibility: hidden">Error: Den angivna emailen finns inte registrerad<br></p>

        </form>
    </div>





<?php
function verify_recaptcha($recaptcha_response)
{
    $secretKey = '6LfN2EkoAAAAABgho_hJuti1mxhIA2IkonUXoEfA';
    $recaptchaResponse = $_POST['g-recaptcha-response'];


    $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $secretKey,
        'response' => $recaptchaResponse,
    ];

    $options = [
        'http' => [
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($verifyUrl, false, $context);
    $result = json_decode($response);

    if (!$result->success)
        return false;
    else
        return true;

}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST["reset_submit"])) {

        if (!verify_recaptcha($_POST["g-recaptcha-response"])) {
            echo ' <script>
            document.getElementById("errorText").style.visibility="visible";
            document.getElementById("errorText").innerHTML = "Unable to verify captcha";
            </script>';
            exit;
        }
        else {

            $email = $_POST["email"];
            global $wpdb;
            $cfdb = apply_filters('cfdb7_database', $wpdb);
            $table_name = $cfdb->prefix . 'kult_users';
            $row = $cfdb->get_row("SELECT email from $table_name WHERE email = '$email'", ARRAY_N);

            if (empty($row)) {
                echo '<script> 
               
            
               
                
                document.getElementById("errorText").style.visibility="visible";
                </script>';


            }
            else {
                //length of random string before Timedata=10
                $numBytes = ceil(10 / 2);
                $randomBytes = random_bytes($numBytes);
                $randomString = bin2hex($randomBytes) . time();
                $data = array('reset_token' => $randomString);
                $result = $cfdb->update($table_name, $data, array('email' => $email));
                if ($result === false) {
                    echo "error";
                } else {
                    require ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
                    require ABSPATH . WPINC . '/PHPMailer/SMTP.php';
                    require ABSPATH . WPINC . '/PHPMailer/Exception.php';

                    $mail = new PHPMailer(true);
                    try {

                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'esq.barrel@gmail.com';
                        $mail->Password   = 'txka ifhq ypcz hxyo';
                        $mail->SMTPSecure= 'ssl';
                        $mail->Port       = 465; // or the port your SMTP server uses
                        $mail->CharSet="utf-8";
                        // Sender and recipient
                        $mail->setFrom('esq.barrel@gmail.com', 'Kulturskolan');
                        $to = $email;
                        $mail->addAddress($to, $to);

                        // Email content
                        $mail->isHTML(true);
                        $mail->Subject = 'Kulturskolan: Återställning av lösenord';
                        $link = '<p> För att återställa ditt lösenord så behöver du bara klicka på den här länken och följa instruktionerna: </p> <a href="' . home_url(). '/logga-in/authentication-php?resetlink=' . $randomString.  '"> här  </a>';
                        $mail->Body    = $link;



                        // Send the email
                        $mail->send();

                        echo '

        <script>
         var body = document.getElementsByClassName("uk-container");
            
            // Replace the content of the body with new HTML
            //body.innerHTML = "<h1>Account created successfully </h1><p>Check your email for activation link</p></div>";
            window.location.href = "'.home_url(). '/logga-in/authentication-php/";
        </script>';
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    echo 'Error sending email: ' . $mail->ErrorInfo;
                }
/*
                    $to = $email;
                    $subject = "Kulturskolan: Återställning av lösenord";

                    $link = ' <a href="' . home_url(). '/logga-in/authentication-php?resetlink=' . $randomString.  '"> här  </a>';
                    $message = "<html><body><p> För att återställa ditt lösenord så behöver du bara klicka på den här länken och följa instruktionerna: " . $link . "</p></body></html>";
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: Kulturskolan\r\n";


                    $mailSent = mail($to, $subject, $message, $headers);


                    if ($mailSent) {
                        echo '<script>
                                document.getElementById("successText").style.visibility="visible";
                              // window.location.href = "http://kulturskolan.info/jobb/";

                              </script>';
                    } else {
                        echo "there was an issue sending the confirmation email.";
                    }
*/
                }
            }

        }

    }




}






echo'
</div>
</body>';
get_footer();