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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/home/tacticalOverview/tacticalOverview.php $
 * SVN : $Id: tacticalOverview.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

/*
 * This file drawing the tactical overview on Home pages. 
 *
 * PHP version 5
 *
 * @package tacticalOverview.php
 * @author Julien Mathis jmathis@merethis.com
 * @author Damien Duponchelle dduponchelle@merethis.com
 * @version $Id: tacticalOverview.php 10473 2010-05-19 21:25:56Z jmathis $
 * @copyright (c) 2007-2008 Centreon
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 
	// Variables $oreon must exist. it contains all personnals datas (Id, Name etc.) using by user to navigate on the interface.
	if (!isset($oreon)) {
		exit();
	}

	// Including files and dependences 
	require_once './class/other.class.php';	
	require_once './include/common/common-Func.php';	
	require_once './class/centreonDB.class.php';
	
	global $pearDB, $pearDBndo;
	
	$pearDB 	= new CentreonDB();
	$pearDBndo 	= new CentreonDB("ndo");	
	
	// Testing the NDO database connexion. If "error" or "failed" is matching in the output message, the script print a error message and exit
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
	} else {
		
			// The user must install the ndo table with the 'centreon_acl'
			if ($err_msg = table_not_exists("centreon_acl")) { 
				print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
			}

			$acl_host_name_list = $oreon->user->access->getHostsString("NAME", $pearDBndo);
			$acl_access_group_list = $oreon->user->access->getAccessGroupsString();
			
			// Including Pear files
			require_once 'HTML/QuickForm.php';
			require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
			
			// Declaring variables
			$ndo_base_prefix = getNDOPrefix(); // Getting ndo database prefix
			$general_opt = getStatusColor($pearDB); // Getting colors of each status like : "[color_ok] => #13EB3A [color_warning] => #F8C706 ..." 
		    
		    /*
			 * Host / Image cache
			 */
			$CacheIcone = array();
			$rq1 = "SELECT name1, icon_image FROM ".$ndo_base_prefix."objects obj, ".$ndo_base_prefix."hosts h WHERE h.host_object_id = obj.object_id";
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			while ($data =& $DBRESULT_NDO1->fetchRow()) {
				if (isset($data["icon_image"]) && $data["icon_image"] && $data["icon_image"] != "")
					$CacheIcone[$data["name1"]] = $data["icon_image"];
			}
			$DBRESULT_NDO1->free();
			unset($data);
		    
			// Get Status Globals for hosts		
			$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state), ".$ndo_base_prefix."hoststatus.current_state" .
					" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects" .
					" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id".
					" AND ".$ndo_base_prefix."objects.is_active = 1 " .
					$oreon->user->access->queryBuilder("AND", $ndo_base_prefix."objects.name1", $acl_host_name_list) . 
					" AND ".$ndo_base_prefix."objects.name1 NOT LIKE '_Module_%' " .
					" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
					" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			
			$hostStatus = array(0=>0, 1=>0, 2=>0, 3=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
				$hostStatus[$ndo["current_state"]] = $ndo["count(".$ndo_base_prefix."hoststatus.current_state)"];
			}
			
			// Get Hosts Problems			
			$rq1 = 	" SELECT DISTINCT obj.name1 , hs.current_state, unix_timestamp(hs.last_check) AS last_check, hs.output, h.address, unix_timestamp(hs.last_state_change) AS lsc" .
					" FROM ".$ndo_base_prefix."hoststatus hs, ".$ndo_base_prefix."objects obj,  ".$ndo_base_prefix."hosts h " .
					" WHERE obj.object_id = hs.host_object_id".
					" AND obj.object_id = h.host_object_id" .
					" AND obj.is_active = 1 " .
					$oreon->user->access->queryBuilder("AND", "obj.name1", $acl_host_name_list) .
					" AND hs.current_state <> 0" .
					" AND hs.problem_has_been_acknowledged = 0" .
					" ORDER by hs.current_state";			
			
			$DBRESULT_NDOHOSTS =& $pearDBndo->query($rq1);
			if (PEAR::isError($DBRESULT_NDOHOSTS))
				print "DB Error : ".$DBRESULT_NDOHOSTS->getDebugInfo()."<br />";
			
			$nbhostpb = 0;
            $tab_hostprobname[$nbhostpb] = "";
           	$tab_hostprobstate[$nbhostpb] = "";
            $tab_hostproblast[$nbhostpb] = "";
            $tab_hostprobduration[$nbhostpb] = "";
            $tab_hostproboutput[$nbhostpb] = "";
            $tab_hostprobip[$nbhostpb] = "";
            $tab_hostprobIcone[$nbhostpb] = "";

			while ($ndo =& $DBRESULT_NDOHOSTS->fetchRow()) {				
				$tab_hostprobname[$nbhostpb] = $ndo["name1"];
	            $tab_hostprobstate[$nbhostpb] = $ndo["current_state"];
	            $tab_hostproblast[$nbhostpb] = $oreon->CentreonGMT->getDate(_("Y/m/d G:i"), $ndo["last_check"], $oreon->user->getMyGMT());
	            $tab_hostprobduration[$nbhostpb] = Duration::toString(time() - $ndo["lsc"]);
	            $tab_hostproboutput[$nbhostpb] = $ndo["output"];
        	    $tab_hostprobip[$nbhostpb] = $ndo["address"];
        	    if (isset($CacheIcone[$ndo["name1"]]))
	        	    $tab_hostprobIcone[$nbhostpb] = $CacheIcone[$ndo["name1"]];
				else
					$tab_hostprobIcone[$nbhostpb] = "../icones/16x16/server_network.gif";
				$nbhostpb++;				
			}
			$DBRESULT_NDOHOSTS->free();
			
			$hostUnhand = array(0=>$hostStatus[0], 1=>$hostStatus[1], 2=>$hostStatus[2]);			
			/*
			 * Get the id's of problem hosts
			*/			
			$rq1 = 	" SELECT ".$ndo_base_prefix."hoststatus.host_object_id, " .$ndo_base_prefix. "hoststatus.current_state ".
					" FROM ".$ndo_base_prefix."servicestatus, ".$ndo_base_prefix."hoststatus, " . $ndo_base_prefix."services, " . $ndo_base_prefix. "objects" .
					" WHERE ".$ndo_base_prefix."servicestatus.service_object_id = ".$ndo_base_prefix."services.service_object_id" . 
					" AND ".$ndo_base_prefix."services.host_object_id = " . $ndo_base_prefix . "hoststatus.host_object_id" .
					" AND ".$ndo_base_prefix."hoststatus.host_object_id = " . $ndo_base_prefix . "objects.object_id" .
					" AND ".$ndo_base_prefix."objects.is_active = 1 " .
					$oreon->user->access->queryBuilder("AND", $ndo_base_prefix."objects.name1", $acl_host_name_list) .						
					" AND ".$ndo_base_prefix."objects.name1 NOT LIKE '_Module_%' " .
					" GROUP BY ".$ndo_base_prefix."services.host_object_id";
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			
			$pbCount = 0;
			while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
				if ($ndo["current_state"] != 0){
					$hostPb[$pbCount] = $ndo["host_object_id"];			
					$pbCount++;
				}
			}
			$DBRESULT_NDO1->free();
			
			/*
			 * Get Host Ack  UP(0), DOWN(1),  UNREACHABLE(2)
			 */			
			$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1), ".$ndo_base_prefix."hoststatus.current_state" .
					" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
					" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id " .
					" AND ".$ndo_base_prefix."objects.is_active = 1 " .
					" AND ".$ndo_base_prefix."hoststatus.problem_has_been_acknowledged = 1 " .
					$oreon->user->access->queryBuilder("AND", $ndo_base_prefix."objects.name1", $acl_host_name_list) . 
					" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
					" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
			
			$hostAck = array(0=>0, 1=>0, 2=>0);
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
				$hostAck[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.name1)"];
				$hostUnhand[$ndo["current_state"]] -= $hostAck[$ndo["current_state"]]; 
			}
			$DBRESULT_NDO1->free();

			/*
			 * Get Host inactive objects
			 */			
			$rq1 = 	" SELECT count(".$ndo_base_prefix."hoststatus.current_state), ".$ndo_base_prefix."hoststatus.current_state" .
					" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects" .
					" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id AND ".$ndo_base_prefix."objects.is_active = 0 " .					
					$oreon->user->access->queryBuilder("AND", $ndo_base_prefix."objects.name1", $acl_host_name_list) . 
					" AND ".$ndo_base_prefix."objects.name1 NOT LIKE '_Module_%' " .
					" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
					" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
						
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			
			$hostInactive = array(0=>0, 1=>0, 2=>0, 3=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow())	{
				$hostInactive[$ndo["current_state"]] = $ndo["count(".$ndo_base_prefix."hoststatus.current_state)"];
				$hostUnhand[$ndo["current_state"]] -= $hostInactive[$ndo["current_state"]];				
			}
			$DBRESULT_NDO1->free();
			 
			/*
			 * Get Host Unrea Not Unhandled
			 */
			
			/*
			 * Get Status global for Services
			 */		
			if (!$is_admin)
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl" .
						" WHERE no.object_id = nss.service_object_id".						
						" AND no.name1 NOT LIKE '_Module_%' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .						
						" AND centreon_acl.group_id IN (".$acl_access_group_list.") " .
						" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
			else
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state". 
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
						" WHERE no.object_id = nss.service_object_id".
						" AND no.name1 not like '_Module_%' ".
						" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";					
		
			$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
			
			$SvcStat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
		
			while ($ndo =& $DBRESULT_NDO2->fetchRow()) {
				$SvcStat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
			} 
			$DBRESULT_NDO2->free();
			
			/*
			 * Get on pb host
			*/
			if (!$is_admin)
				$rq2 = 	" SELECT nss.current_state, " . $ndo_base_prefix ."services.host_object_id".
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl, " . $ndo_base_prefix."services" .
						" WHERE no.object_id = nss.service_object_id".
						" AND nss.service_object_id = ".$ndo_base_prefix."services.service_object_id".						
						" AND no.name1 NOT LIKE '_Module_%' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".$acl_access_group_list.") " .
						" AND no.is_active = 1" .
						" AND nss.problem_has_been_acknowledged = 0" .
						" AND nss.current_state > 0 GROUP BY nss.service_object_id";
			else
				$rq2 = 	" SELECT nss.current_state, ". $ndo_base_prefix ."services.host_object_id".
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, " . $ndo_base_prefix."services" .
						" WHERE no.object_id = nss.service_object_id".
						" AND nss.service_object_id = ".$ndo_base_prefix."services.service_object_id".
						" AND no.name1 NOT LIKE '_Module_%' ".						
						" AND no.is_active = 1" .
						" AND nss.problem_has_been_acknowledged = 0" .
						" AND nss.current_state > 0 GROUP BY nss.service_object_id";
			
			$onPbHost = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq2);
			while($ndo =& $DBRESULT_NDO1->fetchRow())	{			
				if ($ndo["current_state"] != 0)
					for ($i = 0; $i < $pbCount; $i++)
						if (isset($hostPb[$i]) && ($hostPb[$i] == $ndo["host_object_id"]))
							$onPbHost[$ndo["current_state"]]++;
			}
			$DBRESULT_NDO1->free();
		
			
			/*
			 * Get ServiceAck  OK(0), WARNING(1),  CRITICAL(2), UNKNOWN(3)
			 */
			if (!$is_admin)
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.object_id), " . $ndo_base_prefix."servicestatus.current_state" .
						" FROM ".$ndo_base_prefix."objects, ".$ndo_base_prefix."servicestatus, centreon_acl" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."servicestatus.service_object_id" .					
						" AND ".$ndo_base_prefix."servicestatus.problem_has_been_acknowledged = 1 " .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 = centreon_acl.host_name ".
						" AND ".$ndo_base_prefix."objects.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".$acl_access_group_list.") " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE '_Module_%' " .
						" GROUP BY ".$ndo_base_prefix."servicestatus.current_state";								
			else
				$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.object_id), " . $ndo_base_prefix."servicestatus.current_state" .
						" FROM ".$ndo_base_prefix."objects, ".$ndo_base_prefix."servicestatus" .
						" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."servicestatus.service_object_id" .
						" AND ".$ndo_base_prefix."servicestatus.problem_has_been_acknowledged = 1 " .
						" AND ".$ndo_base_prefix."objects.is_active = 1 " .
						" AND ".$ndo_base_prefix."objects.name1 NOT LIKE '_Module_%' " .
						" GROUP BY ".$ndo_base_prefix."servicestatus.current_state";									
			
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			
			$svcAck = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			while ($ndo =& $DBRESULT_NDO1->fetchRow()) {
				$svcAck[$ndo["current_state"]] = $ndo["count(DISTINCT ".$ndo_base_prefix."objects.object_id)"];
			}
			$DBRESULT_NDO1->free();
			/*
			 * Get Services Inactive objects
			 */
			if (!$is_admin)
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
						" WHERE no.object_id = nss.service_object_id".				
						" AND no.name1 NOT LIKE '_Module_%' ".
						" AND no.name1 = centreon_acl.host_name ".
						" AND no.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".$acl_access_group_list.") ".
						" AND no.is_active = 0 GROUP BY nss.current_state ORDER by nss.current_state";
			else
				$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
						" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
						" WHERE no.object_id = nss.service_object_id".						
						" AND no.name1 NOT LIKE '_Module_%' ".
						" AND no.is_active = 0 GROUP BY nss.current_state ORDER by nss.current_state";			
	
			$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
			
			$svcInactive = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			while ($ndo =& $DBRESULT_NDO2->fetchRow()) {
				$svcInactive[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
			}
			$DBRESULT_NDO2->free();
			
			
			/*
			 * Get Undandled Services
			 */
			$svcUnhandled = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
			for ($i=0; $i<=4; $i++) {
				$svcUnhandled[$i] = $SvcStat[$i] - $svcAck[$i] - $svcInactive[$i] - $onPbHost[$i];			
			}
			
			/*
			 * Get problem table
			 */
			if (!$is_admin)
				$rq1 = 	" SELECT distinct obj.name1, obj.name2, stat.current_state, unix_timestamp(stat.last_check) as last_check, stat.output, unix_timestamp(stat.last_state_change) as last_state_change, svc.host_object_id, ht.address " .
						" FROM ".$ndo_base_prefix."objects obj, ".$ndo_base_prefix."servicestatus stat, " . $ndo_base_prefix . "services svc, centreon_acl, " . $ndo_base_prefix . "hosts ht " .
						" WHERE obj.object_id = stat.service_object_id" .
						" AND stat.service_object_id = svc.service_object_id" .
						" AND obj.name1 = ht.display_name" .
						" AND stat.current_state > 0" .
						" AND stat.current_state <> 3" .
						" AND stat.problem_has_been_acknowledged = 0" .
						" AND obj.is_active = 1" .
						" AND obj.name1 NOT LIKE '_Module_%' " .
						" AND obj.name1 = centreon_acl.host_name ".
						" AND obj.name2 = centreon_acl.service_description " .
						" AND centreon_acl.group_id IN (".$acl_access_group_list.") " .
						" ORDER by stat.current_state DESC, obj.name1";
			else
				$rq1 = 	" SELECT distinct obj.name1, obj.name2, stat.current_state, unix_timestamp(stat.last_check) as last_check, stat.output, unix_timestamp(stat.last_state_change) as last_state_change, svc.host_object_id, ht.address " .
						" FROM ".$ndo_base_prefix."objects obj, ".$ndo_base_prefix."servicestatus stat, " . $ndo_base_prefix . "services svc, " . $ndo_base_prefix . "hosts ht " .
						" WHERE obj.object_id = stat.service_object_id" .
						" AND stat.service_object_id = svc.service_object_id" .
						" AND obj.name1 = ht.display_name" .
						" AND stat.current_state > 0" .
						" AND stat.current_state <> 3" .
						" AND stat.problem_has_been_acknowledged = 0" .
						" AND obj.is_active = 1" .				
						" AND obj.name1 NOT LIKE '_Module_%' " .
						" ORDER by stat.current_state DESC, obj.name1";
			$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
			
			$j = 0;	
			$tab_hostname[$j] = "";
			$tab_svcname[$j] = "";
			$tab_state[$j] = "";
			$tab_last[$j] = "";
			$tab_duration[$j] = "";
			$tab_output[$j] = "";
			$tab_ip[$j] = "";
			$tab_icone[$j] = "";
			
			while ($ndo =& $DBRESULT_NDO1->fetchRow()){
				$is_unhandled = 1;	
	
				for ($i = 0; $i < $pbCount && $is_unhandled; $i++){
					if (isset($hostPb[$i]) && ($hostPb[$i] == $ndo["host_object_id"]))
						$is_unhandled = 0;
				}
	
				if ($is_unhandled) {
					$tab_hostname[$j] = $ndo["name1"];
					$tab_svcname[$j] = $ndo["name2"];
					$tab_state[$j] = $ndo["current_state"];
					$tab_last[$j] = $oreon->CentreonGMT->getDate(_("Y/m/d G:i"), $ndo["last_check"], $oreon->user->getMyGMT());
					$tab_ip[$j] = $ndo["address"];
		
					if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"])
						$tab_duration[$j] = Duration::toString(time() - $ndo["last_state_change"]);
					else if ($ndo["last_state_change"] > 0)
						$tab_duration[$j] = " - ";
					$tab_output[$j] = $ndo["output"];
					if (isset($CacheIcone[$ndo["name1"]]))
						$tab_icone[$j] = $CacheIcone[$ndo["name1"]];
					else
						$tab_icone[$j] = "../icones/16x16/server_network.gif";
					$j++;
				}
			}
			$DBRESULT_NDO1->free();
			
			$nb_pb = $j;
			 
			$path = "./include/home/tacticalOverview/";
		
			/*
			 * Smarty template Init
			 */
			$tpl = new Smarty();
			$tpl = initSmartyTpl($path, $tpl);
			
			$tpl->assign("color", $general_opt);
			$tpl->assign("HostStatus", $hostStatus);
			$tpl->assign("HostAck", $hostAck);
			$tpl->assign("HostInact", $hostInactive);			
			$tpl->assign("HostUnhand", $hostUnhand);
			$tpl->assign("ServiceStatus", $SvcStat);
			$tpl->assign("SvcAck", $svcAck);
			$tpl->assign("SvcInact", $svcInactive);
			$tpl->assign("SvcOnPbHost", $onPbHost);
			$tpl->assign("SvcUnhandled", $svcUnhandled);
			$tpl->assign("nb_pb", $nb_pb);
			$tpl->assign("tb_hostname", $tab_hostname);
			$tpl->assign("tb_svcname", $tab_svcname);
			$tpl->assign("tb_state", $tab_state);
			$tpl->assign("tb_last", $tab_last);
			$tpl->assign("tb_output", $tab_output);
			$tpl->assign("tb_duration", $tab_duration);
			$tpl->assign("tb_ip", $tab_ip);
			$tpl->assign("tb_icone", $tab_icone);
						
			$tpl->assign("tb_hostprobname", $tab_hostprobname);
			$tpl->assign("tb_hostprobstate", $tab_hostprobstate);
			$tpl->assign("tb_hostproblast", $tab_hostproblast);
			$tpl->assign("tb_hostproboutput", $tab_hostproboutput);
			$tpl->assign("tb_hostprobduration", $tab_hostprobduration);
			$tpl->assign("tb_hostprobip", $tab_hostprobip);
			$tpl->assign("tb_hostprobIcone", $tab_hostprobIcone);
			$tpl->assign("nb_hostpb", $nbhostpb);
			
			$tpl->assign("refresh_interval", $oreon->optGen["oreon_refresh"]);
			
			/*
			 * URL
			 */
			$tpl->assign("url_hostPb",     "main.php?p=20103&o=hpb&search=");
			$tpl->assign("url_hostOK",     "main.php?p=20103&o=h&search=");
			$tpl->assign("url_host_unhand","main.php?p=20105&o=h_unhandled&search=");
			$tpl->assign("url_svc_unhand", "main.php?p=20215&o=svc_unhandled&search=");
			$tpl->assign("url_svc_ack",    "main.php?p=2020402&o=svcOV&acknowledge=1&search=");
			$tpl->assign("url_ok",         "main.php?p=2020101&o=svc_ok&search=");
			$tpl->assign("url_critical",   "main.php?p=2020103&o=svc_critical&search=");
			$tpl->assign("url_warning",    "main.php?p=2020102&o=svc_warning&search=");
			$tpl->assign("url_unknown",    "main.php?p=2020104&o=svc_unknown&search=");
			$tpl->assign("url_hostdetail", "main.php?p=201&o=hd&host_name=");
			$tpl->assign("url_svcdetail",  "main.php?p=202&o=svcd&host_name=");
			$tpl->assign("url_svcdetail2", "&service_description=");
			
			/*
			 *  Strings for the host part
			 */
			$tpl->assign("str_hosts", _("Hosts"));
			$tpl->assign("str_up", _("Up"));
			$tpl->assign("str_down", _("Down"));
			$tpl->assign("str_unreachable", _("Unreachable"));
			
			/*
			 *  Strings for the service part
			 */
			$tpl->assign("str_services", _("Services"));
			$tpl->assign("str_ok", _("OK"));
			$tpl->assign("str_warning", _("Warning"));
			$tpl->assign("str_critical", _("Critical"));
			$tpl->assign("str_unknown", _("Unknown"));
			$tpl->assign("str_pbhost", _("On Problem Host"));
			$tpl->assign("str_unhandledpb", _("Unhandled"));
			
			/*
			 *  Common Strings for both the host and service parts
			 */
		 	$tpl->assign("str_pending", _("Pending"));
			$tpl->assign("str_disabled", _("Disabled"));
			$tpl->assign("str_acknowledged", _("Acknowledged"));
			
			/*
			 *  Strings for service problems
			 */
			$tpl->assign("str_unhandled", _("Unhandled Service problems"));
			$tpl->assign("str_no_unhandled", _("No unhandled service problem"));
			$tpl->assign("str_hostname", _("Host Name"));
			$tpl->assign("str_servicename", _("Service Name"));
			$tpl->assign("str_status", _("Status"));
			$tpl->assign("str_lastcheck", _("Last Check"));
			$tpl->assign("str_duration", _("Duration"));
			$tpl->assign("str_output", _("Status Output"));
			$tpl->assign("str_actions", _("Actions"));
			$tpl->assign("str_ip", _("IP Address"));
			
			/*
			 *  Strings for hosts problems
			 */
			$tpl->assign("str_hostprobunhandled", _("Unhandled Host problems"));
			$tpl->assign("str_hostprobno_unhandled", _("No unhandled host problem"));
			$tpl->assign("str_hostprobhostname", _("Host Name"));
			$tpl->assign("str_hostprobstatus", _("Status"));
			$tpl->assign("str_hostproblastcheck", _("Last Check"));
			$tpl->assign("str_hostprobduration", _("Duration"));
			$tpl->assign("str_hostproboutput", _("Status Output"));
			$tpl->assign("str_hostprobip", _("IP Address"));
						
			/*
			 * Display tactical
			 */
			$tpl->display("tacticalOverview.ihtml");	
	}
 ?>