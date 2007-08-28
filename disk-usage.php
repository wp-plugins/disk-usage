<?php
/*
Plugin Name: Disk Usage
Plugin URI: http://wordpress.designpraxis.at
Description: Displays disk space used by your WordPress installation.
Version: 1.1
Author: Roland Rust
Author URI: http://wordpress.designpraxis.at
*/

/* 
Changelog:

Changes in 1.1

- bug fixed for script loading with  
	add_action('admin_print_scripts-manage_page_disk-usage/disk-usage', 'dprx_du_loadjs');
*/

add_action('init', 'dprx_du_init_locale',98);
function dprx_du_init_locale() {
	$locale = get_locale();
	$mofile = dirname(__FILE__) . "/locale/".$locale.".mo";
	load_textdomain('dprx_du', $mofile);
}

add_action('admin_menu', 'dprx_du_add_admin_pages');

function dprx_du_add_admin_pages() {
	add_submenu_page('edit.php', 'Disk Usage', 'Disk Usage', 10, __FILE__, 'dprx_du_manage_page');
}

function dprx_du_check_dir($dir) {
	$command = "du -s ".$dir;
	exec($command,$res);
	$sum = explode("\t",$res[0]);
	return dprx_du_format_size($sum[0]);
}

function dprx_du_check_dir_bytes($dir) {
	$command = "du -s ".$dir;
	exec($command,$res);
	$sum = explode("\t",$res[0]);
	return $sum[0];
}

function dprx_du_format_size($rawSize) {
    $rawSize = $rawSize*1024;
    if ($rawSize / 1048576 > 1) 
        return round($rawSize/1048576, 1) . ' MB'; 
    else if ($rawSize / 1024 > 1) 
        return round($rawSize/1024, 1) . ' KB'; 
    else 
        return round($rawSize, 1) . ' bytes';
}


wp_enqueue_script('prototype');
/* This is a workaround for WordPress bug http://trac.wordpress.org/browser/trunk/wp-admin/admin-header.php?rev=5640 */
if (eregi("disk-usage",$_GET['page'])) {
add_action('admin_print_scripts', 'dprx_du_loadjs');
}
function dprx_du_loadjs() {
	?>
	<script type="text/javascript">
	function dprx_du_js() {
		dprxu = new Ajax.Updater(
		'dprx_diskusage',
		'<?php bloginfo("wpurl"); ?>/wp-admin/edit.php?page=<?php echo $_REQUEST['page']; ?>',
			{method: 'get', 
			 parameters:'dprx_du_ajax=1',
			 evalScripts: true}
		);
	}
	</script>
	<?php
}

add_action('init', 'dprx_check_disk_usage',99);
function dprx_check_disk_usage() {
	if (!empty($_REQUEST['dprx_du_ajax'])) {
		if(dprx_du_check_dir_bytes(ABSPATH) < "1024") {
		?>
		<p><?php _e('Unfortunately, you cannot use this plugin. It uses the du (disk usage) command only available in Unix and Mac OSX environments, if php is allowed to execute it.', 'dprx_du') ?></p>
		<?php
		exit;
		}
		?>
		<p>
		<?php _e('Your WordPress installation uses', 'dprx_du') ?>
		<b><?php echo dprx_du_check_dir(ABSPATH); ?></b>
		<?php _e('of disk space.', 'dprx_du') ?>
		</p>
		<?php
		$dirarray = array();
		if ($handle = opendir(ABSPATH)) {
		    while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir(ABSPATH.$file)) {
					$path = ABSPATH.$file;
					$dirarray[$path] = dprx_du_check_dir_bytes($path);
				}
			}
		    }
		    closedir($handle);
		}
		?>
		<p>
		<?php _e('Your WordPress installation contains', 'dprx_du') ?>
		<b><?php echo count($dirarray); ?></b>
		<?php _e('directories:', 'dprx_du') ?>
		</p>
		<?php
		foreach ($dirarray as $d => $b) {
			if ($biggest < $b) {
			$biggest = $b;
			$biggest_dir = $d;
			}
			$dirname = str_replace(ABSPATH,"",$d);
			echo "<b>".dprx_du_check_dir($d)."</b> ".$dirname."<br />";
		}
		?>
		<p>
		<?php _e('The directory containing the biggest files and largest subdirectories is', 'dprx_du') ?>
		<b><?php echo str_replace(ABSPATH,"",$biggest_dir); ?></b>
		</p>
		<?php
		$command = "du -a ".$biggest_dir." | sort -n";
		exec($command,$res);
		$res = array_reverse($res);
		$i=0;
		foreach($res as $r) {
			$dat = explode("\t",$r);
			echo "<b>".dprx_du_format_size($dat[0])."</b> ".str_replace(ABSPATH,"",$dat[1])."<br />";
			$i++;
			if ($i > 50) { break; }
		}
		?>
		<p>etc.</p>
		<?php
	exit;
	}
}
function dprx_du_manage_page() {
	?>
	<div class=wrap>
		<h2><?php _e('Disk Usage') ?></h2>
	<div id="dprx_diskusage"><?php _e('Checking disk usage. Please wait.','dprx_du') ?></div>
	</div>
	<div class="wrap">
		<p>
		<?php _e("Running into Troubles? Features to suggest?","dprx_du"); ?>
		<a href="http://wordpress.designpraxis.at/">
		<?php _e("Drop me a line","dprx_du"); ?> &raquo;
		</a>
		</p>
		<div style="display: block; height:30px;">
			<div style="float:left; font-size: 16px; padding:5px 5px 5px 0;">
			<?php _e("Do you like this Plugin?","dprx_du"); ?>
			<?php _e("Consider to","dprx_du"); ?>
			</div>
			<div style="float:left;">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_xclick">
			<input type="hidden" name="business" value="rol@rm-r.at">
			<input type="hidden" name="no_shipping" value="0">
			<input type="hidden" name="no_note" value="1">
			<input type="hidden" name="currency_code" value="EUR">
			<input type="hidden" name="tax" value="0">
			<input type="hidden" name="lc" value="AT">
			<input type="hidden" name="bn" value="PP-DonationsBF">
			<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but21.gif" border="0" name="submit" alt="Please donate via PayPal!">
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			</div>
		</div>
	</div>
	 <script type="text/javascript">
	 	setTimeout("dprx_du_js()",3000);
	 </script>
	<?php
}
?>
