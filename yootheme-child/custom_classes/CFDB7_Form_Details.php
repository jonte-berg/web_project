<?php
/*Custom class for displaying a form submission as a card/profile */


namespace custom_classes;

class CFDB7_Form_Details
{
    private $form_id;
    private $form_post_id;

    /**
     * @return int
     */
    public function getFormId(): int
    {
        return $this->form_id;
    }

    /**
     * @param int $form_id
     */
    public function setFormId(int $form_id): void
    {
        $this->form_id = $form_id;
    }
    public function __construct($data)
    {
        //set the ID for the form u want ( hardcoded to 343 since thats the ID in DB for the form)
        $this->form_post_id = 1483;


        $this->form_id =  $data;





        $this->form_details_page();
    }

    public function form_details_page(){
        global $wpdb;
        $cfdb          = apply_filters( 'cfdb7_database', $wpdb );
        $table_name    = $cfdb->prefix.'db7_forms';
        $upload_dir    = wp_upload_dir();
        $cfdb7_dir_url = $upload_dir['baseurl'].'/cfdb7_uploads';
        $rm_underscore = apply_filters('cfdb7_remove_underscore_data', true);


        $results    = $cfdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $this->form_post_id AND user_id = $this->form_id LIMIT 1", OBJECT );
        $form_limit = $cfdb->get_results("select count(*)  from $table_name WHERE form_post_id = $this->form_post_id LIMIT 1",ARRAY_N);

        if ( empty($results) ) {
            echo  '<h1>Ingen ansökan gjord ännu... skickas vidare till ansökning...</h1>';
            echo '<script>
   window.location.href = "'.home_url(). '/registrera/";
</script>';

        }
        ?>
        <div class="wrap">
            <div id="welcome-panel" class="cfdb7-panel">
                <div class="cfdb7-panel-content">
                    <div class="welcome-panel-column-container">
                        <?php do_action('cfdb7_before_formdetails_title',$this->form_post_id ); ?>

                        <?php do_action('cfdb7_after_formdetails_title', $this->form_post_id ); ?>
                        <p><?php echo $results[0]->form_date; ?></p>
                        <?php
                        $yourSkills =  unserialize($results[0]->form_value);
                        $src =$cfdb7_dir_url. "/". $yourSkills['your-bildcfdb7_file'];


                        echo "<img src='$src' alt='Bild saknas' style='max-width: 200px;max-height: 200px;'>";
                        ?>
                        <?php $form_data  = unserialize( $results[0]->form_value );

                        foreach ($form_data as $key => $data):

                            $matches = array();
                            $key     = esc_html( $key );

                            if ( $key == 'cfdb7_status' )  continue;
                            if( $rm_underscore ) preg_match('/^_.*$/m', $key, $matches);
                            if( ! empty($matches[0]) ) continue;

                            if ( strpos($key, 'cfdb7_file') !== false ){

                                $key_val = str_replace('cfdb7_file', '', $key);
                                $key_val = str_replace('your-', '', $key_val);
                                $key_val = str_replace( array('-','_'), ' ', $key_val);
                                $key_val = ucwords( $key_val );
                                echo '<p><b>'.$key_val.'</b>: <a href="'.$cfdb7_dir_url.'/'.$data.'">'
                                    .$data.'</a></p>';
                            }else{


                                if ( is_array($data) ) {

                                    $key_val      = str_replace('your-', '', $key);
                                    $key_val      = str_replace( array('-','_'), ' ', $key_val);
                                    $key_val      = ucwords( $key_val );
                                    $arr_str_data =  implode(', ',$data);
                                    $arr_str_data =  esc_html( $arr_str_data );

                                    if (!empty($arr_str_data))
                                        echo '<p><b>'.$key_val.'</b>: '. nl2br($arr_str_data) .'</p>';

                                }else{

                                    $key_val = str_replace('your-', '', $key);
                                    $key_val = str_replace( array('-','_'), ' ', $key_val);

                                    $key_val = ucwords( $key_val );
                                    $data    = esc_html( $data );

                                    if (!empty($data))
                                        echo '<p><b>'.$key_val.'</b>: '.nl2br($data).'</p>';

                                }
                            }

                        endforeach;

                        $form_data['cfdb7_status'] = 'read';
                        $form_data = serialize( $form_data );
                        $form_id = $results[0]->form_id;

                        $cfdb->query( "UPDATE $table_name SET form_value =
                            '$form_data' WHERE form_id = '$form_id' LIMIT 1"
                        );


                        // buttons for testing
                       /* echo  '<span id="user-id">Form id: '. $form_id   . '</span>
                                <button id="prev-btn"><a href=?fid=129&ufid=';
                        if($form_id>1)
                            echo $form_id-1;
                        else
                            echo  $form_limit[0][0];
                        echo'  </a> ← </button>
                                <button id="next-btn"><a href=?fid=129&ufid=';

                        if($form_id<$form_limit[0][0])
                            echo $form_id+1;
                        else
                            echo '1';
                        echo '  </a> →</button>
                                <button id="back-btn"><a href=..  </a> Back 2 list</button>';
                       */
                        ?>
                    </div>
                </div>
            </div>

        </div>
        <?php
        do_action('cfdb7_after_formdetails', $this->form_post_id );

    }

}