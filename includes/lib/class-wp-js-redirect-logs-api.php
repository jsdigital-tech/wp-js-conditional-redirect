<?php 

if ( ! defined( 'ABSPATH' ) ) exit;
class WP_JS_Redirect_Logs_Class {
    function __construct(){  
        add_action('my_hourly_event', array(
            $this,
            'nf_run_cronjob'
        ));
        
    } 
    public function nf_run_cronjob(){
        global $wpdb;
        $table_name = $wpdb->prefix . 'jsdigital_404_analysis';
        $sql="DELETE FROM  $table_name WHERE time < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $wpdb->query($sql);
	} 
	 
    function wps_theme_func_settings(){ 
        echo '<p>Export all 404 related data </p>';
        echo '<a class="ccsve_button button button-success" href="admin.php?page=nf_export_data&amp;fullexport=xlsx">Export to XLS</a>';
        
    }  
}

$var = new WP_JS_Redirect_Logs_Class(); 
