<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/class/Oreon.class.php $
 * SVN : $Id: Oreon.class.php 9179 2009-10-08 10:27:34Z jmathis $
 * 
 */
 
class CentreonConfigPoller {
	private $DB;
	private $resultTest;
	private $optGen;
	private $nagiosCFGPath;
	private $centreon_path;
	private $centcore_pipe;

	public function CentreonConfigPoller($DB, $centreon_path) {
		$this->DB = $DB;
		$this->resultTest = 0;
		$this->nagiosCFGPath = "$centreon_path/filesGeneration/nagiosCFG/";
		$this->centreon_path = $centreon_path;
		$this->resultTest = array("warning" => 0, "errors" => 0);
		$this->centcore_pipe = "/var/lib/centreon/centcore.cmd";
	}
	
	private function getOptGen() {
		$DBRESULT =& $this->DB->query("SELECT * FROM options");
		while ($row =& $DBRESULT->fetchRow()) {
			$this->optGen[$row["key"]] = $row["value"];
		}
		$DBRESULT->free();
		unset($row);
	}

	private function testPollerId($id) {
		$DBRESULT =& $this->DB->query("SELECT id FROM nagios_server WHERE `id` = '$id'");
		if ($DBRESULT->numRows() != 0)
			return;
		else {
			print "ERROR: Unknown poller...\n";
			$this->getPollerList($this->format);
			exit(1);
		}
			
	}
	
	private function isPollerLocalhost($id) {
		$DBRESULT =& $this->DB->query("SELECT localhost FROM nagios_server WHERE `id` = '$id'");
		if ($data =& $DBRESULT->fetchRow())
			return $data["localhost"];
		else {
			print "ERROR: Unknown poller...\n";
			$this->getPollerList($this->format);
			exit(1);
		}
			
	}

	public function getPollerList($format) {
		$DBRESULT =& $this->DB->query("SELECT id,name FROM nagios_server ORDER BY id");
		if ($format == "xml")
			print "";
		while ($data =& $DBRESULT->fetchRow()) {
	    	print $data["id"]."\t".$data["name"]."\n";
	    }
		$DBRESULT->free();
		unset($data);
	}
	
	public function pollerReload($variables) {
		if (!isset($variables)) {
			print "Cannot get poller id.";
			exit(1);
		}
		
		$this->testPollerId($variables);
		
		/*
		 * Get Init Script
		 */
		$DBRESULT =& $this->DB->query("SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'");
		$serveurs =& $DBRESULT->fetchrow();
		$DBRESULT->free();
		(isset($serveurs["init_script"])) ? $nagios_init_script = $serveurs["init_script"] : $nagios_init_script = "/etc/init.d/nagios";
		unset($serveurs);
	
		$DBRESULT =& $this->DB->query("SELECT * FROM `nagios_server` WHERE `id` = '$variables'  LIMIT 1");
		$host = $DBRESULT->fetchRow();
		$DBRESULT->free();
			
		$msg_restart = "";
		if (isset($host['localhost']) && $host['localhost'] == 1) {
			$msg_restart = shell_exec("sudo " . $nagios_init_script . " reload");
		} else { 
			system("echo 'RELOAD:".$host["id"]."' >> ". $this->centcore_pipe);
			$msg_restart .= _("OK: A reload signal has been sent to ".$host["name"]);
		}
		print $msg_restart;
		$DBRESULT =& $this->DB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$variables."' LIMIT 1");
	}
	
	public function pollerRestart($variables) {
		if (!isset($variables)) {
			print "Cannot get poller id.";
			exit(1);
		}
		
		$this->testPollerId($variables);
		
		/*
		 * Get Init Script
		 */
		$DBRESULT =& $this->DB->query("SELECT id, init_script FROM nagios_server WHERE localhost = '1' AND ns_activate = '1'");
		$serveurs =& $DBRESULT->fetchrow();
		$DBRESULT->free();
		(isset($serveurs["init_script"])) ? $nagios_init_script = $serveurs["init_script"] : $nagios_init_script = "/etc/init.d/nagios";
		unset($serveurs);
	
		$DBRESULT =& $this->DB->query("SELECT * FROM `nagios_server` WHERE `id` = '$variables'  LIMIT 1");
		$host = $DBRESULT->fetchRow();
		$DBRESULT->free();	
		
		$msg_restart = "";
		if (isset($host['localhost']) && $host['localhost'] == 1) {
			$msg_restart = shell_exec("sudo " . $nagios_init_script . " restart");
		} else { 
			system("echo 'RESTART:".$variables."' >> ". $this->centcore_pipe);
			$msg_restart = _("OK: A restart signal has been sent to ".$host["name"]);
		}
		print $msg_restart;
		$DBRESULT =& $this->DB->query("UPDATE `nagios_server` SET `last_restart` = '".time()."' WHERE `id` = '".$variables."' LIMIT 1");
	}
	
	public function pollerTest($format, $variables) {
		if (!isset($variables)) {
			print "Cannot get poller id.";
			exit(1);
		}
		
		$this->testPollerId($variables);
		/*
		 * Get Nagios Bin
		 */
		$DBRESULT_Servers =& $this->DB->query("SELECT `nagios_bin` FROM `nagios_server` WHERE `ns_activate` = '1' AND `localhost` = '1' LIMIT 1");
		$nagios_bin = $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free();
		
		$stdout = shell_exec("sudo ".$nagios_bin["nagios_bin"] . " -v ".$this->nagiosCFGPath.$variables."/nagiosCFG.DEBUG");
		$lines = split("\n", $stdout);

		$msg_debug = "";
		foreach ($lines as $line) {
			if (strncmp($line, "Processing object config file", strlen("Processing object config file")) 
				&& strncmp($line, "Website: http://www.nagios.org", strlen("Website: http://www.nagios.org"))) {
					$msg_debug .= $line . "\n";
					/*
					 * Detect Errors
					 */
					if (preg_match("/Total Warnings: ([0-9])*/", $line, $matches))
						if (isset($matches[1]))
							$this->resultTest["warning"] = $matches[1];
					if (preg_match("/Total Errors: ([0-9])*/", $line, $matches))
						if (isset($matches[1]))
							$this->resultTest["errors"] = $matches[1];
					if (preg_match("/^Error:/", $line, $matches))
						$this->resultTest["errors"]++;
					if (preg_match("/^Errors:/", $line, $matches))
						$this->resultTest["errors"]++;
				}
		}
		if ($this->resultTest["errors"] != 0) {
			print "Error: Nagios Poller $variables cannot restart. configuration broker. Please see debug bellow :\n";
			print "---------------------------------------------------------------------------------------------------";
			print $msg_debug;
			print "---------------------------------------------------------------------------------------------------";
		} else if ($this->resultTest["warning"] != 0) {
			print "Warning: Nagios Poller $variables can restart but configuration is not optimal. Please see debug bellow :\n";
			print "---------------------------------------------------------------------------------------------------";
			print $msg_debug;
			print "---------------------------------------------------------------------------------------------------";
		} else {
			print "OK: Nagios Poller $variables can restart without problem...";
		}
		return;
	}
	
	public function pollerGenerate($variables, $login, $password) {
		require_once "../../../include/configuration/configGenerate/DB-Func.php";
		require_once "../../../include/common/common-Func.php";
		
		$this->testPollerId($variables);
		$tab["localhost"] = $this->isPollerLocalhost($variables);
		
		$centreon_path = $this->centreon_path;
		global $pearDB;
		$pearDB = $this->DB;

		$nagiosCFGPath = $this->nagiosCFGPath;
		$DebugPath = "filesGeneration/nagiosCFG/";
		
		$ret["comment"] = 0;
		/*
		 * Init environnement
		 */
		if ($this->optGen["version"] == "2.2")
			require_once $this->centreon_path."/www/class/centreon.class.php";
		else
			require_once $this->centreon_path."/www/class/Oreon.class.php";

		require_once $this->centreon_path."/www/class/centreonDB.class.php";
		require_once $this->centreon_path."/www/class/centreonAuth.class.php";
		require_once $this->centreon_path."/www/class/centreonLog.class.php";
		
		global $oreon, $_SERVER;

		$_SERVER["REMOTE_ADDR"] = "127.0.0.1"; 
		
		chdir("../../..");

		$CentreonLog = new CentreonUserLog(-1, $pearDB);
		$centreonAuth = new CentreonAuth($login, $password, 0, $this->DB, $CentreonLog,NULL);
		$user =& new User($centreonAuth->userInfos, $this->optGen["nagios_version"]);
		$oreon = new Oreon($user); 
		$oreon->user->version = 3; 		
		$tab['id'] = $variables;

    	chdir("./modules/centreon-clapi/core/");
		/*
		if ($this->optGen["version"] == "2.2")
			CentreonSession::start();
		else
			Session::start();
		*/
		/*
		 * Insert session in session table
		 */
		$pearDB->query("INSERT INTO `session` (`session_id` , `user_id` , `current_page` , `last_reload`, `ip_address`) VALUES ('1', '".$oreon->user->user_id."', '1', '".time()."', '".$_SERVER["REMOTE_ADDR"]."')");	
		
		/*
		 * Generate dependancies tree.
		 */
		global $gbArr;
		$gbArr = manageDependencies();

		/*
		 * Generate Configuration
		 */
		$path = "../../../include/configuration/configGenerate/";
		$path2 = "./include/configuration/configGenerate/";

		require $path."genCGICFG.php";

		chdir("../../..");

		require $path2."genNagiosCFG.php";
		require $path2."genNagiosCFG-DEBUG.php";

		chdir("./modules/centreon-clapi/core/");

		require $path."genNdomod.php";
		require $path."genNdo2db.php";
		require $path."genResourceCFG.php";
		require $path."genTimeperiods.php";
		require $path."genCommands.php";
		require $path."genContacts.php";
		require $path."genContactGroups.php";
		require $path."genHosts.php";
		require $path."genHostTemplates.php";
		require $path."genHostGroups.php";
		require $path."genServiceTemplates.php";
		require $path."genServices.php";
		require $path."genServiceGroups.php";
		require $path."genEscalations.php";
		require $path."genDependencies.php";
		require $path."centreon_pm.php";
  
 		chdir("../../..");
 		if ($tab['localhost']) {
			$flag_localhost = $tab['localhost'];
			/*
			 * Meta Services Generation
			 */
			if ($files = glob("./include/configuration/configGenerate/metaService/*.php"))
				foreach ($files as $filename)
					require_once($filename);
			
			/*
			 * Module Generation
			 */
			foreach ($oreon->modules as $key => $value) {
				if (file_exists("./modules/".$key."/core/common/functions.php"))
					require_once "./modules/".$key."/core/common/functions.php";
				if ($files = glob("./modules/".$key."/generate_files/*.php"))
					foreach ($files as $filename) {
						require_once ($filename);
					}
			}
		}
		chdir("./modules/centreon-clapi/core/");
		unset($generatedHG);
		unset($generatedSG);
		unset($generatedS);
 
 		print "Configuration files generated for poller ".$variables;
	}
	
	public function cfgMove($variables) {
		if (!isset($variables)) {
			print "Cannot get poller id.";
			exit(1);
		}
		/*
		 * Check poller existance
		 */
		$this->testPollerId($variables);
		
		/*
		 * Move files.
		 */
		$DBRESULT_Servers =& $this->DB->query("SELECT `cfg_dir` FROM `cfg_nagios` WHERE `nagios_server_id` = '$variables' LIMIT 1");
		$Nagioscfg = $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free(); 
		
		$DBRESULT_Servers =& $this->DB->query("SELECT * FROM `nagios_server` WHERE `id` = '$variables'  LIMIT 1");
		$host = $DBRESULT_Servers->fetchRow();
		$DBRESULT_Servers->free();
		if (isset($host['localhost']) && $host['localhost'] == 1) {
			$msg_copy = "";
			foreach (glob($this->nagiosCFGPath.$variables."/*.cfg") as $filename) {
				$bool = @copy($filename , $Nagioscfg["cfg_dir"].basename($filename));
				$filename = array_pop(explode("/", $filename));
				if (!$bool)
					$msg_copy .= $this->display_copying_file($filename, " - "._("movement")." KO");
			}
			if (strlen($msg_copy) == 0) {
				$msg_copy .= _("OK: All configuration files copied with success.");
			}
		} else {
			passthru("echo 'SENDCFGFILE:".$host['id']."' >> ".$this->centcore_pipe, $return);
			if (!isset($msg_copy))
				$msg_copy = "";
			$msg_copy .= _("OK: All configuration will be send to '".$host['name']."' by centcore in several minutes.");
		}	
		print $msg_copy;
	}
	
	private function display_copying_file($filename = NULL, $status){
		if (!isset($filename))
			return ;
		$str = "- ".$filename." -> ".$status."\n";
		return $str;
	}
	
	/*
	 * Method for config file Generation
	 */
}
?>