<?php

function wowprogress_admin_init(){
	register_setting( WOWPROGRESS_PLUGIN_SLUG.'_plugin_options', WOWPROGRESS_PLUGIN_SLUG.'_options', 'wowprogress_validate_options' );
}
add_action('admin_init', 'wowprogress_admin_init' );


function wowprogress_add_options_page() {
	add_options_page(WOWPROGRESS_PLUGIN_NAME . " " . __('Settings', 'wowprogress'), WOWPROGRESS_PLUGIN_NAME, 'manage_options', __FILE__, 'wowprogress_render_form');
}
add_action('admin_menu', 'wowprogress_add_options_page');


function wowprogress_render_form() {
	?>
	<div class="wrap">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h2><?php echo WOWPROGRESS_PLUGIN_NAME . " " . __('Settings', 'wowprogress') ?></h2>

		<!-- Beginning of the Plugin Options Form -->
		<form method="post" action="options.php">
			<?php settings_fields(WOWPROGRESS_PLUGIN_SLUG.'_plugin_options'); ?>
			<?php $options = get_option(WOWPROGRESS_PLUGIN_SLUG.'_options'); ?>

			<!-- Table Structure Containing Form Controls -->
			<!-- Each Plugin Option Defined on a New Table Row -->
			<table class="form-table">

				<tr>
					<th scope="row"><?php _e('Theme', 'wowprogress') ?></th>
					<td>
						<select name='wowprogress_options[theme]'>
							<?php foreach(wow_progress_themes() as $theme){
							echo "<option value='$theme' " . selected($theme, $options['theme']) . ">$theme</option>";
							}?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e('Show Backgrounds', 'wowprogress') ?></th>
					<td>
						<input name="wowprogress_options[show_backgrounds]" type="checkbox" value="1" <?php if (isset($options['show_backgrounds'])) { checked('1', $options['show_backgrounds']); } ?> />
					</td>
				</tr>

                <?php
                    $availableRaids = wowprogress_widget::load_raids_file(WOWPROGRESS_RAIDS_FILE);
                ?>
                <tr valign="top">
                    <th scope="row"><?php _e('Enabled Raids', 'wowprogress') ?></th>
                    <td>
                        <?php foreach($availableRaids as $raid){ ?>
                            <input type="checkbox" name="wowprogress_options[show_raid][<?php echo $raid['tag'];?>]" value="1" <?php if (isset($options['show_raid'][$raid['tag']])) { checked('1', $options['show_raid'][$raid['tag']]); } ?>/>
                            <?php echo $raid['name']?><br />
                        <?php } ?>
                    </td>
                </tr>

            </table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'wowprogress') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

function wowprogress_validate_options($input) {
	return $input;
}

?>