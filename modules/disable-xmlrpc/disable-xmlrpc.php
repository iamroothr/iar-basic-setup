<?php
/**
 * Module: Disable XML-RPC
 * Description: Disables XML-RPC.
 */

if (!defined('ABSPATH')) exit;

add_filter('xmlrpc_enabled', '__return_false');
