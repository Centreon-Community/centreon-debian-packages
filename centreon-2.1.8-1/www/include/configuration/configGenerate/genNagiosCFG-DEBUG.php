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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genNagiosCFG-DEBUG.php $
 * SVN : $Id: genNagiosCFG-DEBUG.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();
	
	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG", $oreon->user->get_name(), false);
	$DBRESULT =& $pearDB->query("SELECT * FROM `cfg_nagios` WHERE `nagios_activate` = '1' AND `nagios_server_id` = '".$tab['id']."' LIMIT 1");
	$nagios = $DBRESULT->fetchRow();
	$str = NULL;
	$ret["comment"] ? ($str .= "# '".$nagios["nagios_name"]."'\n") : NULL;
	if ($ret["comment"] && $nagios["nagios_comment"])	{
		$comment = array();
		$comment = explode("\n", $nagios["nagios_comment"]);
		foreach ($comment as $cmt)
			$str .= "# ".$cmt."\n";
	}
	
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hosts.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hostTemplates.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/serviceTemplates.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/services.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/misccommands.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/checkcommands.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/contactgroups.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/contacts.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hostgroups.cfg\n";
	
	if ($oreon->user->get_version() >= 2)
		$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/servicegroups.cfg\n";
		
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/timeperiods.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/escalations.cfg\n";
	$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/dependencies.cfg\n";	
	
	if ($oreon->user->get_version() == 2)	{
		$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/hostextinfo.cfg\n";
		$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/serviceextinfo.cfg\n";
	}
	
	/*
	 * Include for Meta Service the cfg file
	 */
	if (isset($tab['localhost']) && $tab['localhost']) {
		if ($files = glob("./include/configuration/configGenerate/metaService/*.php"))
			foreach ($files as $filename)	{
				$cfg = NULL;
				$file =& basename($filename);
				$file = explode(".", $file);
				$cfg .= $file[0];
				$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/".$cfg.".cfg\n";
			}
		# Include for Module the cfg file
		foreach ($oreon->modules as $name => $tab2)
			if ($oreon->modules[$name]["gen"] && $files = glob("./modules/$name/generate_files/*.php"))
				foreach ($files as $filename)	{
					$cfg = NULL;
					$file =& basename($filename);
					$file = explode(".", $file);
					$cfg .= $file[0];
					$str .= "cfg_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/".$cfg.".cfg\n";
				}
	}
	$str .= "resource_file=".$oreon->optGen["oreon_path"].$DebugPath.$tab['id']."/resource.cfg\n";
	$nagios["cfg_dir"] = NULL;
	foreach ($nagios as $key=>$value)	{
		if ($value != NULL && $key != "nagios_id" && $key != "nagios_name" && $key != "nagios_server_id" && $key != "nagios_comment" && $key != "nagios_activate")	{	
			if ($key == "aggregate_status_updates" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "enable_notifications" && $value == 2);	
			else if ($key == "execute_service_checks" && $value == 2);	
			else if ($key == "accept_passive_service_checks" && $value == 2);	
			else if ($key == "execute_host_checks" && $value == 2);	
			else if ($key == "accept_passive_host_checks" && $value == 2);	
			else if ($key == "enable_event_handlers" && $value == 2);
			else if ($key == "check_external_commands" && $value == 2);
			else if ($key == "retain_state_information" && $value == 2);
			else if ($key == "use_retained_program_state" && $value == 2);
			else if ($key == "use_retained_scheduling_info" && $value == 2);
			else if ($key == "use_syslog" && $value == 2);
			else if ($key == "log_notifications" && $value == 2);
			else if ($key == "log_service_retries" && $value == 2);
			else if ($key == "log_host_retries" && $value == 2);
			else if ($key == "log_event_handlers" && $value == 2);
			else if ($key == "log_initial_states" && $value == 2);
			else if ($key == "log_external_commands" && $value == 2);
			else if ($key == "log_passive_checks" && $value == 2);
			else if ($key == "auto_reschedule_checks" && $value == 2);
			else if ($key == "use_agressive_host_checking" && $value == 2);
			else if ($key == "enable_flap_detection" && $value == 2);
			else if ($key == "soft_state_dependencies" && $value == 2);
			else if ($key == "obsess_over_services" && $value == 2);
			else if ($key == "obsess_over_hosts" && $value == 2);
			else if ($key == "process_performance_data" && $value == 2);
			//else if ($key == "max_service_check_spread");
			//else if ($key == "max_host_check_spread");
			else if ($key == "check_for_orphaned_services" && $value == 2);
			else if ($key == "check_service_freshness" && $value == 2);
			else if ($key == "check_host_freshness" && $value == 2);
			else if ($key == "use_regexp_matching" && $value == 2);
			else if ($key == "use_true_regexp_matching" && $value == 2);
			else if ($key == "service_inter_check_delay_method" && $value == 2);
			else if ($key == "host_inter_check_delay_method" && $value == 2);
			else if ($key == "service_reaper_frequency") {
				if ($oreon->user->get_version() == 2) {
					$str .= $key."=".$value."\n";
				} else {
					$str .= "check_result_reaper_frequency=".$value."\n";
				}
			}
			else if ($key == "global_host_event_handler" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "global_service_event_handler" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "ocsp_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "ochp_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "host_perfdata_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "service_perfdata_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "host_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			else if ($key == "service_perfdata_file_processing_command" && $value)	{
				$DBRESULT2 =& $pearDB->query("SELECT command_name FROM `command` WHERE command_id = '".$value."'");
				$row = $DBRESULT2->fetchRow();
				$str .= $key."=".$row["command_name"]."\n";
			}
			/*
			 * Nagios 3 part
			 */
			else if ($key == "enable_predictive_host_dependency_checks" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "enable_predictive_service_dependency_checks" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "use_large_installation_tweaks" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "free_child_process_memory" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "child_processes_fork_twice" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "enable_environment_macros" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "enable_embedded_perl" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "use_embedded_perl_implicitly" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "debug_verbosity" && ($value == 2 || $oreon->user->get_version() == 3));
			else if ($key == "cached_host_check_horizon" && $oreon->user->get_version() == 3);
			else if ($key == "cached_service_check_horizon" && $oreon->user->get_version() == 3);
			else if ($key == "additional_freshness_latency" && $oreon->user->get_version() == 3);			
			else if ($key == "max_debug_file_size" && $oreon->user->get_version() == 3);				
			else if ($key == "downtime_file" && $oreon->user->get_version() == 3);
			else if ($key == "comment_file" && $oreon->user->get_version() == 3);
			else if ($key == "enable_embedded_perl" && $oreon->user->get_version() == 2);
			else if ($key == "use_embedded_perl_implicitly" && $oreon->user->get_version() == 2);			
			else if ($key == "tmp_path" && $oreon->user->get_version() == 2);
			else if ($key == "check_result_path" && $oreon->user->get_version() == 2);
			else if ($key == "max_check_result_file_age " && $oreon->user->get_version() == 2);
			else if ($key == "debug_level_opt");
			else
				$str .= $key."=".$value."\n";
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/nagiosCFG.DEBUG");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>