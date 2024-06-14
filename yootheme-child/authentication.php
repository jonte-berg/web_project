

<?php

/*
Template Name: authentication
*/

/*
 * This File handles the activation of accounts through the activation key
 * This File also handles the password reset of accounts through the reset key
 *
 * */

get_header();

global $wpdb;

//metod för konto-aktivering
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["activationkey"])) {


    $activationKey = $_GET["activationkey"];
    //fix for google feature (using a gmail and adding +N
    if (str_contains($activationKey," "))
        $activationKey= str_replace(" ","+",$activationKey);

    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix.'kult_users';
    $row = $cfdb->get_row("SELECT email from $table_name WHERE activation_key = '$activationKey' AND activated='0'",ARRAY_N);

    //correct activation_key
    if(!empty($row)) {

       $data = array('activated' => '1');
       $where = array('email'=>$row[0]);
       $result = $cfdb->update($table_name,$data,$where);

       if($result===false)
           exit("Error inserting into DB");
       else {
           echo '<div style="text-align: center">';
           echo '<h3> Ditt konto med email: ' . $row[0] . '<br>är nu aktiverat! <br> 
                    <br>Du kan nu logga in
                    <a href="../../logga-in" style="color: blue; text-decoration:underline;"> här</a> </h3>';
           echo '</div>';
       }

    }

    //wrong activation_key
    else{
        echo '<div style="text-align: center">';
        echo '<h2 style="color: red;"> Ogiltig aktiveringsnyckel<br>Dubbelkolla din email för rätt aktiveringsnyckel <br> Åker tillbaka till startsidan om <p id="countdown">5</p></h2>';
        echo '';
        echo'</div>';
    }

    echo '<script>
      function updateCountdown() {
              
            var countdownElement = document.getElementById("countdown");
            var currentCount = parseInt(countdownElement.innerText);

                
            if (currentCount <= 0)
            window.location.href = "'.home_url().'"; // Change the URL
        
            else {
       
            countdownElement.innerText = (currentCount - 1).toString();
            setTimeout(updateCountdown, 1000); // 1000 milliseconds = 1 second
            }
       }
    
       
       updateCountdown();
             
    </script>';
}

//metod för lösenordsåterställning
else if($_SERVER["REQUEST_METHOD"]=== "GET" &&isset($_GET["resetlink"])){

    $resetlink = $_GET["resetlink"];

    if(strlen($resetlink)!=20){
        echo '<div style="text-align: center">';
    echo '<h2 style="color: red;"> Utgången länk !<br>Gör en ny återställning så du får en ny länk</h2>';
    echo '';
    echo'</div>';
    echo '<script>
        window.location.href = "'.home_url().'";
        </script>';
    exit;}

    $reset_expire = substr($resetlink,10);

    //only valid if activated in less than an hour!
    if((time()-$reset_expire)>=3600){
        echo '<div style="text-align: center">';
        echo '<h2 style="color: red;"> Utgången länk !<br> Gör en ny återställning så du får en ny länk</h2>';
        echo '';
        echo'</div>';
        echo '<script>
        window.location.href = "'.home_url().'";
        </script>';
        exit;
    }

    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix.'kult_users';
    $row = $cfdb->get_row("SELECT email from $table_name WHERE reset_token = '$resetlink'",ARRAY_N);

    //correct resetlink - generate reset pass form
    if(!empty($row)) {

        echo '

    <div class="container" id="container" style="justify-content: center">
        <div class="form-container-reset" >
            <form method="POST" action="reset-success" id="reset_form">
                
                <h1>New password  </h1>
                <label>Email</label>
                <input type="email"   value="'. $row[0]. '" placeholder="'. $row[0].'" name="Remail" id="Remail" readonly />
                <input type="password" id="Rpassword" required  placeholder="Password"  name="Rpassword"/>
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm Password" >
                <button type="submit" value="submit" >Submit</button>
                <p id="errorText" style="color: red;visibility: hidden">Error:</p>
                <p id="countDown" ></p>
        
        </form>
    </div>';

    }
    else{ //wrong activation_key
        echo '<div style="text-align: center">';
        echo '<h2 style="color: red;"> Fel aktiveringsnyckel !<br>Kolla din email som du registrerade dig med för att hitta din länk <br>Du förflyttas nu till startsidan om:  <p id="countdown">5</p></h2>';
        echo '';
        echo'</div>';
    }
    //script for password validation
    echo '<script> 


document.getElementById("reset_form").onsubmit = function () {
        return validatePassword();


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


        
    };
  
   </script>';

}

//wrong GET requests...
else {
    echo '<div style="text-align: center">';
    echo '<h2 style="color: red;">Kolla din email som du registrerade dig med för en länk du återställer lösenordet med </br> Du blir nu skickad till startsidan om: <h3 id="countdown">5</h3></h2>';
    echo '';
    echo '</div>';

    //script for auto redirect
    echo '<script>
      function updateCountdown() {
              
            var countdownElement = document.getElementById("countdown");
            var currentCount = parseInt(countdownElement.innerText);

                
            if (currentCount <= 0)
             window.location.href = "'.home_url().'"; 
        
            else {
       
            countdownElement.innerText = (currentCount - 1).toString();
            setTimeout(updateCountdown, 1000); // 1000 milliseconds = 1 second
            }
       }
    
       
       updateCountdown();
             
    </script>';
}







get_footer();

