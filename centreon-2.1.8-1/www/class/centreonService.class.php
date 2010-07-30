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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/class/centreonService.class.php $
 * SVN : $Id: centreonService.class.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
 /*
  *  Class that contains various methods for managing services
  */
 class CentreonService {
 	private $local_pearDB; 	
 	
 	/*
 	 *  Constructor
 	 */
 	function CentreonService($pearDB) {
 		$this->local_pearDB = $pearDB;
 	}
 	
 	/*
 	 *  Method that returns service description from service_id
 	 */
 	public function getServiceDesc($svc_id) {
 		$rq = "SELECT service_description FROM service WHERE service_id = '".$svc_id."' LIMIT 1";
 		$DBRES =& $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows())
 			return NULL;
 		$row =& $DBRES->fetchRow(); 		
 		return $row['service_description'];
 	}
 	
 	/*
 	 *  Method that returns the id of a service
 	 */
 	public function getServiceId($svc_desc, $host_name) { 		
        $rq = "SELECT s.service_id " . 
 			  "FROM service s, host_service_relation hsr, host h " . 
 			  "WHERE s.service_id = hsr.service_service_id " .
 			  "AND hsr.host_host_id = h.host_id " .
 			  "AND h.host_name = '".$host_name."' " .
 			  "AND s.service_description = '".$svc_desc."' LIMIT 1";
 		$DBRES = $this->local_pearDB->query($rq);
 		if (!$DBRES->numRows()) {
 			return null;
 		}
 		$row = $DBRES->fetchRow(); 		
 		return $row['service_id'];
 	}
 	 	
 	/*
 	 *  Returns a string that replaces on demand macros by their values
 	 */
 	public function replaceMacroInString($svc_id, $string) { 		 		 		 		
 		$rq = "SELECT service_register FROM service WHERE service_id = '".$svc_id."' LIMIT 1";
        $DBRES =& $this->local_pearDB->query($rq);
        if (!$DBRES->numRows())
        	return $string;
        $row =& $DBRES->fetchRow();
        
        /*
         * replace if not template
         */
        if ($row['service_register']) {
	 		if (preg_match("/$SERVICEDESC$/", $string))
	 			$string = str_replace("\$SERVICEDESC\$", $this->getServiceDesc($svc_id), $string); 		
        }
 		$matches = array();
 		$pattern = '|(\$_SERVICE[0-9a-zA-Z\_\-]+\$)|';
 		preg_match_all($pattern, $string, $matches);
 		$i = 0; 		
 		while (isset($matches[1][$i])) {	 			 			
 			$rq = "SELECT svc_macro_value FROM on_demand_macro_service WHERE svc_svc_id = '".$svc_id."' AND svc_macro_name LIKE '".$matches[1][$i]."'"; 			
 			$DBRES =& $this->local_pearDB->query($rq); 			 			
	 		while ($row =& $DBRES->fetchRow()) {
	 			$string = str_replace($matches[1][$i], $row['svc_macro_value'], $string);
	 		} 			 			 			
 			$i++;		
 		}
 		if ($i) {
	 		$rq2 = "SELECT service_template_model_stm_id FROM service WHERE service_id = '".$svc_id."'";
	 		$DBRES2 =& $this->local_pearDB->query($rq2);
	 		while ($row2 =& $DBRES2->fetchRow()) {
	 			$string = $this->replaceMacroInString($row2['service_template_model_stm_id'], $string);
	 		}
 		}
 		return $string;
 	}
 }
 
 ?>