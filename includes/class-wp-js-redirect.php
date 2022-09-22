<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_JS_Redirect {
 
	/**
	 * The single instance of WP_JS_Redirect.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null; 

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'wp_js_redirect';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		
		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		
		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new WP_JS_Redirect_Admin_API();
		}
		add_action( 'template_redirect', array( $this, 'wpd_eso_redirect_on_404'),9);  
		add_action( 'init',  array( $this,'add_404_redirect_post_type'), 0 ); 
		 

	} // End __construct ()
	
	
	public function add_404_redirect_post_type() {
		$labels = array(
			'name'                  => _x( 'Redirect 404', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Redirect 404', 'Post Type Singular Name', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Redirect 404', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title' ),
			'hierarchical'          => false,
			'public'                => false,
			'show_ui'               => false,
			'show_in_menu'          => false,
			'menu_position'         => 5,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => false,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'rewrite'               => false,
			'capability_type'       => 'page',
			'show_in_rest'          => false,
		);
		register_post_type( 'redirect404', $args );

		if (is_admin()){
			if (isset($_POST['postid']) && $_POST['postid']!=''){
				$this->settings->setoption('cond_redirect',array(array("id"=>$_POST['postid'],"op"=>$_POST['op'],"url"=>$_POST['url'],"redirect"=>$_POST['redirect'],"active"=>$_POST['active'])));
			}
			$redirects = $this->settings->getoption('cond_redirect');
			if ($redirects):
				foreach($redirects as $redirect) {
					$op=$redirect['op'];
					$jsdigital_url=$redirect['url'];
					$dest=$redirect['redirect'];
					$active=$redirect['active'];
					$id=isset($redirect['id'])?$redirect['id']:'';
					$conflict="";
					if (!$jsdigital_url || !$dest) continue;
					
					if (get_page_by_path( get_site_url().'/'.$jsdigital_url )) $conflict.='The url "/'.$jsdigital_url.' exists and does not return 404!';
					if ($op =='contain'){
						if (strpos($dest,$jsdigital_url)!==false) $conflict.='This redirect creates a loop!';
					}
					else {
						if ($dest==$jsdigital_url) $conflict.='This redirect creates a loop!';
					}
					$postarr=array(
						"post_type"=>"redirect404",
						"post_status"=>'publish',
						'meta_input' => array(
							'op' => $op,
							'url' => $jsdigital_url,
							'redirect'=>$dest,
							'active'=>$active,
							'conflict'=>$conflict,
						)
					);
					if($id) {$postarr['ID']=$id;wp_update_post($postarr);}
					else wp_insert_post($postarr);
				}
			$this->settings->deleteoption('cond_redirect');
			endif;
		}
	}

	public function wpd_eso_redirect_on_404(){
		
		if (is_404()) { 
			global $wp; 
			global $wpdb; 
			$is_redirected = 'no';
			$error_code = '404'; 
			$jsdigital_url_val; 
			$posts = get_posts(array('post_type'=>'redirect404','numberposts'=>'-1','orderby'=>'publish_date','order'=>'ASC'));
			if ($posts):
				foreach ($posts as $p):
					$op = get_post_meta($p->ID,'op',true);
					$jsdigital_url = get_post_meta($p->ID,'url',true);
					$redirect = get_post_meta($p->ID,'redirect',true);
					$active = get_post_meta($p->ID,'active',true);
					if ($active!="1") continue;
					if( (!$op || $op=='contain') && strpos($_SERVER['REQUEST_URI'], $jsdigital_url)!==false){
						wp_redirect(home_url($redirect),301);  
						//exit;		 
					}
					if( $op=='equal' && trim($_SERVER['REQUEST_URI'],'/') == trim(parse_url(get_site_url(),PHP_URL_PATH),'/').'/'.trim($jsdigital_url,'/')){
						 wp_redirect(home_url($redirect),301);  
						//exit;		 
					}     
					$jsdigital_url_val = $jsdigital_url;  
					print_r(explode(';',$jsdigital_url));
				endforeach;
			endif;
			
			/*log all 404 errors*/
			$allowed =  array('php'); /*only allow actual page urls instead of viewing all links .jpg, .css, etc.*/ 
			$durl=$wp->request; 
			$curl=substr(strrchr($durl,'.'),1);
            $tablename = $wpdb->prefix.'jsdigital_404_analysis';
            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) { 
                $user_ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { 
                $user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $user_ip = $_SERVER['REMOTE_ADDR'];
			}   
			if($curl=='' || in_array($curl,$allowed)){ 
				$wpdb->insert( $tablename, array(
					'user_ip' => $user_ip, 
					'time' => date("Y-m-d H:i:s"),
					'link' => $durl,
					'error_code' => $error_code,
					'redirected' => $is_redirected
						),
					array( '%s', '%s', '%s', '%s', '%s' ) 
				); 
			} 
			
			
			
		} 		
	} 

	 

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/jquery.repeater.min.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
		wp_register_script( $this->_token . '-admin-bootstrap', esc_url( $this->assets_url ) . 'js/bootstrap.bundle.min.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin-bootstrap' );
		wp_register_script( $this->_token . '-admin-dataTables', esc_url( $this->assets_url ) . 'js/jquery.dataTables.min.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin-dataTables' );
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/cr_style.css' );
		wp_enqueue_style( $this->_token . '-admin' ); 
		wp_register_style( $this->_token . '-admin-bootstrap', esc_url( $this->assets_url ) . 'css/bootstrap.min.css' );
		wp_enqueue_style( $this->_token . '-admin-bootstrap' ); 
		wp_register_style( $this->_token . '-admin-dataTables', esc_url( $this->assets_url ) . 'css/jquery.dataTables.min.css' );
		wp_enqueue_style( $this->_token . '-admin-dataTables' ); 
	} // End admin_enqueue_scripts ()
 


	/**
	 * Main WP_JS_Redirect Instance
	 *
	 * Ensures only one instance of WP_JS_Redirect is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_JS_Redirect()
	 * @return Main WP_JS_Redirect instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();

		/*Build database for log viewer*/
		global $wp;
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'jsdigital_404_analysis';
    
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            user_ip VARCHAR(255) NOT NULL,
            link VARCHAR(255) NOT NULL,
            error_code VARCHAR(255) NOT NULL,
			redirected VARCHAR(255) NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";

        error_reporting(0);
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        add_option('test_db_version', $test_db_version);

        if (! wp_next_scheduled ( array( ) )) {
            wp_schedule_event(time(), 'hourly', array(
                $this, 'my_hourly_event'
            ));
		
		} 
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}