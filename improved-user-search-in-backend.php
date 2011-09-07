<?php
/*
Plugin Name: Improved User Search in Backend
Version: 1.0.1
Description:  This Plugin improves the search for users in WordPress backend significantly. It empowers admins to search for the first name, last name and email address of users instead of only their nicknames/nicenames.
Plugin URI: http://www.blackbam.at/blackbams-blog/2011/06/27/wordpress-improved-user-search-first-name-last-name-email-in-backend/
Author: David Stöckl
Author URI: http://www.blackbam.at/
*/

/*

	Copyright (c) David Stöckl <david.stoeckl@blackbam.at>
	
	Released and distributed under the GPL, according to the WordPress Codex.

	

*/

  /* version check */
  global $wp_version;
  
  $exit_msg='Improved User Search in Backend requires WordPress version 3.0 or higher. <a href="http://codex.wordpress.org/Upgrading_Wordpress">Please update!</a>';
  
  if(version_compare($wp_version,"3.0","<")) {
  	exit ($exit_msg);
  }

if(is_admin()) {
    add_action('pre_user_query', 'user_search_by_multiple_parameters');
   
    function user_search_by_multiple_parameters($wp_user_query) {
        if(false === strpos($wp_user_query -> query_where, '@') && !empty($_GET["s"])) {
   
            global $wpdb;
   
            $uids=array();
   
            $usermeta_affected_ids = $wpdb -> get_results("SELECT DISTINCT user_id FROM " . $wpdb -> prefix . "usermeta WHERE (meta_key='first_name' OR meta_key='last_name') AND meta_value LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'");
   
            foreach($usermeta_affected_ids as $maf) {
                array_push($uids,$maf->user_id);
            }
   
            $users_affected_ids = $wpdb -> get_results("SELECT DISTINCT ID FROM " . $wpdb -> prefix . "users WHERE user_nicename LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%' OR user_email LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'");
   
            foreach($users_affected_ids as $maf) {
                if(!in_array($maf->ID,$uids)) {
                    array_push($uids,$maf->ID);
                }
            }
   
            $id_string = implode(",",$uids);
       
            $wp_user_query -> query_where = str_replace("user_nicename LIKE '%" . mysql_real_escape_string($_GET["s"]) . "%'", "ID IN(" . $id_string . ")", $wp_user_query -> query_where);
        }
        return $wp_user_query;
    }
}

?>