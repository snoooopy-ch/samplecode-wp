<?php
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_utils.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin_list.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_user.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin_calculate.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin_tree.php');

class BSMlmAffiPointMain {
	private $bs_affiliate_options = [];

	protected static $_intance = null;

	protected $_swpm_flag = false;

	public static function get_instance() {
        self::$_intance = empty(self::$_intance) ? new BSMlmAffiPointMain() : self::$_intance;
        return self::$_intance;
    }

    public function __construct() {
		$this->bs_affiliate_options = (array) get_option( 'bs_mlm_affipoint_options', array() );

		add_action('plugins_loaded', array(&$this, "plugins_loaded"));
		add_action('init', array(&$this, 'init_hook'));
		add_action('wp', array(&$this, 'wp_hook') );
        add_action('wp_loaded', array(&$this, 'handle_wp_loaded_tasks'));
		
        add_action('admin_menu', array(&$this, 'admin_menu_hook'));
		add_action('admin_init', array(&$this, 'admin_init_hook'));

		//AJAX hooks (Admin)
        add_action('wp_ajax_bs_mlm_affipoint_ajax_setting', 'BSMlmAffiPointAdmin::bs_mlm_affipoint_ajax_setting');
        add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_setting', 'BSMlmAffiPointAdmin::bs_mlm_affipoint_ajax_setting');

		// AJAX hooks (User)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_check_code', 'BSMlmAffiPointUser::bs_mlm_affipoint_ajax_check_code');
        add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_check_code', 'BSMlmAffiPointUser::bs_mlm_affipoint_ajax_check_code');

		//AJAX hooks (Admin)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_html_tree', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_html_tree');
		add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_html_tree', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_html_tree');

		//AJAX hooks (Admin)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_html_tree_insert', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_html_tree_insert');
		add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_html_tree_insert', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_html_tree_insert');

		//AJAX hooks (Admin)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_tree_rebuild', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_tree_rebuild');
		add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_tree_rebuild', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_tree_rebuild');

		//AJAX hooks (Admin)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_generate_code', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_generate_code');
		add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_generate_code', 'BSMlmAffiPointAdminTree::bs_mlm_affipoint_ajax_generate_code');

		//AJAX hooks (Admin)
		add_action('wp_ajax_bs_mlm_affipoint_ajax_generate_point', 'BSMlmAffiPointAdminCalculate::bs_mlm_affipoint_ajax_generate_point');
		add_action('wp_ajax_nopriv_bs_mlm_affipoint_ajax_generate_point', 'BSMlmAffiPointAdminCalculate::bs_mlm_affipoint_ajax_generate_point');

		// user profile customize in admin page
		add_action( 'show_user_profile', 'BSMlmAffiPointUser::mlm_user_profile_customize', 12 );
		add_action( 'edit_user_profile', 'BSMlmAffiPointUser::mlm_user_profile_customize', 12 );
		add_action( 'personal_options_update', 'BSMlmAffiPointUser::mlm_user_profile_customize_update', 12 );
		add_action( 'edit_user_profile_update', 'BSMlmAffiPointUser::mlm_user_profile_customize_update', 12 );

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(is_plugin_active( 'simple-membership/simple-wp-membership.php' )) {
			$this->_swpm_flag = true;
		}

		if (is_plugin_active( 'bs-affiliate/index.php' )) {
			deactivate_plugins('bs-affiliate/index.php');
		}
    }

	// ==================================== plugin install & update =====================================
    public function plugins_loaded() {
        //Runs when plugins_loaded action gets fired
        if (is_admin()) {
			$installed_version = $this->get_option_value('bs_mlm_affipoint_active_version');
			if (!empty($installed_version) && $this->get_option_value('bs_mlm_affipoint_active_version') != BS_MLM_AFFIPOINT_VER) {
                $this->run_update_installer();
            }
        }
    }
	
	public function plugin_installer() {
		global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$charset_collate = $wpdb->get_charset_collate();

		$wp_track_table = $wpdb->prefix . "bs_mlm_affipoint";
		if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {
			$sql = "CREATE TABLE " . $wpdb->prefix . "bs_mlm_affipoint (
				id int(12) NOT NULL PRIMARY KEY AUTO_INCREMENT, 
				source_user_id int(12),
				target_user_id int(12),
				affiliate_code varchar(20) NOT NULL,
				use_date_time datetime NOT NULL,
				use_date_time_gm datetime NOT NULL,
				mail_send_flag tinyint(1) DEFAULT 0,
				note1 longtext,
				note2 longtext,
				point int(12) DEFAULT 0,
				create_date date NOT NULL
				) " . $charset_collate . ";";
			dbDelta($sql);
	
			$use_date_time = current_time('timestamp');
			$use_date = date("Y-m-d H:i:s", $use_date_time);
			$use_date_time_gm = get_gmt_from_date( $use_date );
			$admin_id = get_current_user_id( );
	
			$wpdb->insert(
				$wpdb->prefix . "bs_mlm_affipoint",
				[
					'source_user_id'	=> 0,
					'target_user_id'	=> $admin_id,
					'affiliate_code'	=> '',
					'use_date_time'		=> $use_date,
					'use_date_time_gm'	=> $use_date_time_gm,
					'mail_send_flag'	=> 1,
					'note1'				=> '',
					'note2'				=> '',
					'point'				=> 0,
					'create_date'		=> $use_date_time_gm
				]
				);
			update_user_meta( $admin_id, 'user_mlm_index', 0 );
		}

		$this->run_update_installer();
	}

	public function run_update_installer() {
		$this->set_option_value('bs_mlm_affipoint_use_flag', 1);
		$this->set_option_value('bs_mlm_affipoint_active_version', BS_MLM_AFFIPOINT_VER);

		$source_code_level = array();
		if($this->_swpm_flag){
			$swpm_level_lists = SwpmMembershipLevelUtils::get_all_membership_levels_in_array(); 
			foreach($swpm_level_lists as $id=>$label) {
				$source_code_level[] = $id;
			}
		}
		$this->set_option_value('source_code_level', maybe_serialize($source_code_level));
		$this->set_option_value('mlm_member_level', maybe_serialize($source_code_level));

		$this->save_option();
	}
	
	// ==================================== plugin hook =====================================
    public function handle_wp_loaded_tasks() {
		if ( is_admin() ) return;

		$bs_affiliate_user = BSMlmAffiPointUser::get_instance();
		
		if( !empty($this->get_option_value("bs_mlm_affipoint_use_flag")) ) {
			// source user action hook
			add_filter('tcd_membership_edit_account_form_table', array(&$bs_affiliate_user, 'user_edit_account_form_mlm'));
			add_action('tcd_membership_account_updated', array(&$bs_affiliate_user, 'user_edit_account_regist'), 10, 2);

			// target user action hook
			add_filter('tcd_membership_registration_account_form_table', array(&$bs_affiliate_user, 'user_create_account_form'));
			add_action('tcd_membership_account_created', array(&$bs_affiliate_user, 'user_create_account_regist'), 10, 2);

			// target user action hook(gianism login)
			add_filter( 'tcd_membership_login_after_gianism_register_form_body', array(&$bs_affiliate_user, 'user_create_account_gianism_form') );
			add_filter( 'tcd_membership_action_giansim_process', array(&$bs_affiliate_user, 'check_affiliate_code') );
			add_action( 'tcd_membership_gianism_account_updated', array(&$bs_affiliate_user, 'user_create_account_regist'), 10, 2);

			// user payment action hook
			add_action('swpm_payment_ipn_processed', array(&$bs_affiliate_user, 'swpm_payment_ipn_processed_with_mlm'), 10, 2);

			 
		}
    }

	// ------------------------------- admin page ----------------------------
    public function admin_bs_affiliate_list() {
        BSMlmAffiPointAdmin::get_instance()->handle_admin_bs_mlm_affipoint_list_menu();
    }

	public function admin_bs_affiliate_setting() {
        BSMlmAffiPointAdmin::get_instance()->handle_admin_bs_mlm_affipoint_setting_menu();
    }

	public function admin_bs_mlm_affipoint_tree() {
		BSMlmAffiPointAdminTree::get_instance()->handle_admin_bs_mlm_affipoint_tree_menu();
	}

	public function admin_menu_hook() {
        $menu_parent_slug = 'bs_mlm_affipoint_list';

		// Add menu
        add_menu_page(__("紹介システム", 'bs-mlm-affipoint'), "MLM紹介システム", BS_MLM_AFFIPOINT_MANAGEMENT_PERMISSION, $menu_parent_slug, array(&$this, "admin_bs_affiliate_list"), 'dashicons-groups', 8.6);
		add_submenu_page($menu_parent_slug, __("History", 'bs-mlm-affipoint'), 'History', BS_MLM_AFFIPOINT_MANAGEMENT_PERMISSION, 'bs_mlm_affipoint_list', array(&$this, "admin_bs_affiliate_list"));
		add_submenu_page($menu_parent_slug, __("Tree", 'bs-mlm-affipoint'), 'Tree', BS_MLM_AFFIPOINT_MANAGEMENT_PERMISSION, 'bs_mlm_affipoint_tree', array(&$this, "admin_bs_mlm_affipoint_tree"));
		add_submenu_page($menu_parent_slug, __("Settings", 'bs-mlm-affipoint'), 'Settings', BS_MLM_AFFIPOINT_MANAGEMENT_PERMISSION, 'bs_affiliate_setting', array(&$this, "admin_bs_affiliate_setting"));
    }

	public function admin_init_hook() {
	}

	public function wp_hook() {
		if ( is_admin() ) return;
	}

	public function init_hook() {
		if ( is_admin() ) return;
    }

	// ================================== utility function ============================
	public function set_option_value($key, $value) {	
		$this->bs_affiliate_options[$key] = $value;
	}

	public function get_option_value($key, $default = '') {	
		return isset($this->bs_affiliate_options[$key])? $this->bs_affiliate_options[$key] : $default;
	}

	public function save_option() {
		update_option('bs_mlm_affipoint_options', $this->bs_affiliate_options);
	}

	public static function is_localhost($whitelist = ['127.0.0.1', '::1']) {
		return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
	}
}