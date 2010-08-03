<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();
	/*
	 * Write command in nagios pipe or in centcore pipe. 
	 */

	function write_command($cmd, $poller){
		global $oreon, $key, $pearDB;
		$str = NULL;

		$informations = split(";", $key);
		if ($poller && isPollerLocalhost($pearDB, $poller))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else if (isHostLocalhost($pearDB, $informations[0]))
			$str = "echo '[" . time() . "]" . $cmd . "\n' >> " . $oreon->Nagioscfg["command_file"];
		else
			$str = "echo 'EXTERNALCMD:$poller:[" . time() . "]" . $cmd . "\n' >> " . "@CENTREON_VARLIB@/centcore.cmd";
		return passthru($str);
	}

	 /*
      * Ack hosts massively
      */
        function massiveHostAck($key){
        	global $pearDB, $_GET, $is_admin, $oreon;
		
			$actions = false;
        	$actions = $oreon->user->access->checkAction("host_acknowledgement");

			$tmp = split(";", $key);
			$host_name = $tmp[0];
			
			isset($_GET['persistent']) && $_GET['persistent'] == "true" ? $persistent = "1" : $persistent = "0";
			isset($_GET['notify']) && $_GET['notify'] == "true" ? $notify = "1" : $notify = "0";
			isset($_GET['sticky']) && $_GET['sticky'] == "true" ? $sticky = "2" : $sticky = "1";
			isset($_GET['force_check']) && $_GET['force_check'] == "true" ? $force_check = "1" : $force_check = "0";
			
            if ($actions == true || $is_admin) {
                    $host_poller = GetMyHostPoller($pearDB, $host_name);
                    $flg = write_command(" ACKNOWLEDGE_HOST_PROBLEM;".$host_name.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);
            }
	
			$actions = $oreon->user->access->checkAction("service_acknowledgement");
			if (($actions == true || $is_admin) && isset($_GET['ackhostservice']) && $_GET['ackhostservice'] == "true") {
				$DBRES = $pearDB->query("SELECT host_id FROM `host` WHERE host_name = '".$host_name."' LIMIT 1");
	            $row =& $DBRES->fetchRow();
				$svc_tab = array();
	            $svc_tab = getMyHostServices($row['host_id']);
	            if (count($svc_tab)) {
					foreach ($svc_tab as $key2 => $value) {
	            		write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$host_name.";".$value.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);
						if ($force_check == 1)
		                	write_command(" SCHEDULE_FORCED_SVC_CHECK;".$host_name.";".time(), $host_poller);                	
	                }
				}
			}
			return _("Your command has been sent");
        }

        /*
         * Ack services massively
         */
        function massiveServiceAck($key){
	        global $pearDB, $is_admin, $oreon;
			
			$actions = false;
			$actions = $oreon->user->access->checkAction("service_acknowledgement");
			                
			$tmp = split(";", $key);
			$host_name = $tmp[0];
			
			if (!isset($tmp[1])) {
				return NULL;
        	} else {
				$svc_description = $tmp[1];
			}
			
			isset($_GET['persistent']) && $_GET['persistent'] == "true" 		? $persistent = "1" : $persistent = "0";
	        isset($_GET['notify']) && $_GET['notify'] == "true" 				? $notify = "1" : $notify = "0";	
			isset($_GET['sticky']) && $_GET['sticky'] == "true" 				? $sticky = "2" : $sticky = "1";
			isset($_GET['force_check']) && $_GET['force_check'] == "true" 		? $force_check = "1" : $force_check = "0";

			$host_poller = GetMyHostPoller($pearDB, $host_name);
			
	        if ($actions == true || $is_admin) {
				$_GET["comment"] = $_GET["comment"];
	            $_GET["comment"] = str_replace('\'', ' ', $_GET["comment"]);
	            $flg = write_command(" ACKNOWLEDGE_SVC_PROBLEM;".$host_name.";".$svc_description.";".$sticky.";".$notify.";".$persistent.";".$_GET["author"].";".$_GET["comment"], $host_poller);
		        if ($force_check == 1) {
		        	write_command(" SCHEDULE_FORCED_SVC_CHECK;".$host_name.";".$svc_description.";".time(), $host_poller);  
		        }
	            return _("Your command has been sent");
	         }
	         return NULL;
        }
?>