<?php

class BSMlmAffiPointAdminCalculate {
	protected static $_intance = null;

	public static function get_instance() {
        self::$_intance = empty(self::$_intance) ? new BSMlmAffiPointAdminCalculate() : self::$_intance;
        return self::$_intance;
    }

    public function __construct() {
    }	

	// load common library
	public function common_library() {
	}

	public static function bs_mlm_affipoint_ajax_generate_point() {
		error_log("mlm-schedule_generate_point_monthly_function-start", 0);

		$bs_affiliate_main = BSMlmAffiPointMain::get_instance();
		$arr_mlm_member_level = maybe_unserialize($bs_affiliate_main->get_option_value("mlm_member_level"));
		if(empty($arr_mlm_member_level)){ $arr_mlm_member_level = array(); }
		$arr_mlm_member_level = implode("','",$arr_mlm_member_level);

		global $wpdb;
		$mlm_table = $wpdb->prefix . 'bs_mlm_affipoint';
		$swpm_table = $wpdb->prefix . 'swpm_members_tbl';

		$query = "SELECT distinct($mlm_table" . ".source_user_id) as mlm_user FROM $mlm_table LEFT JOIN $swpm_table ON " . $mlm_table . ".source_user_id = " . $swpm_table . ".member_id ";
		$query .= "WHERE " . $swpm_table . ".membership_level IN ('".$arr_mlm_member_level."') ";
		$users = $wpdb->get_results($query);

		foreach ( $users as $index => $user) {
			$children = array();
			$children[] = $user->mlm_user;
			$total = 0;
			for ( $deep = 0; $deep < 8 ; $deep++ ) {
				$point_for_deep = $bs_affiliate_main->get_option_value("mlm_level_point_" . $deep, '1');

				$children_arr = implode("','",$children);
				$subQuery = "SELECT `target_user_id` FROM $mlm_table LEFT JOIN $swpm_table ON " . $mlm_table . ".target_user_id = " . $swpm_table . ".member_id ";
				$subQuery .= " WHERE `source_user_id` IN ('".$children_arr."') ";
				$subQuery .= " AND " . $swpm_table . ".membership_level IN ('".$arr_mlm_member_level."') ";
				$subQuery = $wpdb->get_results($subQuery);
				$count = count($subQuery);

				$total += $point_for_deep * $count;
				$children = array_column( $subQuery, 'target_user_id' );
				if ( count($children) == 0) {
					break;
				}
			}

			do_action( 'user_mlm_register', $user->mlm_user, $total, 5 );
		}
		error_log("mlm-schedule_generate_point_monthly_function-end", 0);

		echo json_encode([
			'code'	=> 'success'
		]);
		wp_die();
	}

	public static function schedule_generate_point_monthly_function() {
		$first_friday_of_month = date('Y-m-d', strtotime("first friday of this month"));
		$today = date('Y-m-d');
		$current = date('Y-m-d H:i:s');

		if ($first_friday_of_month == $today) {
			BSMlmAffiPointAdminCalculate::bs_mlm_affipoint_ajax_generate_point();
		}
	}
}