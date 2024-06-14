<?php
/*
Template Name: newTableGen
*/

if(!(!isset($_SESSION['user_id'])||$_SESSION['admin']!=='1')) {

    //default db initialisation
    global $wpdb;
    $cfdb = apply_filters('cfdb7_database', $wpdb);
    $table_name = $cfdb->prefix . 'db7_forms';
    $form_post_id = 1483; // Query the database for form data



    $columns = $cfdb->get_row("SELECT form_value FROM $table_name WHERE form_post_id = $form_post_id LIMIT 1", OBJECT);
    echo '<table class="table-1669" id="applicants">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>UID </th>';
    echo '<th>Datum</th>';
    echo '<th>Namn</th>';
    echo '<th>form_id</th>';

//generated columns from form values

    $form_columns = $columns->form_value;
    $data_fields = array_keys(unserialize($form_columns));
    $td_fields = array();

//headers
    foreach ($data_fields as $key) {
        if (str_contains($key, 'bild') || str_contains($key, 'circus')
            || str_contains($key, 'dans') || str_contains($key, 'drama')
            || str_contains($key, 'media') || str_contains($key, 'crew')
            || str_contains($key, 'komun') || str_contains($key, '_file')) {

            //sorry.... 2 lazy to change form key value.... this is bad... should just change to kommun instead of komun in form
            if (str_contains($key, 'komun'))
                echo '<th>kommun</th>';

            else
                echo '<th>' . substr($key, 5) . ' </th>';


            array_push($td_fields, substr($key, 5));
        }
    }
    array_push($td_fields, "musik");
                  echo '<th>Musik</th>';
// Add more headers for your columns as needed
    echo '</tr>';
    echo '</thead>';



    //if GET contains filter values
    if ($_SERVER["REQUEST_METHOD"] == "GET" && (!empty($_GET))) {

        // Retrieve keys from the GET request
        $keys = array_keys($_GET);


        // Initialize an empty array to store keyWords
        $keyWords = array();
        $sort;
        // Iterate through keys and add them to the $keyWords array
        foreach ($keys as $key) {
            // You can perform additional checks or processing here if needed
            if (str_contains($key, "sort"))
                $sort = $_GET[$key];
            else
                $keyWords[str_replace("checkbox", "", $key)] = $_GET[$key];

        }

        // Now $keyWords is an associative array with keys from the GET parameters
        //print_r($keyWords);
        $query = "Select * FROM $table_name WHERE form_post_id = $form_post_id";
        foreach (array_keys($keyWords) as $key) {
            $query .= " AND form_value LIKE '%" . $key . "%'";
        }

        if (!empty($sort)) {
            if ($sort != "name")
                $query .= " ORDER BY " . $sort . " desc";


        }

        // echo $query;
        $results = $cfdb->get_results($query, OBJECT);
        if ($sort == "name") {

            $applikanter = array();



            foreach ($results as $applicant) {
                $applikanterItem = array();

                $applikanterItem['form_id'] = $applicant->form_id;
                $applikanterItem['user_id'] = $applicant->user_id;
                $applikanterItem['form_date'] = $applicant->form_date;


                array_push($applikanter, array_merge($applikanterItem,unserialize($applicant->form_value)));

            }


            function sortByKeyAsc($a, $b)
            {
                return $a['your-name'] <=> $b['your-name'];
            }

// Use usort to sort the array based on the custom function
            usort($applikanter, 'sortByKeyAsc');

        }


       // var_dump($applikanter);
        echo '<tbody id="table-body">';

//rows


        if ($sort == "name") {
            $results=$applikanter;
            foreach ($results as $result) {

                echo '<tr>';


                $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=".$result["user_id"]."  </a>  ".$result["form_id"];

                if (isset($result['cfdb7_status']) && ($result['cfdb7_status'] === 'read')) {

                    $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=".$result["user_id"]."   </a>  ".$result["form_id"];
                    $rowlink = "<tr class='clickable-row-read' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=".$result["user_id"];

                } else
                    $rowlink = "<tr class='clickable-row-unread' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=".$result["user_id"];



                echo '<td>' . $result["user_id"] . '</td>';
                echo '<td>' . esc_html(date('Y-m-d', strtotime($result["form_date"]))) . '</td>';

                echo '<td>' . esc_html($result['your-name']) . ' ' . '</td>';

                echo '<td>' . esc_html($result['form_id']) . '</td>';


                $data_fields = array_values($td_fields);
                $upload_dir    = wp_upload_dir();
                $cfdb7_dir_url = $upload_dir['baseurl'].'/cfdb7_uploads';
                foreach ($data_fields as $key) {
                    if (str_contains($key,'musik'))
                        break;
                    if (!is_array(($result['your-' . $key]))) {

                       if (str_contains($key,'cfdb7_file')){
                            if(str_contains($result['your-'.$key],'.jpg')||
                                str_contains($result['your-'.$key],'.png')||
                           str_contains($result['your-'.$key],'.gif')){
                            $src = $cfdb7_dir_url . "/" . $result['your-'.$key];
                            echo "<td><img src='$src' alt='ingen fil' style='max-width: 50px;max-height: 50px;'></td>";
                            }
                            else
                                echo '<td>' . $result['your-' . $key] . '</td>';
                        }
                            else
                                echo '<td></td>';

                    }

                    else {
                        echo '<td>';

                        foreach ($result['your-' . $key] as $subarr) {
                            echo $subarr . '</br>';

                        }
                        echo '</td>';
                    }

                }
                echo '<td>';
                foreach (array_keys($result) as $key){
                    if (str_contains($key, 'musik'))
                    {
                        if(!empty($result[$key][0]))
                            echo " ". substr($key, 10) .": ".implode(",",$result[$key]) . "<br>";
                    }


                }
                echo '</td>';

                echo '</tr>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        }
        else {
            foreach ($results as $result) {

                echo '<tr>';
                $form_value = unserialize($result->form_value);

                $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->form_id  </a>  $result->form_id ";

                if (isset($form_value['cfdb7_status']) && ($form_value['cfdb7_status'] === 'read')) {

                    $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id  </a>  $result->user_id ";
                    $rowlink = "<tr class='clickable-row-read' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id>";

                } else
                    $rowlink = "<tr class='clickable-row-unread' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id>";



                echo '<td>' . $result->user_id . '</td>';
                echo '<td>' . esc_html(date('Y-m-d', strtotime($result->form_date))) . '</td>';

                echo '<td>' . esc_html($form_value['your-name']) . ' ' . '</td>';

                echo '<td>' . esc_html($result->form_id) . '</td>';


                $data_fields = array_values($td_fields);


                $upload_dir    = wp_upload_dir();
                $cfdb7_dir_url = $upload_dir['baseurl'].'/cfdb7_uploads';




                foreach ($data_fields as $key) {
                    if (str_contains($key,'musik'))
                        break;
                    if (!is_array(($form_value['your-' . $key]))) {

                        if (str_contains($key,'cfdb7_file')){
                            if(str_contains($form_value['your-'.$key],'.jpg')||
                                str_contains($form_value['your-'.$key],'.png')||
                                str_contains($form_value['your-'.$key],'.gif')){
                                $src = $cfdb7_dir_url . "/" . $form_value['your-'.$key];
                                echo "<td><img src='$src' alt='ingen fil' style='max-width: 50px;max-height: 50px;'></td>";
                            }
                            else
                                echo '<td>' . $form_value['your-' . $key] . '</td>';
                        }
                        else
                            echo '<td></td>';

                    }

                    else {

                        echo '<td>';

                        foreach ($form_value['your-' . $key] as $subarr) {
                            echo $subarr . '</br>';

                        }
                        echo '</td>';
                    }

                }
                echo '<td>';
                foreach (array_keys($form_value) as $key){
                    if (str_contains($key, 'musik'))
                    {
                        if(!empty($form_value[$key][0]))
                            echo " ". substr($key, 10) .": ".implode(",",$form_value[$key]) . "<br>";
                    }


                }
        echo '</td>';

                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';


        }
    }

    //if GET is empty with no filter
    else {

        //generate the columns
        $query = "SELECT * FROM $table_name WHERE form_post_id = $form_post_id ORDER BY form_date desc";
        $results = $cfdb->get_results($query, OBJECT);

        $return_array = array();
        foreach ($results as $result) {
            $return_array[] = array(
                'form_id' => $result->form_id,
                'user_id' => $result->user_id
                //'form_value' =>unserialize($result->form_value)
            );
        }


        echo '<tbody id="table-body">';

//rows
        foreach ($results as $result) {

            echo '<tr>';
            $form_value = unserialize($result->form_value);
            $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->form_id  </a>  $result->form_id ";

            if (isset($form_value['cfdb7_status']) && ($form_value['cfdb7_status'] === 'read')) {

                $link = "<a href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id  </a>  $result->user_id ";
                $rowlink = "<tr class='clickable-row-read' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id>";

            } else
                $rowlink = "<tr class='clickable-row-unread' data-href=admin.php?page=cfdb7-list.php&fid=$form_post_id&ufid=$result->user_id>";


            echo $rowlink;
            echo '<td>' . $result->user_id . '</td>';
            echo '<td>' . esc_html(date('Y-m-d', strtotime($result->form_date))) . '</td>';

            echo '<td>' . esc_html($form_value['your-name']) . ' ' . '</td>';

            echo '<td>' . esc_html($result->form_id) . '</td>';


            $data_fields = array_values($td_fields);

            foreach ($data_fields as $key) {

                if (!is_array(($form_value['your-' . $key]))) {

                    if (str_contains($key,'cfdb7_file')){
                        if(str_contains($result['your-'.$key],'.jpg')||
                            str_contains($result['your-'.$key],'.png')||
                            str_contains($result['your-'.$key],'.gif')){
                            $src = $cfdb7_dir_url . "/" . $result['your-'.$key];
                            echo "<td><img src='$src' alt='ingen fil' style='max-width: 50px;max-height: 50px;'></td>";
                        }
                        else
                            echo '<td>' . $form_value['your-' . $key] . '</td>';
                    }
                    else
                        echo '<td></td>';

                }

                else {
                    echo '<td>';

                    foreach ($form_value['your-' . $key] as $subarr) {
                        echo $subarr . '</br>';

                    }
                    echo '</td>';
                }

            }

            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }



}





else{
    header('Location: ' . home_url());
    exit;
}
?>
