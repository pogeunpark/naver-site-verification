<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
  die;
}

delete_option('sbnsv_key');
delete_option('sb_naver_analytics_id');

// for site options in Multisite
delete_site_option('sbnsv_key');
delete_site_option('sb_naver_analytics_id');
