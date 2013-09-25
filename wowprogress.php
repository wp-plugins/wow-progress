<?php
/**
 * Plugin Name: WoW Progress
 * Description: A widget that helps to display guild raid progress.
 * Author: freevision.sk
 * Version: 1.3.0
 * Author URI: http://www.freevision.sk
 * Text Domain: wowprogress
 */
/**
 * Copyright (C) 2013  Montas, (Valter Martinek) (email : montas@freevision.sk)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/.
 */
define( 'WOWPROGRESS_VERSION', '1.1.0' );
if ( ! defined( 'WOWPROGRESS_PLUGIN_SLUG' ) )	define( 'WOWPROGRESS_PLUGIN_SLUG',	'wowprogress');
if ( ! defined( 'WOWPROGRESS_PLUGIN_NAME' ) )	define( 'WOWPROGRESS_PLUGIN_NAME',	'WoW Progress');
if ( ! defined( 'WOWPROGRESS_PLUGIN_DIR' ) )	define( 'WOWPROGRESS_PLUGIN_DIR',	WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
if ( ! defined( 'WOWPROGRESS_PLUGIN_URL' ) )	define( 'WOWPROGRESS_PLUGIN_URL',	WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) );

if ( ! defined( 'WOWPROGRESS_THEMES_FOLDER' ) )	define( 'WOWPROGRESS_THEMES_FOLDER','themes' );

if ( ! defined( 'WOWPROGRESS_RAIDS_FILE' ) )	define( 'WOWPROGRESS_RAIDS_FILE',	WOWPROGRESS_PLUGIN_URL.'/raids.json' );
if ( ! defined( 'WOWPROGRESS_EXPANSIONS' ) )	define( 'WOWPROGRESS_EXPANSIONS',	WOWPROGRESS_PLUGIN_URL.'/images/exp/%s.png' );
if ( ! defined( 'WOWPROGRESS_RAIDS' ) )			define( 'WOWPROGRESS_RAIDS',		WOWPROGRESS_PLUGIN_URL.'/images/raids/%s.png' );
if ( ! defined( 'WOWPROGRESS_HC_ICON' ) )		define( 'WOWPROGRESS_HC_ICON',		WOWPROGRESS_PLUGIN_URL.'/images/heroic_icon.png' );
if ( ! defined( 'WOWPROGRESS_VIDEO_ICON' ) )	define( 'WOWPROGRESS_VIDEO_ICON',	WOWPROGRESS_PLUGIN_URL.'/images/video_icon.png' );
if ( ! defined( 'WOWPROGRESS_ACHI' ) )			define( 'WOWPROGRESS_ACHI',			'<a href="http://www.wowhead.com/achievement=%d?who=%s&when=%d">%s</a>' );

$nice_code = false;
if($nice_code){
	define('NL', "\n");
	define('TAB', "  ");
}
else{
	define('NL', "");
	define('TAB', "");
}

class wowprogress_widget extends WP_Widget {

	private $WoWraids;

	function wowprogress_widget(){
		$widget_ops = array('classname' => WOWPROGRESS_PLUGIN_SLUG, 'description' => 'WoW Progress Widget' );
		parent::WP_Widget(false, $name = 'WoW Progress', $widget_ops);
		$this->WoWraids = $this->load_raids_file(WOWPROGRESS_RAIDS_FILE);
	}

	function widget($args, $instance){
		extract($args, EXTR_SKIP);
		$options = get_option(WOWPROGRESS_PLUGIN_SLUG.'_options');

		echo $before_widget;
		if ( !empty( $instance['title'] ) )
			echo $before_title . $instance['title'] . $after_title;

		// Start widget
		echo NL.NL;
		echo '<div id="wowprogress">'.NL.NL;

		$exp = "";
		foreach ($this->WoWraids as $raid) {
            // Skip if raid is disabled in settings
            if(!isset($options['show_raid'][$raid['tag']]) || $options['show_raid'][$raid['tag']] != '1') continue;
			// Skip if raid is not shown
			if(!$instance[$raid['tag']."_show"]) continue;

			// Output expansion header and start raid list if expansion is different from previous
			if ($exp != $raid['exp']){
				// If not first, close previous
				if ($exp != "") echo TAB.'</ul> <!-- .expansion -->'.NL.NL;

				// Set new
				$exp = $raid['exp'];

				// Output header
				echo TAB.'<div class="expansion_head"><img src="' . sprintf(WOWPROGRESS_EXPANSIONS, $exp) . '" /></div>'.NL;

				// Start raids list
				echo TAB.'<ul class="expansion">'.NL;
			}

			// Start raid
			echo TAB.TAB.'<li class="raid"'.($options['show_backgrounds'] ? 'style="background-image: url(\'' . sprintf(WOWPROGRESS_RAIDS, $raid['background']) . '\');"' : '') .'>'.NL;

			// Check if raid is complete
			$complete = true;
			$complete_hc = true;
			foreach($raid['bosses'] as $bossid => $boss){
				$complete &= $instance[$raid['tag']."_".$bossid] == "on";
				$complete_hc &= $instance[$raid['tag']."_".$bossid."_hc"] == "on";
			}

			// Background overlay for background image lightness correction
			echo TAB.TAB.TAB.'<div class="raid_film">'.NL;
			
			// Start raid header
			echo TAB.TAB.TAB.TAB.'<div class="raid_head'.($complete_hc ? " hc" : "").'">';

			if($complete && $instance["guild"] != "" && $instance[$raid['tag']."_time"] != "")
				printf(WOWPROGRESS_ACHI, $raid['achievement'], $instance["guild"], $instance[$raid['tag']."_time"], $raid['name']);
			else
				echo $raid['name'];

			// End raid header
			echo '</div>'.NL;

			// Start boss list
			echo TAB.TAB.TAB.TAB.'<ul'.($instance[$raid['tag']."_expand"] ? "" : ' style="display: none"') . '>'.NL;

			// Output each boss
			foreach($raid['bosses'] as $bossid => $boss){
                echo TAB.TAB.TAB.TAB.TAB.'<li'.($instance[$raid['tag']."_".$bossid] == "on" ? ($instance[$raid['tag']."_".$bossid."_hc"] == "on" ? ' class="down hc"' : ' class="down"') : "").'>';
                echo $boss;
                if($instance[$raid['tag']."_".$bossid."_vid"] != ""){
                    echo '<a class="video_link" href="'.$instance[$raid['tag']."_".$bossid."_vid"].'"><img src="'.WOWPROGRESS_VIDEO_ICON.'" /></a>';
                }
                echo '</li>'.NL;
            }

			// End boss list
			echo TAB.TAB.TAB.TAB.'</ul>'.NL;
			
			// End raid background film
			echo TAB.TAB.TAB.'</li> <!-- .raid_film -->'.NL;

			// End raid
			echo TAB.TAB.'</li> <!-- .raid -->'.NL;
		}

		// If any exp was output, close it
		if ($exp != "")
			echo TAB.'</ul> <!-- .expansion -->'.NL.NL;

		// End widget
		echo '</div> <!-- #wowprogress -->'.NL;
		echo $after_widget;
	}

	function update($new_instance, $old_instance ){
		$instance = $old_instance;

		$instance['title']	          = strip_tags($new_instance['title']);
		$instance['guild']	          = strip_tags($new_instance['guild']);

		foreach ($this->WoWraids as $raid) {
			$instance[$raid['tag']."_time"]   = $new_instance[$raid['tag']."_time"];
			$instance[$raid['tag']."_show"]   = $new_instance[$raid['tag']."_show"];
			$instance[$raid['tag']."_expand"] = $new_instance[$raid['tag']."_expand"];

			foreach ($raid['bosses'] as $boss_id => $bossname) {
				$instance[$raid['tag']."_".$boss_id]        = $new_instance[$raid['tag'].'_'.$boss_id];
				$instance[$raid['tag']."_".$boss_id."_hc"]  = $new_instance[$raid['tag']."_".$boss_id."_hc"];
                $instance[$raid['tag']."_".$boss_id."_vid"] = $new_instance[$raid['tag'].'_'.$boss_id."_vid"];
			}
		}

		return $instance;
	}

	function form($instance){
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title' => 'Progress'
			)
		);

		$this->print_form_fields($instance);
	}

	function print_form_fields($instance){
        $options = get_option(WOWPROGRESS_PLUGIN_SLUG.'_options');

        echo '<table>';

		echo '<thead><tr><th colspan="3"></th></tr></thead>';

		echo '<tbody>';
		echo $this->form_text_input("title", __("Title", "wowprogress"), esc_attr($instance['title']));
		echo $this->form_text_input("guild", __("Guild", "wowprogress"), esc_attr($instance['guild']), __("Name of your guild.\nThis will be used in achievement link.", "wowprogress"));
		echo '<tr><td colspan="3"><hr /></td></tr>';
		echo '</tbody>';

		foreach ($this->WoWraids as $raid) {
            if(!isset($options['show_raid'][$raid['tag']]) || $options['show_raid'][$raid['tag']] != '1') continue;

			echo '<thead><tr><th colspan="3">'.$raid['name'].'</th></tr></thead>';

			echo '<tbody>';
			echo $this->form_checkbox_input($raid['tag']."_show", __("Show", "wowprogress"), $instance[$raid['tag']."_show"]);
			echo $this->form_checkbox_input($raid['tag']."_expand", __("Open", "wowprogress"), $instance[$raid['tag']."_expand"]);
			echo '</tbody>';

			echo '<thead><tr><th>N</th><th>HC</th><th>Boss</th></tr></thead>';
			echo '<tbody>';

			foreach ($raid['bosses'] as $boss_id => $boss_name)
				echo $this->form_boss($raid['tag']."_".$boss_id, $boss_name, $instance);

			echo $this->form_text_input($raid['tag']."_time", __("Time", "wowprogress"), $instance[$raid['tag']."_time"], __("Time when guild achieved guild run achievement.\nShould be in unix micro time (ei. 1304035200000).", "wowprogress"));
			echo '<tr><td colspan="3"><hr /></td></tr>';

			echo '</tbody>';
		}
		echo '</table>';
	}

	function form_checkbox($id, $state){
		return '<input type="checkbox" id="'.$this->get_field_id($id).'" name="'.$this->get_field_name($id).'"'.($state == "on" ? " checked" : "").'>&nbsp;';
	}

	function form_label($id, $label){
		return '<label for="'.$this->get_field_id($id).'">'.$label.'</label>';
	}

	function form_text($id, $value, $title = ""){
		return '<input type="text" class="widefat" id="'.$this->get_field_id($id).'" name="'.$this->get_field_name($id).'" value="'.$value.'" title="'.$title.'" />';
	}

	function form_checkbox_input($id, $label, $state){
		$res = "";
		$res .= '<tr>';
		$res .= '<td></td>';
		$res .= '<td>'.$this->form_checkbox($id, $state).'</td>';
		$res .= '<td>'.$this->form_label($id, $label).'</td>';
		$res .= '</tr>';
		return $res;
	}

	function form_text_input($id, $label, $value, $title = ""){
		$res = "";
		$res .= '<tr>';
		$res .= '<td colspan="2">'.$this->form_label($id, $label).'</td>';
		$res .= '<td>'.$this->form_text($id, $value, $title).'</td>';
		$res .= '</tr>';
		return $res;
	}

    function form_link_input($id, $label, $value, $title = ""){
        $res = "";
        $res .= '<tr>';
        $res .= '<td>'.$this->form_label($id, $label).'</td>';
        $res .= '<td colspan="2">'.$this->form_text($id, $value, $title).'</td>';
        $res .= '</tr>';
        return $res;
    }

    function form_boss($boss_id, $boss_name, $instance){
		$boss_id_hc = $boss_id."_hc";

		$res = "";
		$res .= '<tr>';
		$res .= '<td>'.$this->form_checkbox($boss_id, $instance[$boss_id]).'</td>';
		$res .= '<td>'.$this->form_checkbox($boss_id_hc, $instance[$boss_id_hc]).'</td>';
		$res .= '<td>'.$this->form_label($boss_id, $boss_name).'</td>';
		$res .= '</tr>';
        $res .= $this->form_link_input($boss_id.'_vid', '<img style="vertical-align: middle" src="'.WOWPROGRESS_VIDEO_ICON.'"/>', $instance[$boss_id."_vid"], __("URL to video.", "wowprogress"));

        return $res;
	}

	function load_raids_file($path){
		return json_decode(file_get_contents($path), true);
	}

}

function wow_progress_themes(){
	$themes = array();
	$files = glob(WOWPROGRESS_PLUGIN_DIR . '/' . WOWPROGRESS_THEMES_FOLDER . "/*.css");
	$themes = array_map('basename', $files);
	return $themes;
}


if (!function_exists('wowprogress_widget_install')) {
    function wowprogress_widget_install() {
		$tmp = get_option(WOWPROGRESS_PLUGIN_SLUG.'_options');
		if(!is_array($tmp)) {
			delete_option(WOWPROGRESS_PLUGIN_SLUG.'_options');
			$arr = array(
				"show_backgrounds" => "1",
				"theme" => "light.css",
                "show_raid" => array(
                    "soo" => "1",
                    "tot" => "1"
                )
			);
			update_option(WOWPROGRESS_PLUGIN_SLUG.'_options', $arr);
		}
    }
}
register_activation_hook(__FILE__, 'wowprogress_widget_install');


if (!function_exists('wowprogress_widget_uninstall')) {
	function wowprogress_widget_uninstall() {
		delete_option(WOWPROGRESS_PLUGIN_SLUG.'_options');
	}
}
register_uninstall_hook(__FILE__, 'wowprogress_widget_uninstall');


function wowprogress_init(){
	load_plugin_textdomain('wowprogress', false, WOWPROGRESS_PLUGIN_SLUG."/languages/");

	/* Plugin scripts */
	wp_enqueue_script('jquery');

	wp_register_script('wowhead', 'http://static.wowhead.com/widgets/power.js');
	wp_enqueue_script('wowhead');

	wp_register_script(WOWPROGRESS_PLUGIN_SLUG, WOWPROGRESS_PLUGIN_URL.'/wowprogress.js');
	wp_enqueue_script(WOWPROGRESS_PLUGIN_SLUG);

	/* Plugin theme */
	wp_register_style(WOWPROGRESS_PLUGIN_SLUG, WOWPROGRESS_PLUGIN_URL.'/'.WOWPROGRESS_PLUGIN_SLUG.'.css');
	wp_enqueue_style(WOWPROGRESS_PLUGIN_SLUG);

	$options = get_option(WOWPROGRESS_PLUGIN_SLUG.'_options');
	wp_register_style(WOWPROGRESS_PLUGIN_SLUG.'_theme', WOWPROGRESS_PLUGIN_URL.'/'.WOWPROGRESS_THEMES_FOLDER.'/'.$options['theme']);
	wp_enqueue_style(WOWPROGRESS_PLUGIN_SLUG.'_theme');
}
add_action('plugins_loaded', 'wowprogress_init');


function wowprogress_init_widget(){
	return register_widget('wowprogress_widget');
}
add_action('widgets_init', 'wowprogress_init_widget');


if (is_admin()) {
	include 'inc/admin.php';
}