<?php

//http://davidseah.com/code/archives/103
function install() {
	global $wpdb;
	$table_prefix = $wpdb->prefix."slides_";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$table = $table_prefix . "clients";
	$sql = "CREATE TABLE $table (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		client_ip mediumint(8) unsigned NOT NULL,
		client_name char,
		client_running boolean not null default 0,
		UNIQUE KEY id (id)
	);";
	$result = dbDelta($sql);

	$table = $table_prefix . "list";
	$sql = "CREATE TABLE $table (
		id mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		list_name CHAR(16),
		list_str VARCHAR(512),
		UNIQUE KEY id (id)
	);";
	$wpdb->insert($table, array(
		"list_name" => "Default",
		"list_str"  => ""
	));
	$result = dbDelta($sql);
	$wpdb->insert($table, array("list_name" => "Default", "list_str" => ""));
}
function uninstall() {
	global $wpdb;
	$table_prefix = $wpdb->prefix."slides_";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$sql = "delete from {$table_prefix}clients;";
	$result = dbDelta($sql);

	$sql = "delete from {$table_prefix}list;";
	$result = dbDelta($sql);
}

function update() {
	$args = array(
		"post_type"   => "post",
		"post_status" => "any"
	);
	$query = new WP_Query($args);
	for($i = 0; $i < $query->post_count; $i++) {
		$query->posts[$i]->post_type = "slides";
		wp_insert_attachment($query->posts[$i]);
	}
}
uninstall();
install();
?>
