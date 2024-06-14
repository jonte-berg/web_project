<?php

/*
Template Name: Custom login/register
*/

/*
 * This File handles the login of accounts
 * This File also handles registration of accounts
 *
 * */

//cant login if already logged in
if (isset($_SESSION['user_id']))
    header('Location: ' . home_url());

get_header();

use PHPMailer\PHPMailer\PHPMailer;


echo'<script src="https://www.google.com/recaptcha/api.js"></script>';



?>

    <body>


    <div class="container" id="container">


        <div class="form-container sign-up-container">

            <form method="POST" action="" id="signup_form">
                <h1 class="mobileheading">Skapa konto</h1>

                <input type="text" required placeholder="Name" name="Rname" id="Rname" />
                <input type="email"  required placeholder="Email" name="Remail" id="Remail"/>

                <input type="password" id="Rpassword" required  placeholder="Password"  name="Rpassword"/>

                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm Password" >

                <div class="g-recaptcha" data-sitekey="6LfN2EkoAAAAABLVmmLBcXHl1weraFXSbt2qfZx4" >


                </div>

                <button type="submit" value="submit" name="signup_submit"> Sign Up</button>
                <a href="#" id="mobile_button2" class="btn-flip" data-back="Här" data-front="Logga in"></a>

                <p id="errorText" style="color: red;visibility: hidden">Error: Den angivna emailen är redan registrerad!<br> Försök med en unik email</p>




            </form>
        </div>
        <div class="form-container sign-in-container">
            <form method="POST" action="">
                <h1 class="mobileheading">Logga In</h1>

                <span> Använd din email som du registrerade dig med!</span>
                <input type="email" required placeholder="E-post" autocomplete="email" name="username" id="username"/>
                <input type="password" required placeholder="Lösenord" name="password"/>

                <a href="<?php echo home_url(). '/logga-in/reset-password';?>" >Glömt lösenord?</a>

                <button type="submit" value="submit" name="login_submit">Logga In</button>




                <a href="#" id="mobile_button" class="btn-flip" data-back="Här" data-front="Registrera"></a>

            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">

                    <h1 style="color: white;  font-size: 48px;">Vill du logga in?</h1>
                    <p style="color: white;font-size: 18px;">Du kan logga in snabbt och enkelt här</p>

                    <button class="ghost" id="signIn">Logga in</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1 style="color: white;  font-size: 48px;">Inget konto?</h1>
                    <p style="color: white;font-size: 18px;">Du kan skapa ett konto hos oss för att sedan göra en ansökan</p>

                    <button class="ghost" id="signUp">Registrera</button>

                </div>


            </div>
        </div>
    </div>
    </body>



    <script>




        function validatePassword() {
            var password = document.getElementById("Rpassword").value;
            var confirmPassword = document.getElementById("confirmPassword").value;

            // Check if the passwords match
            if (password !== confirmPassword) {
                document.getElementById("errorText").style.visibility="visible";
                document.getElementById("errorText").innerHTML = "Passwords do not match.";
                return false;
            }

            // Check password length and strength
            if (password.length < 8) {
                document.getElementById("errorText").style.visibility="visible";
                document.getElementById("errorText").innerHTML = "Password should be at least 8 characters long.";
                return false;
            }


            return true;
        }


        document.getElementById("signup_form").onsubmit = function () {
            return validatePassword();
        };





        const signUpButton = document.getElementById("signUp");
        const signInButton = document.getElementById("signIn");
        const container = document.getElementById("container");
        const signInmobileButton = document.getElementById("mobile_button");
        const signUpmobileButton = document.getElementById("mobile_button2");

        signInmobileButton.addEventListener("click", () => {

            container.classList.add("right-panel-active");
        });
        signUpmobileButton.addEventListener("click", () => {

            container.classList.remove("right-panel-active");
        });
        signUpButton.addEventListener("click", () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener("click", () => {
            container.classList.remove("right-panel-active");
        });
    </script>


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

    if (isset($_POST["signup_submit"])) {

        if (!verify_recaptcha($_POST["g-recaptcha-response"])) {
            echo ' <script>
            document.getElementById("errorText").style.visibility="visible";
            document.getElementById("errorText").innerHTML = "Unable to verify captcha";
            </script>';
            exit;
        }

        else {

            $username = $_POST["Rname"];
            $email = $_POST["Remail"];
            $password = $_POST["Rpassword"];
            register($username, $email,$password);

        }
    }


    if (isset($_POST["login_submit"])) {

        $username = $_POST["username"];
        $password = $_POST["password"];

        if (!isset($_SESSION['user_id']))
            authenticate($username, $password);

        //ifall de på ngt sätt lyckats komma hit...
        else {
            if(session_destroy())
                authenticate($username, $password);
            else
                header('Location: ' . home_url());
        }
    }

}




function register($name, $email,$password){




    global $wpdb;
    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix . 'kult_users';
    $hashedPassword= password_hash($password,PASSWORD_DEFAULT);
    $activationkey= $email . rand(1,1000);

    $data = array(
        'name' => $name,
        'email' => $email,
        'password' => $hashedPassword,
        'activation_key' => $activationkey
    );
    $row = $cfdb->get_row("SELECT email from $table_name WHERE email = '$email'",ARRAY_N);

    //mail finns redan
    if(!empty($row)) {
        echo '<script> 
               
                container.classList.add("right-panel-active");
               
                document.getElementById("Rname").value="' .$name. '";
                document.getElementById("Remail").value="' .$email. '";
                document.getElementById("Remail").style.border ="2px solid red";
                document.getElementById("errorText").style.visibility="visible";
                </script>';

    }

    //unik mail-good to insert
    else{


        $result = $cfdb->insert($table_name, $data);
        if($result===false){
            echo "error: " ;
        }
        require ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require ABSPATH . WPINC . '/PHPMailer/Exception.php';

        $mail = new PHPMailer(true);
        try {
            // Server settings
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
            $mail->addAddress('esq.barrel@gmail.com', $name);

            // Email content
            $mail->isHTML(true);
            $mail->Subject = 'Kulturskolan: Registrering';
            $link = "Tack för att du skapat ett inlogg till Regional Kulturskola Skånes vikariebank. 
            Efter att du har aktiverat ditt konto genom länken <a href=".home_url().'/logga-in/authentication-php?activationkey='. $activationkey. "> här </a>
             så kan du gå tillbaka till sidan för att lägga upp din profil så att du blir sökbar som vikarie. <br> <br>
Allt gott, <br> <br>
<a href=".home_url().">Länk till Hemsida </a> <br><br>
 Notera att det inte går att svara på detta mail, kontakt med oss sker via: regionalkulturskola@lund.se  

";
            $mail->Body    = $link;



            // Send the email
            $mail->send();

            echo '

        <script>
         var body = document.getElementsByClassName("uk-container");
            
            // Replace the content of the body with new HTML
            body.innerHTML = "<h1>Account created successfully </h1><p>Check your email for activation link</p></div>";
            window.location.href = "'.home_url(). '/jobb/registrerad/";
        </script>';

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            echo 'Error sending email: ' . $mail->ErrorInfo;
        }
        // else
        //echo "Insert successfully";

       // ini_set('SMTP','smtp.gmail.com' );
        //ini_set('smtp_port', 25);
       // ini_set('');
        /*$from = "vikariebanken.kulturskolan@gmail.com" ;
        $to = $email;
        $subject = "Registration Confirmation";
        $link = " <a href=".home_url().'/logga-in/authentication-php?activationkey='. $activationkey. "> här </a>";
        $message = "<html><body><p>Tack för att du registrerade dig på vår hemsida! Du kan nu påbörja din ansökan till oss på kulturskolan efter du har aktiverat ditt konto. Det gör du genom att klicka på följande link: " . $link . "</p></body></html>";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: Kulturskolan\r\n"; // Replace with your email address


        $mailSent = mail($to, $subject, $message, $headers);
*/
       /* if ($mailSent) {
            echo '

        <script>
         var body = document.getElementsByClassName("uk-container");
            
            // Replace the content of the body with new HTML
            body.innerHTML = "<h1>Account created successfully </h1><p>Check your email for activation link</p></div>";
            window.location.href = "'.home_url(). '/jobb/registrerad/";
        </script>';
        } else {
            echo "Registration successful, but there was an issue sending the confirmation email.";
        }*/


    }


}

//username + eventuell passCheck
function authenticate($username,$password){

    global $wpdb;
    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix . 'kult_users';


    $results = $cfdb->get_results("SELECT * from $table_name WHERE email = '$username' LIMIT 1 ",OBJECT);



          //fins användare
    if($results) {

        foreach ($results as $result) {

            //checka hashade lösen med input
            if (password_verify($password, $result->password)) {




                $_SESSION['user_id'] = $result->id;
                $_SESSION['admin'] = $result->admin;
                $timestamp = time();
                $formattedDate = date('Y-m-d H:i:s', $timestamp);
                $data = array('lastLogin' => $formattedDate);
                $where =array('email'=>$username);


                $result = $cfdb->update($table_name, $data, $where);


                if($result===false)
                    exit("Error inserting into DB");
                //success, redirect.
                else
                echo '<script>window.location.href = "'. home_url() .'";</script>';


            } else {
                echo '<script type="text/javascript">';
                echo 'alert("Inloggning misslyckades");';
                echo '</script>';
            }


        }
    }
    else{
            echo '<script type="text/javascript">';
            echo 'alert("Det finns ingen användare registrerad med denna email");';
            echo '</script>';
        }

}

            get_footer();