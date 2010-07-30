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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/class/centreonHost.class.php $
 * SVN : $Id: centreonHost.class.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
 /*
  *  Class that contains various methods for managing hosts 
  */
 class CentreonHost {
 	private $local_pearDB;
		
 	
 	/*
 	 *  Constructor
 	 */
 	function CentreonHost($pearDB) {
 		$this->local_pearDB = $pearDB;
 	}
 	
 	/*
 	 *  Method that returns a hostname from host_id
 	 */
 	public function getHostName($host_id) {
 		$rq = "SELECT host_name FROM host WHERE host_id = '".$host_id."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['host_name'];
 	} 
 	 	
 	/*
 	 *  Method that returns a host alias from host_id
 	 */
 	public function getHostAlias($host_id) {
 		$rq = "SELECT host_alias FROM host WHERE host_id = '".$host_id."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['host_alias'];
 	}
 	
 	/*
 	 *  Method that returns a host address from host_id
 	 */
 	public function getHostAddress($host_id) {
 		$rq = "SELECT host_address FROM host WHERE host_id = '".$host_id."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['host_address'];
 	}
 	
 	/*
 	 *  Method that returns the id of a host
 	 */
 	public function getHostId($host_name) {
 		$rq = "SELECT host_id FROM host WHERE host_name = '".$host_name."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['host_id'];
 	}
 	
 	/*
 	 * Method that returns the poller id that monitors the host
 	 */
 	public function getHostPollerId($host_id) {
 		$rq = "SELECT nagios_server_id FROM ns_host_relation WHERE host_host_id = '".$host_id."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['nagios_server_id']; 		
 	}
 	
 	/*
 	 *  Returns a string that replaces on demand macros by their values
 	 */
 	public function replaceMacroInString($hostParam, $string) { 		 		
		if (is_numeric($hostParam)) {
 	        $host_id = $hostParam;		    
		}
		elseif (is_string($hostParam)) {
		    $host_id = $this->getHostId($hostParam);
		}
		$rq = "SELECT host_register FROM host WHERE host_id = '".$host_id."' LIMIT 1";
        $DBRESULT =& $this->local_pearDB->query($rq);
        if (!$DBRESULT->numRows())
        	return $string;
        $row =& $DBRESULT->fetchRow();
        
        /*
         * replace if not template
         */
        if ($row['host_register']) {
			if (strpos($string, "\$HOSTADDRESS$"))
	 			$string = str_replace("\$HOSTADDRESS\$", $this->getHostAddress($host_id), $string);
			if (strpos($string, "\$HOSTNAME$"))
	 			$string = str_replace("\$HOSTNAME\$", $this->getHostName($host_id), $string); 		
			if (strpos($string, "\$HOSTALIAS$"))
	 			$string = str_replace("\$HOSTALIAS\$", $this->getHostAlias($host_id), $string);
        }
        unset($row);
        	
 		$matches = array();
 		$pattern = '|(\$_HOST[0-9a-zA-Z\_\-]+\$)|';
 		preg_match_all($pattern, $string, $matches);
 		$i = 0; 		
 		while (isset($matches[1][$i])) {	 			 			
 			$rq = "SELECT host_macro_value FROM on_demand_macro_host WHERE host_host_id = '".$host_id."' AND host_macro_name LIKE '".$matches[1][$i]."'"; 			
 			$DBRES =& $this->local_pearDB->query($rq); 			 			
	 		while ($row =& $DBRES->fetchRow()) {
	 			$string = str_replace($matches[1][$i], $row['host_macro_value'], $string);
	 		} 			 			 			
 			$i++;		
 		}
 		if ($i) {
	 		$rq2 = "SELECT host_tpl_id FROM host_template_relation WHERE host_host_id = '".$host_id."' ORDER BY `order`";
	 		$DBRES2 =& $this->local_pearDB->query($rq2);
	 		while ($row2 =& $DBRES2->fetchRow()) {
	 			$string = $this->replaceMacroInString($row2['host_tpl_id'], $string);
	 		}
 		}
		return $string;
	}
}
 
?>