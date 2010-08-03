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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/metaService/meta_services.php $
 * SVN : $Id: meta_services.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_services.cfg", $oreon->user->get_name());
	$str = NULL;
	
	$DBRESULT =& $pearDB->query("SELECT * FROM meta_service WHERE meta_activate = '1'");
	# Write Virtual Services For meta 
	while ($meta =& $DBRESULT->fetchRow())	{
		$strEval = NULL;
		$strEval .= "define service{\n";
		$strEval .= print_line("service_description", "meta_".$meta["meta_id"]);
		$strEval .= print_line("host_name", "_Module_Meta");
		$strEval .= print_line("check_command", "check_meta!" . $meta["meta_id"]);
		$strEval .= print_line("max_check_attempts", $meta["max_check_attempts"]);
		$strEval .= print_line("normal_check_interval", $meta["normal_check_interval"]);
		$strEval .= print_line("retry_check_interval", $meta["retry_check_interval"]);
		$strEval .= print_line("active_checks_enabled", "1");
		$strEval .= print_line("passive_checks_enabled", "0");
		
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT tp_name FROM timeperiod WHERE tp_id = '".$meta["check_period"]."' LIMIT 1");
		$period =& $DBRESULT2->fetchRow();
		if (isset($period) && $period["tp_name"])
			$strEval .= print_line("check_period", $period["tp_name"]);
		$DBRESULT2->free();
			
		$strEval .= print_line("notification_interval", $meta["notification_interval"]);
		
		$DBRESULT2 =& $pearDB->query("SELECT DISTINCT tp_name FROM timeperiod WHERE tp_id = '".$meta["notification_period"]."' LIMIT 1");
		$period =& $DBRESULT2->fetchRow();
		if (isset($period) && $period["tp_name"])
			$strEval .= print_line("notification_period", $period["tp_name"]);
		$DBRESULT2->free();
	
		$strEval .= print_line("notification_options", $meta["notification_options"]);
		if ($meta["notifications_enabled"] != 2) 
			print_line("notifications_enabled", $meta["notifications_enabled"] == 1 ? "1": "0");
		
		$contactGroup = array();
		$strTemp = NULL;
		$DBRESULT2 =& $pearDB->query("SELECT cg.cg_id, cg.cg_name FROM meta_contactgroup_relation mcgr, contactgroup cg WHERE mcgr.meta_id = '".$meta["meta_id"]."' AND mcgr.cg_cg_id = cg.cg_id ORDER BY `cg_name`");
		while ($contactGroup =& $DBRESULT2->fetchRow())	{
			if (isset($gbArr[1][$contactGroup["cg_id"]]))
				$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
		}
		$DBRESULT2->free();
		unset($contactGroup);
		
		if ($strTemp) 
			$strEval .= print_line("contact_groups", $strTemp);
		$strEval .= print_line("register", "1");
		$strEval .= "\t}\n\n";

		$str .= $strEval;
	}
	
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_services.cfg");
	fclose($handle);
	unset($str);
	unset($meta);
	unset($strEval);
	unset($str);
?>