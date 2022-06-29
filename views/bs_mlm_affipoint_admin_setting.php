<?php
$bs_affiliate_main_obj = BSMlmAffiPointMain::get_instance(); 

$arr_source_code_level = maybe_unserialize($bs_affiliate_main_obj->get_option_value("source_code_level"));
if(empty($arr_source_code_level)){ $arr_source_code_level = array(); }

$arr_mlm_member_level = maybe_unserialize($bs_affiliate_main_obj->get_option_value("mlm_member_level"));
if(empty($arr_mlm_member_level)){ $arr_mlm_member_level = array(); }
?>

<div class="notice wpstream_notices notice-info" >
	<p style="font-weight: bold">If you turn on MLM, you cannot use the option to "register as a member and settle membership fees at the same time"</p>
</div>


<form id="frm_bs_affiliate_setting" class="bs_affiliate_setting_form" method="post">

<div id="blv_tab-panel">
	<div class="blv_theme_option_field cf">
		<h3 class="blv_theme_option_headline">Bonus point setting</h3>
		<p>For the 8th and subsequent stages, points will not be awarded even if the number of people increases.</p>
		<table class="form-table">
			<tbody>
<?php for($i = 0; $i < 8; $i++ ) { ?>
	<tr>
		<th style="text-align: center"><label><?php echo ($i + 1) . 'Label depth'; ?></label></th>
		<td>
			<input type="number" name="mlm_level_point_<?php echo $i ?>" value="<?php echo($bs_affiliate_main_obj->get_option_value("mlm_level_point_" . $i, '1'));?>" style="width:180px;" min="1"/>
		</td>
	</tr>
<?php } ?>
			</tbody>
		</table>
		<hr>
		<br>
		<h3 class="blv_theme_option_headline"><?php BSMlmAffiPointUtils::e( 'Online Salon Affiliate Code Setting' ); ?></h3>
		<p>Online salon members can issue affiliate codes and referral codes on My Page (My Account).<br>
		When another user enters the code to register as a member, Since it is displayed<a href="?page=bs_mlm_affipoint_list"> in History</a>, please give any reward.</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label><?php BSMlmAffiPointUtils::e( 'Referrer code system' ); ?></label></th>
					<td>
						<label><input type="checkbox" name="bs_mlm_affipoint_use_flag" value="1" class="regular-text" <?php checked( $bs_affiliate_main_obj->get_option_value("bs_mlm_affipoint_use_flag"), 1 ); ?>>Enable referrer code system</label><br/><br/>
						<label><input type="checkbox" name="code_required_flag" value="1" class="regular-text" <?php checked( $bs_affiliate_main_obj->get_option_value("code_required_flag"), 1 ); ?>>Mandatory entry of referral code</label>
					</td>
				</tr>
				
				<tr>
					<th><label><?php BSMlmAffiPointUtils::e( 'The content of the email sent to the administrator at the time of application' ); ?></label></th>
					<td><textarea name="admin_mail_body" id="admin_mail_body" rows="6" class="regular-text"><?php echo esc_attr( $bs_affiliate_main_obj->get_option_value("admin_mail_body") ); ?></textarea></td>
				</tr>
				<tr>
					<th><label><?php BSMlmAffiPointUtils::e( 'description' ); ?></label></th>
					<td><textarea name="target_code_description" id="target_code_description" rows="6" class="regular-text"><?php echo esc_attr( $bs_affiliate_main_obj->get_option_value("target_code_description") ); ?></textarea></td>
				</tr>
				<?php 
					include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					if(is_plugin_active( 'simple-membership/simple-wp-membership.php' )) {
						$swpm_level_lists = SwpmMembershipLevelUtils::get_all_membership_levels_in_array(); 
				?>
					<tr>
						<th><label><?php BSMlmAffiPointUtils::e( 'Referral code issuance member rank' ); ?></label></th>
						<td>
							<?php foreach ( $swpm_level_lists as $id => $swpm_level ) { ?>
								<label><input type="checkbox" name="source_code_level[]" value="<?php echo($id);?>" <?php if(in_array($id, $arr_source_code_level)) { echo('checked="checked"'); } ?>　/><?php echo($swpm_level);?><br/>
							<?php } ?>
						</td>
					</tr>
					<tr>
						<th><label><?php BSMlmAffiPointUtils::e( 'MLM membership rank' ); ?></label></th>
						<td>
							<?php foreach ( $swpm_level_lists as $id => $swpm_level ) { ?>
								<label><input type="checkbox" name="mlm_member_level[]" value="<?php echo($id);?>" <?php if(in_array($id, $arr_mlm_member_level)) { echo('checked="checked"'); } ?>　/><?php echo($swpm_level);?><br/>
							<?php } ?>
							<p>Only the checked member ranks will be displayed and placed on the tree and will be eligible for benefits (points).</p>
						</td>
					</tr>
				<?php }?>
			</tbody>
		</table>

		<input type="submit" style="padding:10px 30px;" class="bs_affiliate_button-ml ajax_button" value="<?php BSMlmAffiPointUtils::e( 'Save settings' ); ?>">
	</div>
</div>
</form>