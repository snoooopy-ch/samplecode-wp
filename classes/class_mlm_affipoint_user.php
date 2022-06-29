<?php
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin_list.php');
include_once(BS_MLM_AFFIPOINT_PATH . 'classes/class_mlm_affipoint_admin_tree.php');

class BSMlmAffiPointUser {
	protected static $_intance = null;
	protected $_error = '';
	protected $_swpm_flag = false;

	public static function get_instance() {
        self::$_intance = empty(self::$_intance) ? new BSMlmAffiPointUser() : self::$_intance;
        return self::$_intance;
    }

    public function __construct() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if(is_plugin_active( 'simple-membership/simple-wp-membership.php' )) {
			$this->_swpm_flag = true;
		}
    }

	function set_error($error){
		$this->_error = $error;
	}

	function get_error() {
		return $this->_error;
	}

	// load common library
	public function common_library() {
		wp_enqueue_script('jquery');
		wp_enqueue_style('bs_mlm_affipoint_user', BS_MLM_AFFIPOINT_URL . '/css/user.css', array(), BS_MLM_AFFIPOINT_VER);
		wp_enqueue_script( 'bs_mlm_affipoint_user', BS_MLM_AFFIPOINT_URL . '/js/user.js', array( 'jquery' ), BS_MLM_AFFIPOINT_VER, true );
		wp_localize_script( 'bs_mlm_affipoint_user', 'bs_mlm_affipoint_user_ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function handle_admin_bs_affiliate_list_menu() {
		$this->common_library();
	}

	public function user_edit_account_form_mlm($args){
		// if(current_user_can('administrator')) { return; }

		$this->common_library();

		$user = wp_get_current_user();

		update_user_meta( $user->ID, 'aaaaa', 'bbbbbb' );

		if($this->_swpm_flag) {
			$swpm_level = SwpmMemberUtils::get_logged_in_members_level();

			$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
			$arr_source_code_level = maybe_unserialize($bs_affiliate_main->get_option_value("source_code_level"));
			if(empty($arr_source_code_level)){ $arr_source_code_level = array(); }
			if(!is_array($arr_source_code_level)){$arr_source_code_level = array();}
			
			if(!in_array($swpm_level, $arr_source_code_level)){ return; }
		}

		ob_start();
?>
<tr>
	<th><label for="label_hidden_affiliate">MLM Code</label></th>
	<td class="p-membership-form__table-radios">
	    <div style="display:flex; margin-bottom:10px;">
		    <input style="" class="regular-text" type="text" name="bs_mlm_affipoint_source_code" id="bs_mlm_affipoint_source_code" length="6" value="<?php echo(isset($user->bs_mlm_affipoint_source_code)?$user->bs_mlm_affipoint_source_code:'');?>" readonly="readonly" >
		    <input style="min-width: 140px; height: 50px; line-height: normal;" class="p-button" type="button" name="btn_create_affiliate_code" id="btn_create_affiliate_code" value="Get a code">
		</div>
		<p style="margin-bottom:0px;"><?php echo($bs_affiliate_main->get_option_value("target_code_description"));?></p>
	</td>
</tr>
<?php
		$html = ob_get_clean();
		echo($html);
	}

	public function user_edit_account_regist($user_id, $formdata){

		$user = wp_get_current_user();

		// affiliate setting
		$meta_key = 'bs_mlm_affipoint_source_code';
		$bs_affiliate_source_code = isset( $formdata[$meta_key] ) ? $formdata[$meta_key] : '';

		update_user_meta( $user_id, $meta_key, $bs_affiliate_source_code );
	}

	public function user_create_account_gianism_form($args) {
		// $this->common_library();

		$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
		$code_required_flag = $bs_affiliate_main->get_option_value("code_required_flag");
		
		$label = "Affiliate code";
		if($code_required_flag) {
			$label .= "Required";
		}

		ob_start();
?>
<p class="p-membership-form__login-affiliate_target_code">
	<input type="text" 
		name="bs_mlm_affipoint_target_code" 
		id="bs_mlm_affipoint_target_code" 
		value="<?php echo esc_attr( isset( $_REQUEST['bs_mlm_affipoint_target_code'] ) ? $_REQUEST['bs_mlm_affipoint_target_code'] : '' ); ?>" 
		placeholder="<?php echo esc_attr( $label ); ?> " 
		data-confirm-label="Affiliate code" 
		maxlength="6" <?php if($code_required_flag) { echo("required"); } ?> 
		style="margin-bottom: 0px!important;">
</p>
<?php
		$html = ob_get_clean();
		echo($html);
	}

	public function user_create_account_form($args)
	{
		$this->common_library();

		$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
		$code_required_flag = $bs_affiliate_main->get_option_value("code_required_flag");
		
		ob_start();
?>
<tr>
	<th><label>アフィリエイトコード<?php if($code_required_flag) {?><span class="is-required">(Required)</span><?php }?></label></th>
	<td>
		<input type="text" name="bs_mlm_affipoint_target_code" id="bs_mlm_affipoint_target_code" 
			value="<?php echo esc_attr( isset( $_REQUEST['bs_mlm_affipoint_target_code'] ) ? $_REQUEST['bs_mlm_affipoint_target_code'] : '' ); ?>" 
			data-confirm-label="Affiliate code" 
			maxlength="6" <?php if($code_required_flag) { echo("required"); } ?> />
	</td>
</tr>
<?php
		$html = ob_get_clean();
		echo($html);
	}


	/**
	 * Trigger when user fill his registeration page.
	 */
	public function user_create_account_regist($user_id, $formdata)
	{
		error_log("mlm-user_create_account_regist", 0);
		error_log(print_r($user_id, true), 0);
		error_log(print_r($formdata, true), 0);

		$affiliate_code = $formdata['bs_mlm_affipoint_target_code'];
		if ( !empty ($affiliate_code)) {
			update_user_meta( $user_id, 'bs_mlm_affipoint_target_code', $affiliate_code );
		}
		
	}

	/**
	 * action to be triggered when user's payment is completed.
	 * 
	 * @param ipn_data Contains payment parameters
	 * @author rinshen
	 */

	public function swpm_payment_ipn_processed_with_mlm( $ipn_data ) {
		error_log("mlm-swpm_payment_ipn_processed_with_mlm", 0);
		error_log(print_r($ipn_data, true), 0);

		$user_id = $ipn_data['swpm_id'];
		$membership_level = SwpmMemberUtils::get_user_by_id($user_id)->membership_level;
		$this->insertTreeAfterMembershipUpdate( $user_id, $membership_level );
	}

	public function insertTreeAfterMembershipUpdate( $user_id, $membership_level ) {
		error_log("mlm-insertTreeAfterMembershipUpdate", 0);
		error_log(print_r($user_id, true), 0);
		error_log(print_r($membership_level, true), 0);

		$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
		$arr_mlm_member_level = maybe_unserialize($bs_affiliate_main->get_option_value("mlm_member_level"));
		if(empty($arr_mlm_member_level)){ $arr_mlm_member_level = array(); }

		if (in_array( $membership_level, $arr_mlm_member_level )) {
			global $wpdb;
			$table = $wpdb->prefix . "bs_mlm_affipoint";
			$records = $wpdb->get_results("SELECT `source_user_id` FROM $table WHERE `target_user_id` = '$user_id' OR `source_user_id` = '$user_id'");
			if ( count( $records ) !== 0 ) {
				return;
			} 

			$bs_mlm_affipoint_target_code = get_user_meta( $user_id, 'bs_mlm_affipoint_target_code', true );

			// Invalid user_id.
			if ( $bs_mlm_affipoint_target_code === false)
				return;
			
			// Empty bs_mlm_affipoint_target_code.
			if ( empty( $bs_mlm_affipoint_target_code )) {
				$parent_node = $this->seekUserPlace(-1, 0, $user_id);
			} else {
				$users = get_users(array(
					'meta_query' => array(
						array(
							'key' => 'bs_mlm_affipoint_source_code',
							'value' => $bs_mlm_affipoint_target_code,
							'compare' => '='
						)
					)
				));
				if(! $users) { return ; }
				$user_mlm_index = get_user_meta( $users[0]->ID, 'user_mlm_index', true );
				$parent_node = $this->seekUserPlace($users[0]->ID, $user_mlm_index, $user_id);
			}
	
			if ($parent_node == -1) {
				return ;
			}
	
			$this->insertTree( $parent_node, $user_id, $bs_mlm_affipoint_target_code );
		}
	}

	public function tree_add_in_admin_page($parent_id, $user_id) {
		$parent_mlm_index = get_user_meta( $parent_id, 'user_mlm_index', true );
		$parent_node = $this->seekUserPlace($parent_id, $parent_mlm_index, $user_id);
		if ($parent_node == -1) {
			return 0;
		}
		$this->insertTree( $parent_node, $user_id );
		return 1;
	}

	public function insertTree($parent_node, $user_id, $affiliate_code = '') {
		global $wpdb;
		$table = $wpdb->prefix . "bs_mlm_affipoint";

		$exist = $wpdb->get_results("SELECT `target_user_id` FROM $table WHERE `target_user_id` = $parent_node ");
		if ( count ($exist) == 0) {
			return;
		}

		$records = $wpdb->get_results("SELECT `source_user_id` FROM $table WHERE `target_user_id` = '$user_id' OR `source_user_id` = '$user_id'");
		if ( count( $records ) !== 0 ) {
			return;
		} 
		
		$use_date_time = current_time('timestamp');
		$use_date = date("Y-m-d H:i:s", $use_date_time);
		$use_date_time_gm = get_gmt_from_date( $use_date );

		$affliate_data = array(
			'source_user_id' => $parent_node,
			'target_user_id' => $user_id,
			'affiliate_code' => $affiliate_code,
			'use_date_time' => $use_date,
			'use_date_time_gm' => $use_date_time_gm,
			'mail_send_flag' => '',
			'create_date' => $use_date_time_gm,
		);

		if ( $affiliate_code != '' ) {
			$affliate_data['mail_send_flag'] = BSMlmAffiPointUtils::sendMailForUseAffiliate($affliate_data) ? 1 : 0;
		}

		$wpdb->insert($table, $affliate_data);
		$bsMlmAffipointAdminTree = BSMlmAffiPointAdminTree::get_instance();
		$bsMlmAffipointAdminTree->rebuildNodeInfo(false);
	}

	public function check_affiliate_code($error = array()) {
		$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
		$code_required_flag = $bs_affiliate_main->get_option_value("code_required_flag");

		$affiliate_code = isset($_REQUEST['bs_mlm_affipoint_target_code']) ? $_REQUEST['bs_mlm_affipoint_target_code'] : '';
		if($code_required_flag && empty($affiliate_code)) {
			$error[] = "Affiliate code is required.";
			return $error;
		}

		if(!empty($affiliate_code)){
			if(!$this->_check_code_possible($affiliate_code)){
				$error[] = "Invalid affiliate code";
				return $error;
			}
		}

		return $error;
	}

	private function _check_code_possible($affiliate_code) {
		$is_success = false;

		// exist code?
		$users = get_users(array(
			'meta_query' => array(
				array(
					'key' => 'bs_mlm_affipoint_source_code',
					'value' => $affiliate_code,
					'compare' => '='
				)
			)
		));

		if($users) {
			$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
			$code_use_count_flag = $bs_affiliate_main->get_option_value("code_use_count_flag");
			$code_use_count = $bs_affiliate_main->get_option_value("code_use_count");

			if($code_use_count_flag) {
				$table = $wpdb->prefix . "bs_mlm_affipoint";

				$query = "SELECT count(id) as use_count FROM $table WHERE affiliate_code = '$affiliate_code'";
				$temp_row = $wpdb->get_row($query, ARRAY_A);
				if($temp_row['use_count'] >= $code_use_count) {
					$is_success = false;
				} else {
					$is_success = true;
				}
			} else {
				$is_success = true;
			}
		} else {
			$is_success = false;
		}

		return $is_success;
	}

	// ==================== ajax process ===============
	// check affiliate code
	public static function bs_mlm_affipoint_ajax_check_code() {
		global $wpdb;

		$result["result"] = "fail";
		$result["error_message"] = "Invalide affiliate code";

		$affiliate_code = $_REQUEST['affiliate_code'];

		$is_success = false;
		if(!empty($affiliate_code)){
			// exist code?
			$users = get_users(array(
				'meta_query' => array(
					array(
						'key' => 'bs_mlm_affipoint_source_code',
						'value' => $affiliate_code,
						'compare' => '='
					)
				)
			));

			if($users) {
				$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
				$code_use_count_flag = $bs_affiliate_main->get_option_value("code_use_count_flag");
				$code_use_count = $bs_affiliate_main->get_option_value("code_use_count");

				if($code_use_count_flag) {
					$table = $wpdb->prefix . "bs_mlm_affipoint";

					$query = "SELECT count(id) as use_count FROM $table WHERE affiliate_code = '$affiliate_code'";
					$temp_row = $wpdb->get_row($query, ARRAY_A);
					if($temp_row['use_count'] >= $code_use_count) {
						$result["result"] = "fail";
						$result["error_message"] = "Invalide affiliate code";
					} else {
						$is_success = true;
					}
				} else {
					$is_success = true;
				}
			} else {
				$result["result"] = "fail";
				$result["error_message"] = "Please check a code";
			}
		}

		if($is_success){
			$result["result"] = "success";
			$result["error_message"] = "";
		}

		echo json_encode($result);
		wp_die();
	}


	/**
	 * Seek best place for new member.
	 * 
	 * @param root_id Where start tree to.
	 * @param root_index MLM's index for root_id
	 * @param user_id User inserted newly.
	 */
	// seek user position if having not mlm-code
	public function seekUserPlace($root_id, $root_index, $user_id) {
		global $wpdb;
		$table = $wpdb->prefix . "bs_mlm_affipoint";

		// If table is empty, insert.
		try {
			$records = $wpdb->get_results("SELECT `target_user_id` FROM $table WHERE 1");
			if (count($records) == 0) {
				update_user_meta( $user_id, 'user_mlm_index', 0 );
				return 0;
			}
		} catch (Exception $e) {}
		
		if ($root_index === 0) {
			$child_queue[$root_index] = 0;
		} else {
			$records = $wpdb->get_results("SELECT `source_user_id` FROM $table WHERE `target_user_id` = '$root_id'");
			$child_queue[$root_index] = get_user_meta( $records[0]->source_user_id, 'user_mlm_index', true );
		}
		
		$new_position = 0;

		try {
			while(count($child_queue) != 0) {
				$current = array_key_first($child_queue);
				$current_parent = array_values($child_queue)[0];
				$child_queue = array_slice($child_queue, 1, null, true);

				$current_user = get_users(array(
					'meta_query' => array(
						array(
							'key' => 'user_mlm_index',
							'value' => $current,
							'compare' => '='
						)
					)
				));
				if(! $current_user) { 
					update_user_meta( $user_id, 'user_mlm_index', $current );
					$new_position = $current_parent;
					break;
				}

				$parent_id = $current_user[0]->ID;
				$query = "SELECT `target_user_id` FROM $table WHERE `source_user_id` = '$parent_id'";
				$children = $wpdb->get_results($query);

				$parent_deep = get_user_meta( $parent_id, 'user_mlm_deep', true );
				$parent_index = get_user_meta( $parent_id, 'user_mlm_index', true );
				foreach([0, 1, 2] as $index => $child) {
					$child_index = $parent_index + ($index + 1) * pow(3, $parent_deep);
					$child_queue[$child_index] = $parent_id;
				}
				ksort($child_queue);
			}
			
		} catch (Exception $e) {
			$new_position = -1;
		}

		return $new_position;
	}

	/**
	 * Add source_code and target_code into user's profile page in admin
	 * 
	 * @param user User info viewed in profile edit page
	 * 
	 * @author rinshen
	 * @return nil
	 */
	public static function mlm_user_profile_customize( $user ) {
		global $dp_options, $gender_options, $receive_options, $notify_options, $business_type_options;
		if ( ! $dp_options ) $dp_options = get_design_plus_option();
		wp_nonce_field( 'mlm_user_profile_customize', 'mlm_user_profile_customize_nonce', false );

		$source_code = get_user_meta( $user->ID, 'bs_mlm_affipoint_source_code', true );
		$target_code = get_user_meta( $user->ID, 'bs_mlm_affipoint_target_code', true );

		wp_enqueue_script( 'bs_mlm_affipoint_user_profile', BS_MLM_AFFIPOINT_URL . '/js/edit_profile.js', array( 'jquery' ), BS_MLM_AFFIPOINT_VER, true );
		wp_localize_script( 'bs_mlm_affipoint_user_profile', 'bs_affiliate_admin_setting_ajax_object', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
?>
	<h3>MLM Details</h3>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="bs_mlm_affipoint_source_code">MLM Code</label></th>
				<td>
					<input class="regular-text" name="bs_mlm_affipoint_source_code" type="text" id="bs_mlm_affipoint_source_code" value="<?php echo $source_code ?>" readonly />
					<?php if (empty($source_code)) { ?>
					<input id="btn-generate-source-code" class="button button-primary" type="button" value="コード発行" />
					<?php } ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bs_mlm_affipoint_target_code">MLM Code</label></th>
				<td><input class="regular-text" name="bs_mlm_affipoint_target_code" type="text" id="bs_mlm_affipoint_target_code" value="<?php echo $target_code ?>" <?php echo empty($target_code)? '' : 'readonly' ?>/></td>
			</tr>
		</tbody>
	</table>
<?php
	}

	/**
	 * Triger when administrator edit user's profile
	 * 
	 * @param user_id Current user's id
	 * 
	 * @author rinshen
	 * @return nil
	 */
	public static function mlm_user_profile_customize_update( $user_id ) {
		error_log("mlm-mlm_user_profile_customize_update", 0);
		error_log(print_r($user_id, true), 0);

		if ( empty( $_POST['mlm_user_profile_customize_nonce'] ) || ! wp_verify_nonce( $_POST['mlm_user_profile_customize_nonce'], 'mlm_user_profile_customize' ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
	
		update_user_meta( $user_id, 'bs_mlm_affipoint_source_code', $_POST['bs_mlm_affipoint_source_code'] );
		update_user_meta( $user_id, 'bs_mlm_affipoint_target_code', $_POST['bs_mlm_affipoint_target_code'] );

		$bs_affiliate_user = BSMlmAffiPointUser::get_instance();
		$bs_affiliate_user->insertTreeAfterMembershipUpdate( $user_id, $_POST['membership_level'] );
	}
}