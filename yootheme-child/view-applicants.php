<?php
/*
Template Name: Admin-view-applicants
*/

/*
 * This file handles the display of the table with all applicant submissions
 * in addition to this we also handle the modal popup with a detailed information about
 * the row in the table you clicked.
 */

//Only for admins
if(!isset($_SESSION['user_id'])||$_SESSION['admin']!=='1'){
	header('Location: ' . home_url());
	exit;
}
else{

    global $wpdb;

    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix . 'db7_forms';
    $form_post_id = 1483; // Query the database for form data

//default query
    $query = "SELECT * FROM $table_name WHERE form_post_id = $form_post_id ORDER BY form_date desc";



get_header();


$results = $cfdb->get_results($query, OBJECT);
$return_array = array();

foreach ($results as $result){
    $return_array[] =array(
        'form_id' => $result->form_id,
        'user_id' => $result->user_id
        //'form_value' =>unserialize($result->form_value)
    );
}

$columns = $cfdb->get_row("SELECT form_value FROM $table_name WHERE form_post_id = $form_post_id LIMIT 1",OBJECT);

//generating filter options!
$filters = $cfdb->get_row("Select * from wp_templates WHERE id=1",ARRAY_A);

//remove unneccessary arrays
unset($filters['id']);
unset($filters['name']);

//glöm inte lägga till musik
$single = array(
        $filters['cirkus'],
        $filters['drama'],
        $filters['kultur'],
        $filters['digitalt']
    );

    foreach (array_keys($filters) as $key){
        $filters[$key]= explode(', ',$filters[$key]);

    }


if ($results && $columns && $filters) {

    echo '  <hr>
<form id="filter" action="" method="get" onsubmit="updateTable(this)">';

    echo '
<H4>Filtrera ansökningar</H4>
<div class="accordion-body">
   <div class="accordion">
    
   
   
   
   <div class="containerr">
    <div class="label">annat</div>
    <div class="content"> 
     ';

        foreach (array_keys($single) as $key){

                echo '<label for="filter' . $single[$key] . '">' . $single[$key] . '</label>
                         <input class="filter" type="checkbox" name="' . $key . 'checkbox" id="filter' . $key . '"> </input>';

        }
    echo '</div></div>';

    foreach (array_keys($filters) as $key) {


        if(count($filters[$key])>1) {
            echo ' 
        <div class="containerr">
      <div class="label">'.$key.'</div>
      <div class="content"> ';


            foreach ($filters[$key] as $value) {

                echo ' <label for="filter' . $key . '">' . $value . '</label>
			        <input class="filter" type="checkbox" name="checkbox' . $value . '" id="' . $value . '"> </input>';

            }
            echo '</div>';
            echo '</div>';
    }

    }

  echo '</div>
        
        <hr>
      
     
    
           <h4 style="text-align: left"> Sortera efter</h4>
          <div style="text-align: left">
          
        
           
        <label for="nameSort">Namn</label>
           <input type="radio" id="nameSort" name="sort" value="name" style="max-width: 5%" />
         
        <label for="dateSort">Datum</label>
           <input type="radio" id="dateSort" name="sort" value="form_date" checked  style="max-width: 5%"/>
          
        
     </div>
             <hr>
        <button id="filterId" type="submit" ">filtrera</button>
        <button id="resetButton" type="reset"">rensa</button>
          <div id="loadingBar" class="loading-bar"></div>

    
       
       
        </div>
        
        </form>
        </div>
        
        <table class="table-1669" id="applicants">
        
        <tbody id="table-body">
        </tbody>
        </table>';


} else {
    echo 'No data found.';
}
?>




	<div id="myModal" class="modal">
		<div class="modal-content">
			<span class="close">&times;</span>
            <div id="loadingBar2" class="loading-bar"></div>
            <p id="loading">Loading...</p>
			<!-- Content to display the row's data -->
			<div id="modalContent"></div>
		</div>
	</div>


    <!-- accordion+table rendering script-->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            //render table
            const form = document.getElementById('filter');
            updateTable(form);

            //load accordion functionality
            const accordion = document.getElementsByClassName('label');
            for (i = 0; i < accordion.length; i++) {
                accordion[i].addEventListener('click', function () {

                    const cont = document.getElementsByClassName('content');
                    this.parentElement.classList.toggle('active');
                });
            }
        });
    </script>




    <!-- table update-->
<script>
    function showLoading() {
        document.getElementById('filterId').innerHTML = 'Loading...';

    }

    // Function to hide the loading icon
    function hideLoading() {
        document.getElementById('loadingBar').style.width = '0%';
        document.getElementById('filterId').innerHTML = 'Filtrera';
    }
function updateTable(form){


    event.preventDefault();

    showLoading();
    const formData = new FormData(form);

    // Create an object to store form values
    var formDataObject = {};
    var url="?";
    // Iterate through FormData entries
    for (const [name, value] of formData.entries()) {
        formDataObject[name] = value;
        url+=name+"="+value+"&";
    }

    const xhr = new XMLHttpRequest();
    xhr.open("GET",'https://localhost/public/applikanter/filter/'+url);
    document.getElementById('loadingBar').style.width = '0%';

    // Simulate loading for 3 seconds
    let width = 0;
    const interval = setInterval(function () {
        width += 10; // Increase the width (adjust as needed)
        document.getElementById('loadingBar').style.width = width + '%';

        // Check if loading is complete
        if (width >= 100) {
            clearInterval(interval); // Stop the interval
            // Optionally, you can perform additional actions here after loading is complete
        }
    }, 300);
    xhr.onreadystatechange=
    function (){

       if(xhr.readyState===4 && xhr.status===200){
            document.getElementById('loadingBar').style.width = '100%';
            clearInterval(interval);
            //update data
            document.getElementById("table-body").innerHTML=xhr.responseText;

        }
        hideLoading();
        };
    xhr.send();

    
    
 }
</script>




<script>

        //script for loading modal content
		const table = document.getElementById("applicants");
		const modal = document.getElementById("myModal");
		const modalContent = document.getElementById("modalContent");
		let firstTdContent = "";
        const loading = document.getElementById("loading");

		// Add a click event listener to the table rows
		table.addEventListener("click", function(event) {

			// Check if the clicked element is a table cell
			if (event.target.tagName === "TD") {

                // Get the parent row of the clicked cell
				const clickedRow = event.target.parentElement;
				const firstTd = clickedRow.querySelector("td:first-child");



				const firstTdContent = firstTd.innertext;


                loading.innerHTML =loading.innerHTML +" Profile with id: "+firstTd.innerText;

				modalContent.innerHTML = "";

                const xhr = new XMLHttpRequest();
                xhr.open("GET",'/public/applikanter/admin-php/?fid=343&ufid='+firstTd.innerText);
                document.getElementById('loadingBar2').style.width = '0%';

                // Simulate loading for 3 seconds
                let width = 0;
                document.getElementById('loadingBar2').style.height ='20px';
                document.getElementById('loadingBar2').style.background= 'gray';
                const interval = setInterval(function () {
                    width += 10; // Increase the width (adjust as needed)
                    document.getElementById('loadingBar2').style.width = width + '%';

                    // Check if loading is complete
                    if (width >= 100) {
                        clearInterval(interval); // Stop the interval
                        // Optionally, you can perform additional actions here after loading is complete
                    }
                }, 300);
                xhr.onreadystatechange=
                    function (){

                        if(xhr.readyState===4 && xhr.status===200){
                            document.getElementById('loadingBar2').style.width = '100%';
                            clearInterval(interval);
                            //update data
                            modalContent.innerHTML = xhr.responseText;
                            //loading finished
                            loading.innerHTML = " ";
                            document.getElementById('loadingBar2').style.width = '0%';

                        }
                        else {
                            loading.innerHTML=" Error loading profile...";

                        }

                    };
                xhr.send();
                modal.style.display = "flex";


			}
		});

		// When the user clicks on the close button or outside the modal, close it
		modal.addEventListener("click", function(event) {
			if (event.target === modal || event.target.className === "close") {
				modal.style.display = "none";
                loading.innerHTML=" Loading...";
			}
		});

	</script>



<?php
get_footer();
}
