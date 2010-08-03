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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genServices.php $
 * SVN : $Id: genServices.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();

	require_once ($centreon_path . "/www/class/centreonService.class.php");

	/*
	 * Build cache for CG
	 */
	$cgSCache = array();
	$DBRESULT =& $pearDB->query("SELECT csr.service_service_id, cg.cg_id, cg.cg_name FROM contactgroup_service_relation csr, contactgroup cg WHERE csr.contactgroup_cg_id = cg.cg_id");
	while ($cg =& $DBRESULT->fetchRow()) {
		if (!isset($cgSCache[$cg["service_service_id"]]))
			$cgSCache[$cg["service_service_id"]] = array();
		$cgSCache[$cg["service_service_id"]][$cg["cg_id"]] = $cg["cg_name"];
	}
	$DBRESULT->free();
	unset($cg);

	/*
	 * Build cache for services contact
	 */
	$cctSCache = array();
	$DBRESULT2 =& $pearDB->query("SELECT c.contact_id, c.contact_name, csr.service_service_id FROM contact_service_relation csr, contact c WHERE csr.contact_id = c.contact_id");
	while ($contact =& $DBRESULT2->fetchRow())	{
		if (!isset($cctSCache[$contact["service_service_id"]]))
			$cctSCache[$contact["service_service_id"]] = array();
		$cctSCache[$contact["service_service_id"]][$contact["contact_id"]] = $contact["contact_name"];
	}
	$DBRESULT2->free();
	unset($contact);

	/*
	 * Build cache for service group
	 */
	$sgCache = array();
	$DBRESULT2 =& $pearDB->query("SELECT sgr.service_service_id, sg.sg_id, sg.sg_name FROM servicegroup_relation sgr, servicegroup sg WHERE sgr.servicegroup_sg_id = sg.sg_id");
	while ($serviceGroup =& $DBRESULT2->fetchRow())	{
		if (!isset($sgCache[$serviceGroup["service_service_id"]]))
			$sgCache[$serviceGroup["service_service_id"]] = array();		
		$sgCache[$serviceGroup["service_service_id"]][$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
	}
	$DBRESULT->free();
	unset($serviceGroup);
	
	/*
	 * Init Generated SG
	 */
	$SGFilled = array();
	
	
	/*
	 * Build cache for Macro
	 */
	$macroCache = array();
	$DBRESULT3 =& $pearDB->query("SELECT svc_macro_name, svc_macro_value, svc_svc_id FROM on_demand_macro_service");
	while ($od_macro =& $DBRESULT3->fetchRow()) { 
		if (!isset($macroCache[$od_macro["svc_svc_id"]]))	
			$macroCache[$od_macro["svc_svc_id"]] = array();
		$macroCache[$od_macro["svc_svc_id"]][$od_macro["svc_macro_name"]] = $od_macro["svc_macro_value"];
	}
	$DBRESULT3->free();
	unset($od_macro);
	
	/*
	 * Create ESI Cache
	 */
	$esiCache = array();
	$DBRESULT3 =& $pearDB->query("SELECT * FROM extended_service_information esi");
	while ($esi =& $DBRESULT3->fetchRow()) { 
		if (!isset($esiCache[$esi["service_service_id"]]))	
			$esiCache[$esi["service_service_id"]] = array();
		$esiCache[$esi["service_service_id"]]["notes"] = $esi["esi_notes"];
		$esiCache[$esi["service_service_id"]]["notes_url"] = $esi["esi_notes_url"];
		$esiCache[$esi["service_service_id"]]["action_url"] = $esi["esi_action_url"];
		if (isset($esi["esi_icon_image"]) && $esi["esi_icon_image"] != 0 && $esi["esi_icon_image"] != "")
			$esiCache[$esi["service_service_id"]]["icon_image"] = getMyServiceExtendedInfoImage($esi["service_service_id"], "esi_icon_image");
		$esiCache[$esi["service_service_id"]]["icon_image_alt"] = $esi["esi_icon_image_alt"];
	}
	$DBRESULT3->free();
	unset($esi);
	
	/*
	 * Create file
	 */
	$handle = create_file($nagiosCFGPath.$tab['id']."/services.cfg", $oreon->user->get_name());

	/*
	 * Get Service List
	 */
	$str = "";
	if ($oreon->CentreonGMT->used() == 1) {
		foreach ($hostGenerated as $host_id => $host_name) {
			$svcList = getMyHostActiveServices($host_id);
			foreach ($svcList as $svc_id => $svc_name) {
				
				$DBRESULT =& $pearDB->query("SELECT * FROM `service` WHERE `service_id` = '$svc_id' ORDER BY `service_description`");
				$service =& $DBRESULT->fetchRow();
				
				$strTMP = NULL;
				$parent = false;
				$ret["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
				if ($ret["comment"] && $service["service_comment"])	{
					$comment = array();
					$comment = explode("\n", $service["service_comment"]);
					foreach ($comment as $cmt)
						$strTMP .= "# ".$cmt."\n";
				}
				/*
				 * Adjust host_location and time period name
				 */
				if (isset($gmtCache[$host_id]))
					 $gmt = $gmtCache[$host_id];
				else
					$gmt = 0;
					 			
	 			/*
	 			 * Begin service definition
	 			 */
				$strTMP .= "define service{\n";
				$strTMP .= print_line("host_name", $host_name);
				$strTMP .= print_line("service_description", convertServiceSpecialChar($service["service_description"]));
				
				/*
                 * Write service_id
                 */
                if ($oreon->user->get_version() >= 3)
	                $strTMP .= print_line("_SERVICE_ID", $service["service_id"]);
				
				/*
				 * Template Model Relation
				 */
				if ($service["service_template_model_stm_id"]) {
					$strTMP .= print_line("use", convertServiceSpecialChar($svcTplCache[$service["service_template_model_stm_id"]]));
				}
				
				if (isset($sgCache[$service["service_id"]])) {
					$strTMPTemp = "";
					foreach ($sgCache[$service["service_id"]] as $sg_name) {
						if ($strTMPTemp != "") 
							$strTMPTemp .= ",";
						$strTMPTemp .= $sg_name;
					}
					if (isset($strTMPSG) && $strTMPSG) 
						$strTMP .= print_line("servicegroups", $strTMPSG);
					unset($strTMPSG);
				}
				
				if ($service["service_is_volatile"] != 2) 
					$strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
				
				/*
				 * Check Command
				 */
				$command = NULL;
				$command = getMyCheckCmdParam($service["service_id"]);
				if ($command)
					$strTMP .= print_line("check_command", $command);
				
				if ($service["service_max_check_attempts"] != NULL) 
					$strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
				if ($service["service_normal_check_interval"] != NULL) 
					$strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
				if ($service["service_retry_check_interval"] != NULL) 
					$strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
				if ($service["service_active_checks_enabled"] != 2) 
					$strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
				if ($service["service_passive_checks_enabled"] != 2) 
					$strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
				
				/*
				 * Check Period
				 */
				if (!$service["timeperiod_tp_id"]) 
					$service["timeperiod_tp_id"] = getMyServiceTPInCache($service["service_template_model_stm_id"], $cpCache);	
				if (isset($timeperiods[$service["timeperiod_tp_id"]]) && $timeperiods[$service["timeperiod_tp_id"]] != "")
					$strTMP .= print_line("check_period", $timeperiods[$service["timeperiod_tp_id"]]."_GMT".$gmt);

				if ($service["service_parallelize_check"] != 2) 
					$strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
				if ($service["service_obsess_over_service"] != 2)
					$strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
				if ($service["service_check_freshness"] != 2) 
					$strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
				if ($service["service_freshness_threshold"] != NULL) 
					$strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
				
				/*
				 * Event_handler
				 */
				 
				if ($service["command_command_id2"])
					$strTMP .= print_line("event_handler", $commands[$service["command_command_id2"]].$service["command_command_id_arg2"]);
				if ($service["service_event_handler_enabled"] != 2) 
					$strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
				if ($service["service_low_flap_threshold"] != NULL) 
					$strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
				if ($service["service_high_flap_threshold"] != NULL) 
					$strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
				if ($service["service_flap_detection_enabled"] != 2) 
					$strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
				if ($service["service_process_perf_data"] != 2) 
					$strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
				if ($service["service_retain_status_information"] != 2) 
					$strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
				if ($service["service_retain_nonstatus_information"] != 2) 
					$strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
				
				/*
				 * Notifications
				 */
				
				if (!$service["timeperiod_tp_id2"])
					$service["timeperiod_tp_id2"] = getMyServiceTPInCache($service["service_template_model_stm_id"], $npCache);
				if (isset($timeperiods[$service["timeperiod_tp_id2"]]) && $timeperiods[$service["timeperiod_tp_id2"]] != "")
					$strTMP .= print_line("notification_period", $timeperiods[$service["timeperiod_tp_id2"]]."_GMT".$gmt);

				if ($service["service_notification_interval"] != NULL) 
					$strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
				if ($service["service_notification_options"]) 
					$strTMP .= print_line("notification_options", $service["service_notification_options"]);
				if ($service["service_notifications_enabled"] != 2) 
					$strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
				if ($service["service_first_notification_delay"] != NULL) 
					$strTMP .= print_line("first_notification_delay", $service["service_first_notification_delay"]);
					
				/*
				 * Contact Group Relation
				 */
				
				if (isset($cgSCache[$service["service_id"]])) {
					$strTMPTemp = "";
					foreach ($cgSCache[$service["service_id"]] as $cg_name) {
						if ($strTMPTemp != "") 
							$strTMPTemp .= ",";
						$strTMPTemp .= $cg_name;
					}
					if ($strTMPTemp) 
						$strTMP .= print_line("contact_groups", $strTMPTemp);
					unset($strTMPTemp);
				}
				
				/*
				 * Contact Relation only for Nagios 3
				 */
				if ($oreon->user->get_version() >= 3) {
					if (isset($cctSCache[$service["service_id"]])) {
						$strTMPTemp = "";
						foreach ($cctSCache[$service["service_id"]] as $cct_id => $cct_name) {
							if ($strTMPTemp != "") 
								$strTMPTemp .= ",";
							$strTMPTemp .= $cct_name;
						}
						if ($strTMPTemp) 
							$strTMP .= print_line("contacts", $strTMPTemp);
						unset($strTMPTemp);
					}
				}
				
				if ($service["service_stalking_options"]) 
					$strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
				if (!$service["service_register"]) 
					$strTMP .= print_line("register", "0");
				
				if ($oreon->user->get_version() >= 3) {
					/*
					 * On-demand macros
					 */
					if (isset($macroCache[$service['service_id']])) {
						foreach ($macroCache[$service['service_id']] as $key => $value) {
							$mac_name = str_replace("\$_SERVICE", "_", $key);
							$mac_name = str_replace("\$", "", $mac_name);
							$mac_name = str_replace("#S#", "/", $mac_name);
							$mac_name = str_replace("#BS##BS#", "\\", $mac_name);
							$value = str_replace("#S#", "/", $value);
							$value = str_replace("#BS##BS#", "\\", $value);
							$strTMP .= print_line($mac_name, $value);
						}
					}
				
					/*
					 * Extended Informations
					 */
					
					if (isset($esiCache[$service["service_id"]])) {
						$esi =& $esiCache[$service["service_id"]];
						if (isset($esi["notes"]) && $esi["notes"])
							$strTMP .= print_line("notes", $esi["notes"]);
						if (isset($esi["notes_url"]) && $esi["notes_url"])
							$strTMP .= print_line("notes_url", $esi["notes_url"]);
						if (isset($esi["action_url"]) && $esi["action_url"])
							$strTMP .= print_line("action_url", $esi["action_url"]);
						if (isset($esi["icon_image"]) && $esi["icon_image"])
							$strTMP .= print_line("icon_image", $esi["icon_image"]);
						if (isset($esi["icon_image_alt"]) && $esi["icon_image_alt"])
							$strTMP .= print_line("icon_image_alt", $esi["icon_image_alt"]);	
					}
				}
				$strTMP .= "}\n\n";
				$str .= $strTMP;
				
				unset($parent);
				unset($strTMPTemp);
			}
		}
		
	} else {
		
		$hostGroupCorresp = array();
		$DBRESULT =& $pearDB->query("SELECT hg_name, hg_id FROM hostgroup");
		while ($data =& $DBRESULT->fetchRow())
			$hostGroupCorresp[$data["hg_id"]] = $data["hg_name"];
		$DBRESULT->free();
		unset($data);
		
		/*
		 * Create Service relation buffer
		 */
		$serviceRelation = array();
		$DBRESULT2 =& $pearDB->query("SELECT host_host_id as host_id, hostgroup_hg_id as hg_id, service_service_id as service_id FROM host_service_relation");
		while ($data =& $DBRESULT2->fetchRow())	{
			if (!isset($serviceRelation[$data["service_id"]]))
				$serviceRelation[$data["service_id"]] = array();
			if (isset($data["hg_id"]) && isset($generatedHG[$data["hg_id"]]) && isset($hgHostGenerated[$hostGroupCorresp[$data["hg_id"]]])) {
				if (!isset($serviceRelation[$data["service_id"]]["hg"]))
					$serviceRelation[$data["service_id"]]["hg"] = array();
				$serviceRelation[$data["service_id"]]["hg"][$data["hg_id"]] = $generatedHG[$data["hg_id"]];
			}
			if (isset($data["host_id"]) && isset($hostGenerated[$data["host_id"]])) {
				if (!isset($serviceRelation[$data["service_id"]]["h"]))
					$serviceRelation[$data["service_id"]]["h"] = array();
				$serviceRelation[$data["service_id"]]["h"][$data["host_id"]] = $hostGenerated[$data["host_id"]];
			}
		}
		$DBRESULT2->free();
		
		$DBRESULT =& $pearDB->query("SELECT * FROM service WHERE `service_activate` = '1' AND `service_register` = '1' ORDER BY `service_description`");
		$service = array();
		$i = 1;
		$str = NULL;
		while ($service =& $DBRESULT->fetchRow())	{
			$LinkedToHost = 0;
			$strDef = "";
			
			$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
			$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);
			$service["service_alias"] = str_replace('#S#', "/", $service["service_alias"]);
			$service["service_alias"] = str_replace('#BS#', "\\", $service["service_alias"]);
			
			if (isset($gbArr[4][$service["service_id"]])) {
				/*
				 *  Can merge multiple Host or HostGroup Definition
				 */
				$strTMP = NULL;
				$parent = false;
				$ret["comment"] ? ($strTMP .= "# '" . $service["service_description"] . "' service definition " . $i . "\n") : NULL;
				if ($ret["comment"] && $service["service_comment"])	{
					$comment = array();
					$comment = explode("\n", $service["service_comment"]);
					foreach ($comment as $cmt)
						$strTMP .= "# ".$cmt."\n";
				}
				$strTMP .= "define service{\n";
				if ($service["service_register"] == 1 && (isset($serviceRelation[$service["service_id"]]["h"]) || isset($serviceRelation[$service["service_id"]]["hg"])))	{
					/*
					 * HostGroup Relation
					 */
					$strTMPTemp = NULL;
					if (isset($serviceRelation[$service["service_id"]]["hg"]))
						foreach ($serviceRelation[$service["service_id"]]["hg"] as $key => $value) {
							$parent = true;
							$strTMPTemp != NULL ? $strTMPTemp .= ", ".$value : $strTMPTemp = $value;
							$LinkedToHost++;
						}
					if ($strTMPTemp) 
						$strTMP .= print_line("hostgroup_name", $strTMPTemp);
					unset($strTMPTemp);
					
					if (!$parent)	{
						/*
						 * Host Relation
						 */
						$strTMPTemp = NULL;
						if (isset($serviceRelation[$service["service_id"]]["h"]))
							foreach ($serviceRelation[$service["service_id"]]["h"] as $key => $value) {
								$parent = true;
								$strTMPTemp != NULL ? $strTMPTemp .= ", ".$value : $strTMPTemp = $value;
								$LinkedToHost++;
							}
						if ($strTMPTemp) 
							$strTMP .= print_line("host_name", $strTMPTemp);
						unset($strTMPTemp);
					}
				}
				
				if ($service["service_description"]) 
					$strTMP .= print_line("service_description", $service["service_description"]);
				
				/*
                 * Write service_id
                 */
                if ($oreon->user->get_version() >= 3)
	                $strTMP .= print_line("_SERVICE_ID", $service["service_id"]);
				
				/*
				 * Template Model Relation
				 */
				if ($service["service_template_model_stm_id"]) {
					$strTMP .= print_line("use", convertServiceSpecialChar($svcTplCache[$service["service_template_model_stm_id"]]));
				}
				
				if (isset($sgCache[$service["service_id"]])) {
					$strTMPTemp = "";
					foreach ($sgCache[$service["service_id"]] as $sg_name) {
						if ($strTMPTemp != "") 
							$strTMPTemp .= ",";
						$strTMPTemp .= $sg_name;
					}
					if (isset($strTMPSG) && $strTMPSG) {
						$strTMP .= print_line("servicegroups", $strTMPSG);
						unset($strTMPSG);
					}
				}
				
				/*
				 * Service Group
				 */
				if (isset($sgCache[$service["service_id"]])) {
					$strTMPTemp = "";
					foreach ($sgCache[$service["service_id"]] as $sg_name) {
						if ($strTMPTemp != "") 
							$strTMPTemp .= ",";
						$strTMPTemp .= $sg_name;
					}
					if (isset($strTMPSG) && $strTMPSG) {
						$strTMP .= print_line("servicegroups", $strTMPSG);
						unset($strTMPSG);
					}
				}


				if ($service["service_is_volatile"] != 2) 
					$strTMP .= print_line("is_volatile", $service["service_is_volatile"] == 1 ? "1": "0");
			
				/*
				 * Check Command
				 */
				$command = NULL;
				$command = getMyCheckCmdParam($service["service_id"]);
				if ($command)
					$strTMP .= print_line("check_command", $command);
				
				
				if ($service["service_max_check_attempts"] != NULL) 
					$strTMP .= print_line("max_check_attempts", $service["service_max_check_attempts"]);
				if ($service["service_normal_check_interval"] != NULL) 
					$strTMP .= print_line("normal_check_interval", $service["service_normal_check_interval"]);
				if ($service["service_retry_check_interval"] != NULL) 
					$strTMP .= print_line("retry_check_interval", $service["service_retry_check_interval"]);
				if ($service["service_active_checks_enabled"] != 2) 
					$strTMP .= print_line("active_checks_enabled", $service["service_active_checks_enabled"] == 1 ? "1": "0");
				if ($service["service_passive_checks_enabled"] != 2) 
					$strTMP .= print_line("passive_checks_enabled", $service["service_passive_checks_enabled"] == 1 ? "1": "0");
				
				/*
				 * Check Period
				 */
				if (isset($service["timeperiod_tp_id"]) && $service["timeperiod_tp_id"])
					$strTMP .= print_line("check_period", $timeperiods[$service["timeperiod_tp_id"]]);

				if ($service["service_parallelize_check"] != 2) 
					$strTMP .= print_line("parallelize_check", $service["service_parallelize_check"] == 1 ? "1": "0");
				if ($service["service_obsess_over_service"] != 2) 
					$strTMP .= print_line("obsess_over_service", $service["service_obsess_over_service"] == 1 ? "1": "0");
				if ($service["service_check_freshness"] != 2) 
					$strTMP .= print_line("check_freshness", $service["service_check_freshness"] == 1 ? "1": "0");
				if ($service["service_freshness_threshold"] != NULL) 	
					$strTMP .= print_line("freshness_threshold", $service["service_freshness_threshold"]);
				
				/*
				 * Event_handler
				 */
				 
				$command = array();
				$service["command_command_id_arg2"] = str_replace('#BR#', "\\n", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#T#', "\\t", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#R#', "\\r", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#S#', "/", $service["command_command_id_arg2"]);
				$service["command_command_id_arg2"] = str_replace('#BS#', "\\", $service["command_command_id_arg2"]);
				
				if ($service["command_command_id2"])
					$strTMP .= print_line("event_handler", $commands[$service["command_command_id2"]].$service["command_command_id_arg2"]);
				
				if ($service["service_event_handler_enabled"] != 2) 
					$strTMP .= print_line("event_handler_enabled", $service["service_event_handler_enabled"] == 1 ? "1": "0");
				if ($service["service_low_flap_threshold"] != NULL) 
					$strTMP .= print_line("low_flap_threshold", $service["service_low_flap_threshold"]);
				if ($service["service_high_flap_threshold"] != NULL) 
					$strTMP .= print_line("high_flap_threshold", $service["service_high_flap_threshold"]);
				if ($service["service_flap_detection_enabled"] != 2) 
					$strTMP .= print_line("flap_detection_enabled", $service["service_flap_detection_enabled"] == 1 ? "1": "0");
				if ($service["service_process_perf_data"] != 2) 
					$strTMP .= print_line("process_perf_data", $service["service_process_perf_data"] == 1 ? "1": "0");
				if ($service["service_retain_status_information"] != 2) 
					$strTMP .= print_line("retain_status_information", $service["service_retain_status_information"] == 1 ? "1": "0");
				if ($service["service_retain_nonstatus_information"] != 2) 
					$strTMP .= print_line("retain_nonstatus_information", $service["service_retain_nonstatus_information"] == 1 ? "1": "0");
				if ($service["service_notification_interval"] != NULL) 
					$strTMP .= print_line("notification_interval", $service["service_notification_interval"]);
				if ($service["service_first_notification_delay"] != NULL) 
					$strTMP .= print_line("first_notification_delay", $service["service_first_notification_delay"]);
					
				if (isset($service["timeperiod_tp_id2"]) && $service["timeperiod_tp_id2"])
					$strTMP .= print_line("notification_period", $timeperiods[$service["timeperiod_tp_id2"]]);
				
				if ($service["service_notification_options"]) 
					$strTMP .= print_line("notification_options", $service["service_notification_options"]);
				if ($service["service_notifications_enabled"] != 2) 
					$strTMP .= print_line("notifications_enabled", $service["service_notifications_enabled"] == 1 ? "1": "0");
				
								
				/*
				 * Contact Group Relation
				 */
				
				if (isset($cgSCache[$service["service_id"]])) {
					$strTMPTemp = "";
					foreach ($cgSCache[$service["service_id"]] as $cg_name) {
						if ($strTMPTemp != "") 
							$strTMPTemp .= ",";
						$strTMPTemp .= $cg_name;
					}
					if ($strTMPTemp) 
						$strTMP .= print_line("contact_groups", $strTMPTemp);
					unset($strTMPTemp);
				}
				
				/*
				 * Contact Relation only for Nagios 3
				 */
				if ($oreon->user->get_version() >= 3) {
					if (isset($cctSCache[$service["service_id"]])) {
						$strTMPTemp = "";
						foreach ($cctSCache[$service["service_id"]] as $cct_id => $cct_name) {
							if ($strTMPTemp != "") 
								$strTMPTemp .= ",";
							$strTMPTemp .= $cct_name;
						}
						if ($strTMPTemp) 
							$strTMP .= print_line("contacts", $strTMPTemp);
						unset($strTMPTemp);
					}
				}
				if ($service["service_stalking_options"]) 
					$strTMP .= print_line("stalking_options", $service["service_stalking_options"]);
				if (!$service["service_register"]) 
					$strTMP .= print_line("register", "0");
				
				if (isset($service["service_register"]) && $service["service_register"] == 0){
					$DBRESULT_TEMP =& $pearDB->query("SELECT host_name FROM host, host_service_relation WHERE `service_service_id` = '".$service["service_id"]."' AND `host_id` = `host_host_id`");
					if (PEAR::isError($DBRESULT_TEMP))
						print "DB Error : ".$DBRESULT_TEMP->getDebugInfo()."<br />";
					while ($template_link =& $DBRESULT_TEMP->fetchRow())
						$strTMP .= print_line(";TEMPLATE-HOST-LINK", $template_link["host_name"]);
					unset($template_link);
					unset($DBRESULT_TEMP);
				}
				
				if ($oreon->user->get_version() >= 3) {
					/*
					 * On-demand macros
					 */
					if (isset($macroCache[$service['service_id']])) {
						foreach ($macroCache[$service['service_id']] as $key => $value) {
							$mac_name = str_replace("\$_SERVICE", "_", $key);
							$mac_name = str_replace("\$", "", $mac_name);
							$mac_name = str_replace("#S#", "/", $mac_name);
							$mac_name = str_replace("#BS##BS#", "\\", $mac_name);
							$value = str_replace("#S#", "/", $value);
							$value = str_replace("#BS##BS#", "\\", $value);
							$strTMP .= print_line($mac_name, $value);
						}
					}
				
					/*
					 * Extended Informations
					 */
					
					if (isset($esiCache[$service["service_id"]])) {
						$esi =& $esiCache[$service["service_id"]];
						if (isset($esi["notes"]) && $esi["notes"])
							$strTMP .= print_line("notes", $esi["notes"]);
						if (isset($esi["notes_url"]) && $esi["notes_url"])
							$strTMP .= print_line("notes_url", $esi["notes_url"]);
						if (isset($esi["action_url"]) && $esi["action_url"])
							$strTMP .= print_line("action_url", $esi["action_url"]);
						if (isset($esi["icon_image"]) && $esi["icon_image"])
							$strTMP .= print_line("icon_image", $esi["icon_image"]);
						if (isset($esi["icon_image_alt"]) && $esi["icon_image_alt"])
							$strTMP .= print_line("icon_image_alt", $esi["icon_image_alt"]);	
					}
				}
				
				$strTMP .= "}\n\n";
				if (!$service["service_register"] || $LinkedToHost)	{
					$i++;
					$str .= $strTMP;
				}
				unset($parent);
				unset($strTMPTemp);
			}
		}
		unset($serviceRelation);
	}
	
	
	unset($service);
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/services.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>