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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/status/TopCounter/statusCounter.php $
 * SVN : $Id: statusCounter.php 10717 2010-07-19 16:54:43Z jmathis $
 * 
 */

	$debug = 0;
	$flag_reset = 0;
	
	foreach ($_GET as $key => $value) {
		$_GET[$key] = htmlentities($value, ENT_QUOTES);
	}

	include_once "@CENTREON_ETC@/centreon.conf.php";	
	include_once $centreon_path . "www/include/common/common-Func.php";
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	
	/* 
	 * Connect to oreon DB 
	 */
	$pearDB 	= new CentreonDB();
	$pearDBC 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");
		
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		print "<data>Can't connect to ndo Database</data>";
		exit();
	}
		
	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	/*
	 * sessionID check and refresh
	 */
	$sid = isset($_POST["sid"]) ? $_POST["sid"] : 0;
	$sid = isset($_GET["sid"]) ? $_GET["sid"] : $sid;

	/* 
	 * Security Check
	 */
	if (!check_injection($sid) && preg_match("/^[0-9A-Za-z]*$/", $sid, $matches)) {
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if ($session =& $res->fetchRow()) {
			$DBRESULT2 =& $pearDB->query("UPDATE `session` SET `last_reload` = '".time()."', `ip_address` = '".$_SERVER["REMOTE_ADDR"]."' WHERE CONVERT( `session_id` USING utf8 ) = '".$sid."' LIMIT 1");
		} else {
			get_error('bad session id');
		}
	} else {
		get_error('need session identifiant !');
	}
	
	/*
	 * LCA
	 */
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user =& $res1->fetchRow();
	$user_id = $user["user_id"];

	global $is_admin;
	
	$is_admin =  isUserAdmin($sid);	
		
	/*
	 * Get Acl Group list
	 */
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups(); 
	$groupnumber = count($grouplist);
	$grouplistStr = $access->getAccessGroupsString(); 
	
	function restore_session($statistic_service = 'null', $statistic_host = 'null'){
		global $pearDB, $sid;
		if (isset($statistic_service) && !is_null($statistic_service)){
			$sql = 	" UPDATE session SET " .
					" s_nbHostsUp = '".$statistic_host["UP"]."'," .
					" s_nbHostsDown = '".$statistic_host["DOWN"]."'," .
					" s_nbHostsUnreachable = '".$statistic_host["UNREACHABLE"]."'," .
					" s_nbHostsPending = '".$statistic_host["PENDING"]."'," .
					" s_nbServicesOk = '".$statistic_service["OK"]."'," .
					" s_nbServicesWarning = '".$statistic_service["WARNING"]."'," .
					" s_nbServicesCritical = '".$statistic_service["CRITICAL"]."'," .
					" s_nbServicesUnknown = '".$statistic_service["UNKNOWN"]."'," .
					" s_nbServicesPending = '".$statistic_service["PENDING"]."'" .
					" WHERE session_id = '".$sid."'";
			$DBRESULT =& $pearDB->query($sql);			
		}
	}

	
	$oreon = "";
	$search = "";
	$search_type_service = 0;
	$search_type_host = 0; 

	/*
	 * Init stat for resume
	 */
	$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);
	$statistic_service = array("OK" => 0, "WARNING" => 0, "CRITICAL" => 0, "UNKNOWN" => 0, "PENDING" => 0);

	$general_opt = array();
	$query = "SELECT `key`, `value` FROM `options` WHERE `key` LIKE 'color_%'";
	$DBRESULT_OPT =& $pearDB->query($query);		
	while ($rowz =& $DBRESULT_OPT->fetchRow()) {
		$general_opt[$rowz['key']] = $rowz['value'];
	}
	$DBRESULT_OPT->free();
	unset($rowz);

	/*
	 * Get active poller only
	 */
	$pollerList = "";
	$request = "SELECT name FROM nagios_server WHERE ns_activate = '1'";
	$DBRESULT =& $pearDB->query($request);
	while ($d = $DBRESULT->fetchRow()) {
		if ($pollerList = "") {
			$pollerList .= ", ";
		}
		$pollerList .= "'".$d["name"]."'";
	}
	$DBRESULT->free();
	

	/* 
	 * Get Host NDO status 
	 */		
	$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1), ".$ndo_base_prefix."hoststatus.current_state" .
			" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects";
	
	if (!$is_admin)
		$rq1 .= " , centreon_acl ";
	
	$rq1 .= " WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id " .
			" AND ".$ndo_base_prefix."objects.is_active = 1 " .
			$access->queryBuilder("AND", $ndo_base_prefix."objects.name1", "centreon_acl.host_name") .				
			$access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr) .
			" AND " . $ndo_base_prefix. "objects.name1 NOT LIKE '_Module_%' " .				
			" GROUP BY ".$ndo_base_prefix."hoststatus.current_state";
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	
	$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);

	while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
		$host_stat[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.name1)"];
	}
	$DBRESULT_NDO1->free();
	unset($ndo);
	
	/* 
	 * Get Service NDO status 
	 */
	if (!$is_admin)
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".					
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".$grouplistStr.") ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	else
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.is_active = 1 GROUP BY nss.current_state";			
	
	$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
	
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	while ($ndo =& $DBRESULT_NDO2->fetchRow()) {
		$svc_stat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
	}
	$DBRESULT_NDO2->free();
	unset($ndo);
	
	restore_session($statistic_service, $statistic_host);

	/*
	 * Check Poller Status
	 */
	$status = 0;
	$latency = 0;
	$activity = 0;
	$error = "";
	$pollerListInError = "";
	
	/*
	 * Get minimum check interval
	 */
	$request = "SELECT MIN(check_interval) FROM ".$ndo_base_prefix."services";
	$DBRESULT =& $pearDBndo->query($request);
	if (isset($DBRESULT)) {
		$data =& $DBRESULT->fetchRow();
		$minInterval = $data["MIN(check_interval)"];
	} else {
		$minInterval = 5;
	}
	
	/*
	 * Get minimin interval lenght
	 */
	$request = "SELECT MIN(interval_length) FROM cfg_nagios";
	$DBRESULT =& $pearDB->query($request);
	$data =& $DBRESULT->fetchRow();
	$intervalLength = $data["MIN(interval_length)"];
	
	/*
	 * Unit Time
	 */
	$timeUnit = $minInterval * $intervalLength;
	
	$request = 	"SELECT UNIX_TIMESTAMP(`status_update_time`) AS last_update, `is_currently_running`, instance_name, ".$ndo_base_prefix."instances.instance_id " .
				"FROM `".$ndo_base_prefix."programstatus`, ".$ndo_base_prefix."instances " .
				"WHERE ".$ndo_base_prefix."programstatus.instance_id = ".$ndo_base_prefix."instances.instance_id AND ".$ndo_base_prefix."instances.instance_name IN ($pollerList) ";
	$DBRESULT =& $pearDBndo->query($request);
	while ($ndo =& $DBRESULT->fetchRow()) {
		/*
		 * Running
		 */
		if ($status != 2 && $ndo["is_currently_running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit * 2)) {
			$status = 1;
			if ($pollerListInError != "")
				$pollerListInError .= ", ";
			$pollerListInError .= $ndo["instance_name"];
		} 
		if ($ndo["is_currently_running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit * 4)) {
			$status = 2;
			if ($pollerListInError != "")
				$pollerListInError .= ", ";
			$pollerListInError .= $ndo["instance_name"];
		}
		/*
		 * Activity
		 */
		if ($activity != 2 && (time() - $ndo["last_update"] >= $timeUnit * 2)) {
			$activity = 1;
		}
		if ((time() - $ndo["last_update"] >= $timeUnit * 4)) {
			$activity = 2;
		}
	}
	$DBRESULT->free();
	$error = "Pollers $pollerListInError not running.";

	$request = 	"SELECT stat_value, i.instance_id, instance_name " .
				"FROM `nagios_stats` ns, instance i " .
				"WHERE ns.stat_label = 'Service Check Latency' " .
				"	AND ns.stat_key LIKE 'Average' " .
				"	AND ns.instance_id = i.instance_id" .
				"	AND i.instance_name IN ($pollerList)";
	$DBRESULT =& $pearDBC->query($request);
	while ($ndo =& $DBRESULT->fetchRow()) {
		if ($latency != 2 && $ndo["stat_value"] >= 60) {
			$latency = 1;
		}
		if ($ndo["stat_value"] >= 120) {
			$latency = 2;
		}
	}
	$DBRESULT->free();
	unset($ndo);

	/*
	 * Error Messages
	 */
	if ($status != 0) {
		$errorPstt = "$error";
	} else {
		$errorPstt = _("OK : all pollers are running");	
	}

	if ($latency != 0) {
		$errorLtc = _("Latency detected on your platform; check configuration for better optimisation");
	} else {
		$errorLtc = _("OK : no latency detected on your platform");
	}
	
	if ($activity != 0) {
		$errorAct = _("Some database poller updates are not active; check your nagios platform");
	} else {
		$errorAct = _("OK : all database poller updates are active");
	}
	

	/*
	 * Create Buffer
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("infos");
	$buffer->writeElement("filetime", time());
	$buffer->endElement();
	$buffer->startElement("s");
	$buffer->writeElement("o", $svc_stat["0"]);
	$buffer->writeElement("w", $svc_stat["1"]);
	$buffer->writeElement("c", $svc_stat["2"]);
	$buffer->writeElement("un1", $svc_stat["3"]);
	$buffer->writeElement("p1", $svc_stat["4"]);
	$buffer->writeElement("up", $host_stat["0"]);
	$buffer->writeElement("d", $host_stat["1"]);
	$buffer->writeElement("un2", $host_stat["2"]);
	$buffer->writeElement("p2", $host_stat["3"]);
	$buffer->endElement();
	$buffer->startElement("m");
	$buffer->writeElement("pstt", $status);
	$buffer->writeElement("ltc", $latency);
	$buffer->writeElement("act", $activity);
	$buffer->writeElement("errorPstt", $errorPstt);
	$buffer->writeElement("errorLtc", $errorLtc);
	$buffer->writeElement("errorAct", $errorAct);
	$buffer->writeElement("debugInterval", $timeUnit);
	$buffer->endElement();
	$buffer->endElement();		
	
	/*
	 * send Header
	 */
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	
	/*
	 * Display Buffer
	 */
	$buffer->output();
	
?>