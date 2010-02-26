<?php

	global $conf_centreon;

	/* Require database config */
	if (file_exists("/etc/centreon2/centreon2.db.conf.php")) {
		require_once '/etc/centreon2/centreon2.db.conf.php';
	}

	if (file_exists("/etc/centreon2/centstorage.db.conf.php")) {
		require_once '/etc/centreon2/centstorage.db.conf.php';
	}

	/* Path to classes */
	$classdir='./class';
	
	/* Centreon2 Path */
	$centreon_path='/usr/share/centreon2/';
?>
