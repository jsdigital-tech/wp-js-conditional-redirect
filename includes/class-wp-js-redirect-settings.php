<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class WP_JS_Redirect_Settings {
  
	/**
	 * The single instance of WP_JS_Redirect_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */ 
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'wcondred_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	 public function add_menu_item  () {
		 $page = add_menu_page( 
			 	__( 'Settings', 'wp-jsdigital-redirect' ) , 
				__( 'JS Redirect', 'wp-jsdigital-redirect' ) , 
				'manage_options' , 
				$this->parent->_token . '_settings' ,  
				array( $this, 'settings_page' ),
				'dashicons-admin-links'
			);
	 }
  

	/**	
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="admin.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'wp-jsdigital-redirect' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}
 
	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
 	public function getoption ( $id ) {
		
		return get_option($this->base.$id);
	} //
 	public function setoption ( $id,$value ) {
		
		return add_option($this->base.$id,$value);
	} //
 	public function deleteoption ( $id ) {
		
		return delete_option($this->base.$id);
	} // End instance()

	private function settings_fields () {

		$settings['standard'] = array(
			'title'					=> __( 'Add Redirects', 'wp-jsdigital-redirect' ),
			'description'			=> __( '' ),
			'fields'				=> array(
				array(
					'id' 			=> 'cond_redirect',
					'label'			=> __( 'Manage Conditional Redirects' , 'wp-jsdigital-redirect' ),
					'description'	=> __( '', 'wp-jsdigital-redirect' ),
					'type'			=> 'repeater',
				),
			)
		); 
 
		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	} 


	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {
	
	?>

		<!--Build page HTML--> 
		 <div class="wrap cr_redirect" id="<?php echo $this->parent->_token; ?>_settings"> 
			 <div class="container">  
				<div class="row cr_border"> 
					<div class="col-md-3 cr_logo">
						<img class="cr_logo" src="<?php echo plugins_url('assets/images/redirect_logo.jpg', __DIR__)?>"/>
						<img class="hide" src="<?php echo plugins_url('assets/images/pencil-hover.svg', __DIR__)?>"/>
						<img class="hide" src="<?php echo plugins_url('assets/images/delete-hover.svg', __DIR__)?>"/>
					</div>
					<div class="col-md-9 cr_tab_menu">
						<ul class="nav nav-tabs">
							<li ><a data-toggle="tab" href="#crSettings" class="active">Settings</a></li>
							<li><a data-toggle="tab" href="#crLogs">Logs</a></li> 
							<li><a data-toggle="tab" href="#crFaq">FAQ</a></li> 
							<li><a data-toggle="tab" href="#crAbout">About</a></li>
						</ul>
					</div> 
		 		</div> <!--row.cr_border-->
				 <div class="row">
					<div class="tab-content cr_tab_content">
						<div id="crAbout" class="tab-pane">
							<h3>About</h3>
							<p>This is a simple conditional redirection plugin.</p>
						</div>

						<div id="crSettings" class="tab-pane in active">
							<h3>URL Redirects (<span class="cr_total_redirects"></span>)</h3> 
							<?php 
							if (isset($_GET['deleteid']) && $_GET['deleteid']!=''){
								if (wp_delete_post($_GET['deleteid'],true)) {
							?>								
							<div class="updated">
							  <p><?php esc_html_e( 'Redirect Rule Deleted!', 'text-domain' ); ?></p>
							</div>
							<?php }	
							}
							?>
							
							<?php 
							if (isset($_POST['postid']) && $_POST['postid']!=''){
							?>								
							<div class="updated">
							  <p><?php esc_html_e( 'Redirect Rule Edited!', 'text-domain' ); ?></p>
							</div>
							<?php 
							}
							?>
							<div class="cr_select">
								 <div class="item">Type <a href="#"><span class="all">All</span><span class="dashicons dashicons-arrow-down-alt2"></span></a></div>
								 <div class="item">Parameter <a href="#"><span class="all">All</span><span class="dashicons dashicons-arrow-down-alt2"></span></a></div>
								 <div class="item">Status <a href="#"><span class="all">All</span><span class="dashicons dashicons-arrow-down-alt2"></span></a></div>
							</div>
							
							<table id="crRedirectLists_wrapper" class="dataTable table table-custom-bordered" style="width:100%">
								<thead>
									<tr>
										<th><input type="checkbox" id="ifURL" value=""> <label for="ifURL">if URL</span></label></th>
										<th>Parameter</th>
										<th>Then Redirect to</th>
										<th>Redirecting Page</th>
										<th class="small-width">Conflicts</th> 
										<th class="small-width no-sort"></th> 
										<th class="small-width no-sort"></th> 
									</tr>
								</thead>
								<?php
								$posts = get_posts(array('post_type'=>'redirect404','numberposts'=>'-1','orderby'=>'publish_date','order'=>'ASC'));
								if ($posts):
								?>
								<tbody>
								<?php foreach ($posts as $p): ?>
								<?php
									$op = get_post_meta($p->ID,'op',true);
									$url = get_post_meta($p->ID,'url',true);
									$redirect = get_post_meta($p->ID,'redirect',true);
									$active = get_post_meta($p->ID,'active',true);
									$conflict = get_post_meta($p->ID,'conflict',true);
								?>
									<tr  id="<?php echo 'tr' . $p->ID; ?>" data-id="<?php echo $p->ID; ?>" data-op="<?php echo $op; ?>" data-url="<?php echo $url; ?>" data-redirect="<?php echo $redirect; ?>" data-active="<?php echo $active; ?>">
										<td class="cr_condition"><input type="checkbox" id="ifURL<?php echo $p->ID; ?>" value=""><label for="ifURL<?php echo $p->ID; ?>"><?php echo strtoupper($op); ?></label></td>
										<td class="cr_param"><?php echo $url; ?></td>
										<td class="cr_url"><span ><?php echo get_site_url().'/'; ?></span><?php echo $redirect; ?> <a href="<?php echo get_site_url().'/'.$url; ?>" target="_blank"><img src="<?php echo plugins_url('assets/images/link.png', __DIR__)?>"/></a></td>
										<td class="cr_active <?php echo $active ? 'cr_green':'cr_red'; ?>">&#9679; <?php echo $active ? 'Active':'Inactive'; ?></td>
										<td class="<?php echo $conflict!='' ? 'cr_red':'cr_green'; ?> cr_conclicts small-width">&#9679; <?php echo $conflict!='' ? '<span title="'.$conflict.'" data-toggle="tooltip" class="red-tooltip">Yes</span>':'No'; ?></td>
										<td class="cr_delete small-width"><input type="button"  class="btn-edit" title="Edit" data-id="<?php echo $p->ID; ?>" value="Edit"> <input title="Delete" type="button"  class="btn-delete" data-id="<?php echo $p->ID; ?>" value="Delete" ></td> 
										<td class="cr_update small-width"><a href="#">&#9679;&#9679;&#9679;</a></td> 
									</tr>
								<?php endforeach; ?>
								</tbody>
								<?php endif; ?>
								<tfoot> 
								</tfoot>
							</table> 
							<script>jQuery('.cr_total_redirects').html("<?php echo count($posts); ?>"); </script>
							<div class="edit-main-div">
								<div class="overlay"></div>
								<div class="edit-div">
									<form method="post" action="">
										<input type="hidden" value="" name="postid" id="postid">
										If URL : <select id="op" name="op" id="cond_redirect0">
										<option value="contain">Contains</option>
										<option value="equal">Equals</option></select>
										<input type="text" name="url" id="url" value=""> Then Redirect to <span class="cr_url"><?php echo home_url();?>/</span>
										<input type="text" class="custom-url" name="redirect" id="redirect" value="">
										<select id="active" name="active">
											<option value="1" selected="">Active</option>
											<option value="0">Inactive</option>
										</select>
										<p class="submit"><input type="submit" class="btn-save" value="Save"> 
											<button class="btn-save btn-close " value="">Close</button>
										</p>
									</form>
								</div>
							</div>
							<script>
								jQuery(document).ready(function () {
									jQuery('[data-toggle="tooltip"]').tooltip();
										jQuery("#crRedirectLists_wrapper .btn-delete").click(function(){
											if (confirm("Are you sure?")){
												var url = document.location.href+"&deleteid="+jQuery(this).data("id");
												document.location = url;
											}
										});
										jQuery(".btn-close").click(function(){jQuery('.edit-main-div, .add-main-div').hide();return false;});
										jQuery("#crRedirectLists_wrapper .btn-edit").click(function(){
												var $tr = jQuery(this).closest('tr');
												jQuery('.edit-div #postid').val($tr.data('id'));
												jQuery('.edit-div #op').val($tr.data('op'));
												jQuery('.edit-div #url').val($tr.data('url'));
												jQuery('.edit-div #redirect').val($tr.data('redirect'));
												jQuery('.edit-div #active').val($tr.data('active'));
												jQuery('.edit-main-div').show();
												
										}); 
										jQuery("#crRedirectLogs_wrapper .log-edit").click(function(){
												var $tr = jQuery(this).closest('tr'); 
												jQuery('.add-div input[name="wcondred_cond_redirect[0][url]"]').val($tr.data('url')); 
												jQuery('.add-main-div').show();
												
										}); 
										jQuery('table.dataTable tbody tr').each(function() { 	 
											jQuery(this).hover(function() {
												var hovID  = '#' + jQuery(this).attr("id") + ' td input[type=button]'; 
												jQuery(hovID).toggle(50);
											});
										});


									jQuery('#crRedirectLists_wrapper').DataTable({
										columnDefs: [
											{ targets: 'no-sort', orderable: false }
										]
									});	  
								});
							</script>
							<div class="cr_bottom_section"> 
								<?php
									$html= '';
									$tab = '';
									if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
										$tab .= $_GET['tab'];
									}
									// Show page tabs
									if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

										$html .= '<h3 class="nav-tab-wrapper">' . "\n";

										$c = 0;
										foreach ( $this->settings as $section => $data ) {

											// Set tab class
											$class = 'nav-tab';
											if ( ! isset( $_GET['tab'] ) ) {
												if ( 0 == $c ) {
													$class .= ' nav-tab-active';
												}
											} else {
												if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
													$class .= ' nav-tab-active';
												}
											}

											// Set tab link
											$tab_link = add_query_arg( array( 'tab' => $section ) );
											if ( isset( $_GET['settings-updated'] ) ) {
												$tab_link = remove_query_arg( 'settings-updated', $tab_link );
											}

											// Output tab
											$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

											++$c;
										}

										$html .= '</h3>' . "\n";
									}

									$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

										// Get settings fields
										ob_start();
										settings_fields( $this->parent->_token . '_settings' );
										do_settings_sections( $this->parent->_token . '_settings' );
										$html .= ob_get_clean();

										$html .= '<p class="submit">' . "\n";
											$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
											$html .= '<input name="Submit" type="submit" class="btn-save" value="' . esc_attr( __( 'Save Settings' , 'wp-jsdigital-redirect' ) ) . '" />' . "\n";
										$html .= '</p>' . "\n";
									$html .= '</form>' . "\n"; 
									  echo $html;
								?>    
							</div> 
						</div>
  
						<div id="crLogs" class="tab-pane">
							
							<div>Filter <span class="filter-logs"></span> </div> 
							<?php
								global $wp;
								global $wpdb;
								$table_name = $wpdb->prefix . 'jsdigital_404_analysis'; 
								$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; 
								$total = $wpdb->get_var( "SELECT COUNT(`id`) FROM {$wpdb->prefix}jsdigital_404_analysis" ); 
								$table_name = $wpdb->prefix . "jsdigital_404_analysis"; 
								$user = $wpdb->get_results( "SELECT * FROM $table_name" ); 
							?>
							<table id="crRedirectLogs_wrapper" class="dataTable table table-custom-bordered" style="width:100%">
								<thead>
									<tr> 
										<!--<th>Is Redirected?</th>-->
										<th>URL</th>
										<th>IP Address</th>
										<th>Error</th>
										<th>Date & Time</th>
										<th class="small-width no-sort"></th> 	
									</tr>
								</thead> 
								<tbody id="the-list">
									<?php foreach ($user as $row){ ?>
									<tr  id="<?php echo 'tr' . $row->id; ?>" data-id="<?php echo $row->id; ?>" data-op="" data-url="<?php echo $row->link;  ?>"  data-active="">
										<!--<td class="small-width"><?php //echo $row->redirected; ?></td>-->
										<td class="cr_url">
											<?php echo site_url(). '/' . '<span class="site-url">' . $row->link . '</span>';  ?> <a href="<?php echo site_url(). '/' . $row->link;?>" target="_blank"><img src="<?php  echo plugins_url('assets/images/link.png', __DIR__)?>" width="20"></a> 
										</td>
										<td><?php echo $row->user_ip; ?></td>
										<td><?php echo $row->error_code; ?></td>
										<td><?php echo $row->time; ?></td>
										<td class="cr_delete small-width"><input type="button"  class="btn-edit log-edit create-redirect" title="Create redirect" data-id="<?php echo $row->id; ?>" value="Edit"></td> 
									</tr>
									<?php } ?>
								</tbody>  
							</table>
							<div class="add-main-div">
								<div class="overlay"></div>
								<div class="add-div">
									<?php   
										echo '<button class="btn-save btn-close " value=""><span class="dashicons dashicons-no"></span></button>' . $html  ;
									?>
										 
								</div>
							</div>   
							<script>
								jQuery(document).ready(function () { 
									jQuery('#crRedirectLogs_wrapper').DataTable({
										"order": [[ 3, "desc" ]],
										columnDefs: [{ targets: 'no-sort', orderable: false }],
										initComplete: function () {
											this.api().columns([2]).every( function () {
												var column = this;
												var select = jQuery('<select class="filter-select"><option value="">All Logs</option></select>')
													.appendTo( jQuery(jQuery('.filter-logs')).empty() )
													.on( 'change', function () {
														var val = jQuery.fn.dataTable.util.escapeRegex(
															jQuery(this).val()
														);
								
														column
															.search( val ? '^'+val+'$' : '', true, false )
															.draw();
													} );
								
												column.data().unique().sort().each( function ( d, j ) {
													select.append( '<option value="'+d+'">'+d+'</option>' )
												});
											});
										}
									});	  
								});
							</script>
						</div> 
						<div id="crFaq" class="tab-pane  ">
							<h3>FAQ</h3>
							<p>Coming soon.</p>
						</div>  
						
					</div>
				</div>
		 	</div> <!--.container-->   
		 </div> <!--//.wrap-->
		
	<?php  }

	/**
	 * Main WP_JS_Redirect_Settings Instance
	 *
	 * Ensures only one instance of WP_JS_Redirect_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see WP_JS_Redirect()
	 * @return Main WP_JS_Redirect_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

}