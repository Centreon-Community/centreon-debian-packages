<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/metaService/meta_host.php $
 * SVN : $Id: meta_host.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
 		exit();

	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_host.cfg", $oreon->user->get_name());
	$str = NULL;
	
	# Init
	
	$nb = 0;
	
	# Host Creation
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	$nb = $DBRESULT->numRows();
	
	if ($nb){
		$str .= "define host{\n";
		$str .= print_line("host_name", "_Module_Meta");
		$str .= print_line("alias", "Meta Service Calculate Module For Centreon");
		$str .= print_line("address", "127.0.0.1");
		$str .= print_line("check_command", "check_host_alive");
		$str .= print_line("max_check_attempts", "3");
		$str .= print_line("check_interval", "1");
		$str .= print_line("active_checks_enabled", "0");
		$str .= print_line("passive_checks_enabled", "0");
		$str .= print_line("check_period", "meta_timeperiod");
		# Contact Group
		$str .= print_line("contact_groups", "meta_contactgroup");
		$str .= print_line("notification_interval", "60");
		$str .= print_line("notification_period", "meta_timeperiod");
		$str .= print_line("notification_options", "d");
		$str .= print_line("notifications_enabled", "0");
		$str .= print_line("register", "1");
		$str .= "\t}\n\n";
	}	
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_hosts.cfg");
	fclose($handle);
	unset($str);
?>