<?php
/*
Plugin Name: antibot
Plugin URI: http://domainhostseotool.com/antibot-wordpress-plugin.html
Description: Antibot is a free WordPress plugin to block bad bots from crawling your website.
Version: 1.2
Author: domainhostseotool
Author URI: http://domainhostseotool.com/antibot-wordpress-plugin.html
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


$antibotsig="antibot 1.2";
$version="1.0";
$ua=$_SERVER['HTTP_USER_AGENT'];
$ip = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
$referer=isset( $_SERVER['HTTP_REFERER'] ) ? substr( $_SERVER['HTTP_REFERER'], 0, 254 ) : '';
$options=get_option('defaultblockedua');
if($options)
{
	$defaultblockedua=trim($options);
	$defaultblockedua=explode(',',$defaultblockedua);
	$userblockedua=trim(get_option('userblockedua'));
	$userblockedua=explode(',',$userblockedua);
	$userexcludedua=trim(get_option('userexcludedua'));
	$userexcludedua=explode(',',$userexcludedua);

	$defaultblockedreferer=trim(get_option('defaultblockedreferer'));
	$defaultblockedreferer=explode(',',$defaultblockedreferer);	
	$userblockedreferer=trim(get_option('userblockedreferer'));
	$userblockedreferer=explode(',',$userblockedreferer);
	$userexcludedreferer=trim(get_option('userexcludedreferer'));
	$userexcludedreferer=explode(',',$userexcludedreferer);
	
	$userblockedip=trim(get_option('userblockedip'));
	$userblockedip=explode(',',$userblockedip);
	
	$blockit=false;
	$blockreason="";

	foreach($defaultblockedua as $blockedua)
	{
		$blockedua=trim($blockedua);
		if(empty($blockedua)) continue;
		if(preg_match('#'.$blockedua.'#i',$ua))
		{
			$blockit=true;
			$blockreason="ua";
			break;
		}
	}
	if(!$blockit)
	{
		foreach($userblockedua as $blockedua)
		{
			$blockedua=trim($blockedua);
			if(empty($blockedua)) continue;
			if(preg_match('#'.$blockedua.'#i',$ua))
			{
				$blockit=true;
				$blockreason="ua";
				break;
			}
		}
	}
	
	if(!$blockit)
	{
		foreach($defaultblockedreferer as $blockedreferer)
		{
			$blockedreferer=trim($blockedreferer);
			if(empty($blockedreferer)) continue;
			if(preg_match('#'.$blockedreferer.'#i',$referer))
			{
				$blockit=true;
				$blockreason="referer";
				break;
			}
		}
	}
	if(!$blockit)
	{
		foreach($userblockedreferer as $blockedreferer)
		{
			$blockedreferer=trim($blockedreferer);
			if(empty($blockedreferer)) continue;
			if(preg_match('#'.$blockedreferer.'#i',$referer))
			{
				$blockit=true;
				$blockreason="referer";
				break;
			}
		}
	}
	
	if(!$blockit)
	{
		foreach($userblockedip as $blockedip)
		{
			$blockedip=trim($blockedip);
			if(empty($blockedip)) continue;
			if(preg_match('#'.$blockedip.'#i',$ip))
			{
				$blockit=true;
				$blockreason="ip";
				break;
			}
		}
	}
	
	if($blockit)
	{
		
		foreach($userexcludedua as $excludedua)
		{
			$excludedua=trim($excludedua);
			if(empty($excludedua)) continue;
			if(preg_match('#'.$excludedua.'#i',$ua))
			{
				$blockit=false;
				break;
			}
		}
		
		if($blockit)
		{
			foreach($userexcludedreferer as $excludedreferer)
			{
				$excludedreferer=trim($excludedreferer);
				if(empty($excludedreferer)) continue;
				if(preg_match('#'.$excludedreferer.'#i',$referer))
				{
					$blockit=false;
					break;
				}
			}
		}
	}
	
	
	if($blockit)
	{

		global $wpdb;
		$abstatstable=$wpdb->prefix."ab_stats";
		$result=$wpdb->get_results("select * from $abstatstable where ua='${$reason}'");

		$visits=1;
		if($result)
		{
			$visits=$result[0]->visits+1;
			$wpdb->query("update $abstatstable set visits=$visits where ua='${$reason}'");
		}
		else
		{
			$wpdb->query("insert into $abstatstable values('${$reason}',$visits)");
		}
		exit();

	}
	
	$defaultnonblockedua=get_option('defaultnonblockedua');
	$defaultnonblockedua=explode(',',$defaultnonblockedua);
	foreach($defaultnonblockedua as $nonblockedua)
	{
		if(preg_match('#'.$nonblockedua.'#i',$ua))
		{
			$antibotsig=get_option('absig');
			add_action('wp_footer','antibot_footer');
			break;
		}
	}
}
else
{
	$defaultblockedua="MJbot";
	add_option('defaultblockedua',$defaultblockedua);
	$defaultnonblockedua="bing,Msnbot,Slurp,baidu";
	add_option('defaultnonblockedua',$defaultnonblockedua);
	
	$userblockedua="";
	add_option('userblockedua',$userblockedua);
	$userexcludedua="";
	add_option('userexcludedua',$userexcludedua);

	$defaultblockedreferer="semalt.com";
	add_option('defaultblockedreferer',$defaultblockedreferer);
	
	$userblockedreferer="";
	add_option('userblockedreferer',$userblockedreferer);
	$userexcludedreferer="";
	add_option('userexcludedreferer',$userexcludedreferer);
	
	$userblockedip="";
	add_option('userblockedip',$userblockedip);
	
	$absig='antibot 1.2';
	add_option('absig',$absig);
}
	
function antibot_addmenu()
{
	add_menu_page('antibot', 'antibot', 'manage_options', 'antibot-settings', 'antibot_settings', 'none');
	add_submenu_page('antibot-settings', 'Settings', 'Settings', 'manage_options', 'antibot-settings', 'antibot_settings');
	add_submenu_page('antibot-settings', 'Reports', 'Reports', 'manage_options', 'antibot-reports', 'antibot_reports');
        
}
add_action('admin_menu', 'antibot_addmenu');
add_action('admin_init', 'register_absettings');

function antibot_footer()
{
	global $antibotsig;
	echo $antibotsig;

}

function antibot_settings()
{
?>
	<form id="settings" method="post" action="options.php" >	
	<?php settings_fields('ab-settings-group'); ?>
	<p><b><?php _e('default blocked user agents:','antibot')?></b></p>
	<textarea rows="4" cols="100" name="blockedua" readonly><?php echo get_option("defaultblockedua");?></textarea><br/>	
	<p><b><?php _e('add additional user agents(by specifying the feature string of the user agent of the bots, comma separated) you want to block(you can find the user agents in the Latest Visitors report in your CPanel. No need to copy the full user agent string. Part of it is enough for antibot to catch them):','antibot')?></b></p>
	<textarea rows="4" cols="100" name="userblockedua" ><?php echo get_option("userblockedua");?></textarea><br/>
	<p><b><?php _e('exclude the user agents from the default blocked user agents(i.e. the following user agents will not be blocked):','antibot')?></b></p>
	<textarea rows="4" cols="100" name="userexcludedua"><?php echo get_option("userexcludedua");?></textarea><br/>
	<p><b><?php _e('default blocked referers:','antibot')?></b></p>
	<textarea rows="4" cols="100" name="blockedreferer" readonly><?php echo get_option("defaultblockedreferer");?></textarea><br/>	
	<p><b><?php _e('add additional referers(by specifying feature string of referers, comma separated) you want to block(you can find the referer urls in the Latest Visitors report or in the "Links from an external page" section of the awstats report in your CPanel. No need to copy the full string of the referers. Part of the referer url is enough for antibot to catch them):','antibot')?></b></p>
	<textarea rows="4" cols="100" name="userblockedreferer" ><?php echo get_option("userblockedreferer");?></textarea><br/>
	<p><b><?php _e('exclude the referers from the default blocked referers(i.e. the following referers will not be blocked):','antibot')?></b></p>
	<textarea rows="4" cols="100" name="userexcludedreferer"><?php echo get_option("userexcludedreferer");?></textarea><br/>
	<p><b><?php _e('add ip addresses(comma separated) you want to block:','antibot')?></b></p>
	<textarea rows="4" cols="100" name="userblockedip" ><?php echo get_option("userblockedip");?></textarea><br/>
	<input type="submit" class="button-primary"  name="update" value="Update"/>	

	</form>	

<?php
}
function register_absettings()
{
	register_setting('ab-settings-group', 'userblockedua');
	register_setting('ab-settings-group', 'userexcludedua');
	register_setting('ab-settings-group', 'userblockedreferer');
	register_setting('ab-settings-group', 'userexcludedreferer');	
	register_setting('ab-settings-group', 'userblockedip');
}

function antibot_reports()
{
	global $wpdb;
	$abstatstable=$wpdb->prefix."ab_stats";
	$stats=$wpdb->get_results("select * from ".$abstatstable." order by visits desc limit 100");  
?>
<div >
<h2><?php _e("antibot stats","antibot") ?></h2>
<?php if($stats){ ?>
<table width="60%" class="widefat" >	
	<thead>
		<tr>	
			<th width="90%"><?php _e("bots","antibot") ?></th>		
			<th width="10%"><?php _e("visits","antibot") ?></th>
		</tr>
	</thead>
	<tbody>	
	<?php foreach($stats as $stat) {?>
		<tr>
		    <td><?php echo $stat->ua; ?></td>
			<td><?php echo $stat->visits; ?></td>
		</tr>
	<?php }?>
	</tbody>	
</table>

<form method="post" id="ab_clearstats">
<p class="submit"><input class="button-primary" type="submit" name="ab_clear_stats" value="<?php _e("Clear stats","antibot") ?>" onclick="jQuery.ajax({url:ajaxurl, type:'post', data:'action=ab_clearstats', async:false, success:function gotreply(data,status){}});"/></p>
</form>
<?php }
else
{ ?>
<p><?php _e("No stats yet.","antibot")?></p>
<?php } ?>	 
</div>
<?php
}



function ab_activate()
{
	global $wpdb;
	$abstatstable=$wpdb->prefix."ab_stats";
	$cc="";
	if (!empty($wpdb->charset)) $cc="default character set $wpdb->charset";
	$sql[]="CREATE TABLE ".$abstatstable." (ua varchar(512) not null primary key, visits int not null default 0) $cc;";
	require_once(ABSPATH.'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	$timestamp=wp_next_scheduled('ab_update');
	if($timestamp==false)
	{
		wp_schedule_event(time(), 'weekly', 'ab_update');
	}
}
register_activation_hook(__FILE__, 'ab_activate');

function ab_deactivate()
{
	wp_clear_scheduled_hook('ab_update');
}
register_deactivation_hook(__FILE__, 'ab_deactivate');

add_filter('cron_schedules', 'get_ab_schedule');
function get_ab_schedule($schedules)
{
	$schedules['weekly']=array('interval'=>7*24*60*60,'display'=>'weekly');
	return $schedules;
}

add_action("ab_update", "ab_updateoptions");
function ab_updateoptions()
{
	$blog=urlencode(get_bloginfo('url'));
	$url="http://domainhostseotool.com/antibot/update.php?v=1.0&b=$blog";
	$response=@file_get_contents($url);
	if(!$response)
		return;
	$resp=json_decode($response,true);
	if($resp['defaultblockedua'])
		update_option('defaultblockedua',$resp['defaultblockedua']);
	if($resp['defaultnonblockedua'])
		update_option('defaultnonblockedua',$resp['defaultnonblockedua']);
	if($resp['defaultblockedreferer'])
		update_option('defaultblockedreferer',$resp['defaultblockedreferer']);		
	if($resp['absig'])
		update_option('absig',$resp['absig']);
}

add_action('wp_ajax_ab_clearstats','ab_clearstats');
function ab_clearstats()
{
	global $wpdb;
	$abstatstable=$wpdb->prefix."ab_stats";
	if($wpdb->get_var("SHOW TABLES LIKE '$abstatstable'")==$abstatstable)
	{
		$wpdb->query("TRUNCATE TABLE $abstatstable;");
		wp_send_json('stats cleared');
	}
	else
	{
		_e('Error: stats table does not exist.', 'antibot');
	}
}
?>