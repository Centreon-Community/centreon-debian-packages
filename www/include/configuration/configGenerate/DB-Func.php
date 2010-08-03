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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/DB-Func.php $
 * SVN : $Id: DB-Func.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
 	/*
 	 * Create Service Template Cache
 	 */
	
	function getMyServiceTPInCache($service_id = NULL, $cache)	{
		if (!$service_id) 
			return;
		
		while (1) {
			if (isset($cache[$service_id]["tp"])) {
				return $cache[$service_id]["tp"];
			} else if (isset($cache[$service_id]["tpl"])) {
				$service_id = $cache[$service_id]["tpl"];
			} else {
				break;
			}
		}
	}

	/*
	 * Get contact List
	 */
	 
	
	/*
	 * Convert Service Sp�cial Char for generation
	 */ 

	function convertServiceSpecialChar($str) {
		$str = str_replace('#S#', "/", $str);
		$str = str_replace('#BS#', "\\", $str);
		return $str;
	}

	/*
	 * Purge Args for commands
	 */

	function convertServiceSpecialCharInCmd($str) {
		$str = str_replace('#BR#', "\\n", $str);
		$str = str_replace('#T#', "\\t", $str);
		$str = str_replace('#R#', "\\r", $str);
		$str = str_replace('#S#', "/", $str);
		$str = str_replace('#BS#', "\\", $str);
		return $str;
	}
	
	function myHour($hour) {
		if (!$hour)
			return "00";
		if ($hour < 10)
			return "0".$hour;
		return $hour; 
	}
	
	function myMinute($min) {
		if (!$min)
			return "00";
		if ($min < 10 && $min > 0)
			return "0".$min;
		return $min; 
	}
	
	/*
	 * Compute values for time range
	 */
	
	function ComputeGMTTime($day, $daybefore, $dayafter, $gmt, $conf) {
		global $PeriodBefore, $PeriodAfter, $Period;
		$tabPeriod = split(",", $conf);
		foreach ($tabPeriod as $period) {
			/*
			 * Match hours
			 */
			preg_match("/([0-9]*)\:([0-9]*)\-([0-9]*):([0-9]*)/", $period, $tabValue);

			if ($gmt < 0) {
				$tabValue[1] += $gmt;
				$tabValue[3] += $gmt;
				
				if ($tabValue[1] < 0 && $tabValue[3] < 0) {				
					$value = (24 + $tabValue[1]);
					$value = myHour($value); 
					if ($PeriodBefore[$daybefore] != "")
						$PeriodBefore[$daybefore] .= ",";
					$PeriodBefore[$daybefore] .= $value.":".$tabValue[2]."-".(24 + $tabValue[3]).":".myMinute($tabValue[4]);
				} else if ($tabValue[1] < 0 && $tabValue[3] > 0) {
					$value = ((24 + $tabValue[3]) % 24);
					if ($Period[$day] != "")
						$Period[$day] .= ",";
					$Period[$day] .= "00:00-".myHour($value).":".(($tabValue[4] < 10 && $tabValue[4] > 0) ? "0".$tabValue[4] : $tabValue[4]);
					if ($PeriodBefore[$daybefore] != "")
						$PeriodBefore[$daybefore] .= ",";
					$PeriodBefore[$daybefore] .= (24 + $tabValue[1]).":".myMinute($tabValue[2])."-24:00";
				} else if ($tabValue[1] < 0 && $tabValue[3] == 0) {
					$value = ((24 + $tabValue[3]) % 24);
					if ($Period[$day] != "")
						$Period[$day] .= ",";
					$Period[$day] .= "00:00-".myHour($value).":".(($tabValue[4] < 10 && $tabValue[4] > 0) ? "0".$tabValue[4] : $tabValue[4]);
					if ($PeriodBefore[$daybefore] != "")
						$PeriodBefore[$daybefore] .= ",";
					$PeriodBefore[$daybefore] .= (24 + $tabValue[1]).":".myMinute($tabValue[2])."-24:00";
				} else {
					$value = ($tabValue[1] < 0 ? 24 + $tabValue[1] : $tabValue[1]);
					if ($Period[$day] != "")
						$Period[$day] .= ",";
					$tabValue[3] = ($tabValue[3] < 0 ? 24 + $tabValue[3] : $tabValue[3]);
					$Period[$day] .= myHour($value).":".myMinute($tabValue[2])."-".(($tabValue[3] < 10 && $tabValue[3] > 0) ? "0".$tabValue[3] : $tabValue[3]).":".myMinute($tabValue[4]);
				}
			} else if ($gmt > 0) {
				$tabValue[1] += $gmt;
				$tabValue[3] += $gmt;
				if ($tabValue[1] >= 24 && $tabValue[3] > 24) {				
					if ($PeriodAfter[$dayafter] != "")
						$PeriodAfter[$dayafter] .= ",";
					$PeriodAfter[$dayafter] .= ($tabValue[1] % 24).":".myMinute($tabValue[2])."-".($tabValue[3] % 24).":".myMinute($tabValue[4])."";
				} else if ($tabValue[1] < 24 && $tabValue[3] > 24) {
					if ($Period[$day] != "")
						$Period[$day] .= ",";
					$Period[$day] .= myMinute($tabValue[1]).":".$tabValue[2]."-"."24:00";
					$tabValue[3] = $tabValue[3] % 24;
					if ($PeriodAfter[$dayafter] != "")
						$PeriodAfter[$dayafter] .= ",";
					$PeriodAfter[$dayafter] .= "00:00-".myHour($tabValue[3]) .":".myMinute($tabValue[4])."";
				} else if ($tabValue[1] == 24 && $tabValue[3] == 24) {			
					if ($PeriodAfter[$dayafter] != "")
						$PeriodAfter[$dayafter] .= ",";
					$PeriodAfter[$dayafter] .= "00:".myMinute($tabValue[2])."-00:".myMinute($tabValue[4]);			
				} else {
					if (($tabValue[3] == 24 && $tabValue[4] > 0)) {
						if ($PeriodAfter[$dayafter] != "")
							$PeriodAfter[$dayafter] .= ",";
						$PeriodAfter[$dayafter] .= "00:00-00:".myMinute($tabValue[4]);
						$tabValue[4] = "00";
					}
					if ($Period[$day] != "")
						$Period[$day] .= ",";
					$Period[$day] .= myMinute($tabValue[1]).":".myMinute($tabValue[2])."-".myMinute($tabValue[3]).":".myMinute($tabValue[4]);			
				}
			} else if ($gmt == 0) {
				if ($Period[$day] != "")
					$Period[$day] .= ",";
				$Period[$day] .= $tabValue[1].":".$tabValue[2]."-".$tabValue[3].":".$tabValue[4];
			}		
		}
	}
	

	function isHostOnThisInstance($host_id, $instance_id){
		global $pearDB;
		$DBRESULT_relation =& $pearDB->query("SELECT * FROM ns_host_relation WHERE host_host_id = '".$host_id."' AND nagios_server_id = '".$instance_id."'");
		if ($DBRESULT_relation->numRows())
			return 1;
		else
			return 0;
	}
	
	function isLocalInstance($instance_id){
		global $pearDB;
		
		$DBRESULT_relation =& $pearDB->query("SELECT localhost FROM nagios_server WHERE id = '".$instance_id."'");
		$data = $DBRESULT_relation->fetchRow();
		return $data["localhost"];
	}

	function manageDependencies($ret = array())	{
		global $pearDB, $form;
		
		/*
		 * Init Dependancies table
		 */
		$gbArr = array();
		$gbArr = checkDependenciesStrong();
		
		return ($gbArr);
	}
	
	function checkDependenciesStrong()	{
		global $pearDB,  $oreon;
		$cctEnb = array();
		$cgEnb = array();
		$hostEnb = array();
		$hgEnb = array();
		$svEnb = array();
		$sgEnb = array();
		$gbEnb = array(0 => &$cctEnb, 1 => &$cgEnb, 2 => &$hostEnb, 3 => &$hgEnb, 4 => &$svEnb, 5 => &$sgEnb);
		
		/*
		 * Contact
		 */
		$contact = array();
		$DBRESULT =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_activate ='1'");
		while ($contact =& $DBRESULT->fetchRow())	{
			if ($oreon->user->get_version() == 2) {
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cg.cg_activate FROM contactgroup_contact_relation cgcr, contactgroup cg WHERE cgcr.contact_contact_id = '".$contact["contact_id"]."' AND cg.cg_id = cgcr.contactgroup_cg_id");
				while ($contactGroup =& $DBRESULT2->fetchRow())	{
					if ($contactGroup["cg_activate"])
						$cctEnb[$contact["contact_id"]] = 1;
					unset($contactGroup);
				}
			} else {
				$cctEnb[$contact["contact_id"]] = 1;
			}
			unset($contact);				
		}
		$DBRESULT->free();

		/*
		 * ContactGroup
		 */
		$contactGroup = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT cgcr.contactgroup_cg_id, cgcr.contact_contact_id FROM contactgroup cg, contactgroup_contact_relation cgcr WHERE cg.cg_activate ='1' AND cgcr.contactgroup_cg_id = cg.cg_id");
		while ($contactGroup =& $DBRESULT->fetchRow())
			isset($cctEnb[$contactGroup["contact_contact_id"]]) ? $cgEnb[$contactGroup["contactgroup_cg_id"]] = 1 : NULL;
		unset($contactGroup);
		$DBRESULT->free();
		
		/*
		 * Host Template Model
		 */
		$host = array();
		$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host.host_register = '0' AND host.host_activate = '1'");
		while($host =& $DBRESULT->fetchRow())
			$hostEnb[$host["host_id"]] = $host["host_name"];
		$DBRESULT->free();
		
		/*
		 * Host
		 */
		 
		 	/*
		 	 * Create template buffer
		 	 */
		 	if ($oreon->user->get_version() >= 3) {
				$hostTemplate = array();
				$DBRESULT =& $pearDB->query("SELECT htr.host_tpl_id, host.host_id FROM host_template_relation htr, host WHERE host.host_id = htr.host_host_id");
				while ($htpl =& $DBRESULT->fetchRow()) {
					$hostTemplate[$htpl["host_id"]]	= $htpl["host_tpl_id"];
				}
			}
		 	
		$host = array();
		if ($oreon->user->get_version() == 2) {
			$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id, host_id FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");			
		} else if ($oreon->user->get_version() >= 3) {
			$DBRESULT =& $pearDB->query("SELECT host.host_id, host.host_name FROM host WHERE host.host_register = '1' AND host.host_activate = '1'");				
		}
		
		while ($host =& $DBRESULT->fetchRow())	{				
			/*
			 * If the Host is link to a Template, we think that the dependencies are manage in the template
			 */
			if (isset($host["host_template_model_htm_id"]) && $host["host_template_model_htm_id"])	{										
				if (isset($host["host_template_model_htm_id"]) && isset($host["host_template_model_htm_id"][$hostEnb]))					
					$hostEnb[$host["host_id"]] = $host["host_name"];											
			} else if ($oreon->user->get_version() >= 3 && isset($hostTemplate[$host["host_id"]])) {										
				$hostEnb[$host["host_id"]] = $host["host_name"];
			} else {	
				$hostEnb[$host["host_id"]] = $host["host_name"];
				/*
				 * Contactgroup dependancy
				 */								
				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT cghr.contactgroup_cg_id FROM contactgroup_host_relation cghr WHERE cghr.host_host_id = '".$host["host_id"]."'");
				while ($valid =& $DBRESULT2->fetchRow())
					isset($cgEnb[$valid["contactgroup_cg_id"]]) ? $hostEnb[$host["host_id"]] = $host["host_name"] : NULL;
				$DBRESULT2->free();
				unset($valid);
				
				/*
				 * Contact dependancy
				 */	
				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT chr.contact_id FROM contact_host_relation chr WHERE chr.host_host_id = '".$host["host_id"]."'");
				while ($valid =& $DBRESULT2->fetchRow())
					isset($cctEnb[$valid["contact_id"]]) ? $hostEnb[$host["host_id"]] = $host["host_name"] : NULL;
				$DBRESULT2->free();
				unset($valid);
				
			}
		}
		$DBRESULT->free();
		unset($host);
		
		/*
		 * Host Group
		 */
		$hostGroup = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT hg.hg_id FROM hostgroup hg WHERE hg.hg_activate = '1'");
		while ($hostGroup =& $DBRESULT->fetchRow())	{						
			$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hgr.host_host_id, hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$hostGroup["hg_id"]."'");
			while ($hostGroup =& $DBRESULT2->fetchRow()) {
				if (isset($hostEnb[$hostGroup["host_host_id"]])) {
					$hgEnb[$hostGroup["hostgroup_hg_id"]] = 1;
					break;
				}
			}
			$DBRESULT2->free();
		}
		$DBRESULT->free();
		unset($hostGroup);
	
		/*
		 * Service Template Model
		 */
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sv.service_id FROM service sv WHERE sv.service_activate = '1' AND service_register = '0'");
		while ($service = $DBRESULT->fetchRow()) {
			$svEnb[$service["service_id"]] = 1;
		}
		$DBRESULT->free();
		
		/*
		 * Service
		 */
		
		$service = array();
		$DBRESULT =& $pearDB->query("SELECT DISTINCT service_id, service_description, service_template_model_stm_id " .
									"FROM service " .
									"WHERE service_activate = '1' " .
									"AND service_register = '1'");
		while ($service =& $DBRESULT->fetchRow())	{
			/*
			 * If the Service is link to a Template, we think that 
			 * the dependencies are manage in the template
			 */			
			if ($service["service_template_model_stm_id"] && isset($svEnb[$service["service_template_model_stm_id"]])) {
				$svEnb[$service["service_id"]] = 1;
			} else {
				$h = false;
				$hg = false;
				$cg = false;
				$cct = false;
				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hsr.host_host_id, hsr.hostgroup_hg_id, cgsr.contactgroup_cg_id FROM contactgroup_service_relation cgsr, host_service_relation hsr WHERE cgsr.service_service_id = '".$service["service_id"]."' AND hsr.service_service_id = '".$service["service_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while ($valid =& $DBRESULT2->fetchRow())	{					
					isset($hostEnb[$valid["host_host_id"]]) ? $h = true : NULL;					
					isset($hgEnb[$valid["hostgroup_hg_id"]]) ? $hg = true : NULL;					
					isset($cgEnb[$valid["contactgroup_cg_id"]]) ? $cg = true : NULL;
				}
				$DBRESULT2->free();
				unset($valid);
				
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT hsr.host_host_id, hsr.hostgroup_hg_id, csr.contact_id FROM contact_service_relation csr, host_service_relation hsr WHERE csr.service_service_id = '".$service["service_id"]."' AND hsr.service_service_id = '".$service["service_id"]."'");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				while ($valid =& $DBRESULT2->fetchRow())	{					
					isset($hostEnb[$valid["host_host_id"]]) ? $h = true : NULL;					
					isset($hgEnb[$valid["hostgroup_hg_id"]]) ? $hg = true : NULL;					
					isset($cctEnb[$valid["contact_id"]]) ? $cct = true : NULL;
				}
				$DBRESULT2->free();
				unset($valid);
				
				if (($h || $hg) && ($cg || $cct)) {
					$svEnb[$service["service_id"]] = $service["service_description"];		
				}				
				unset($valid);
			}
		}
		$DBRESULT->free();
		
		
		/*
		 * Service Group
		 */
		$serviceGroup = array();
		$DBRESULT =& $pearDB->query("SELECT sg_id, sg_name FROM servicegroup sg WHERE sg.sg_activate = '1'");
		while ($serviceGroup =& $DBRESULT->fetchRow())	{
			$DBRESULT2 =& $pearDB->query("SELECT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$serviceGroup["sg_id"]."'");
			while ($valid =& $DBRESULT2->fetchRow()) {
				if (isset($svEnb[$valid["service_service_id"]])) {
					$sgEnb[$serviceGroup["sg_id"]] = $serviceGroup["sg_name"];
				}
			}
			$DBRESULT2->free();
		}
		unset($serviceGroup);
		$DBRESULT->free();
		
		/*
		 * Meta Service
		 */
		$oms = array();
		$DBRESULT =& $pearDB->query("SELECT meta_id FROM meta_service WHERE meta_activate = '1'");
		while ($oms =& $DBRESULT->fetchRow())
			$omsEnb[$oms["meta_id"]] = 1;
		unset($oms);
		$DBRESULT->free();
		
		return ($gbEnb);
	}
	
	function print_header($handle, $name)	{
		$time = date("F j, Y, g:i a");
		$by = $name;
		$str  = "###################################################################\n";
		$len = strlen($str); // Get line lenght
		$str .= "#                                                                 #\n";
		$str .= "#                       GENERATED BY CENTREON                     #\n";
		$str .= "#                                                                 #\n";
		$str .= "#               Developped by :                                   #\n";
		$str .= "#                   - Julien Mathis                               #\n";
		$str .= "#                   - Romain Le Merlus                            #\n";
		$str .= "#                                                                 #\n";
		$str .= "#                           www.centreon.com                      #\n";
		$str .= "#                For information : contact@centreon.com           #\n";
		$str .= "###################################################################\n";
		$str .= "#                                                                 #\n";
		$str .= "#         Last modification " . $time;
		
		$len_time = strlen($time);
		$DBRESULT = $len - 28 - $len_time - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $DBRESULT; $i++)
			$str  .= " ";
		
		$str .= "#\n";
		$str .= "#         By " . $by;
		$len_by = strlen($by);
		$DBRESULT = $len - 13 - $len_by - 2;
		
		// Add space to put text on center
		for ($i = 0; $i != $DBRESULT; $i++)
			$str  .= " ";
		$str .= "#\n";
		$str .= "#                                                                 #\n";
		$str .= "###################################################################\n\n";
		fwrite($handle, $str);
	}
	
	// Create File, print header and return handle. 	
	function create_file($filename, $name, $header = true)	{
		if (!$handle = fopen($filename, 'w')) {         
	    	echo _("Can't access to needed file").$filename;         
	    	exit;
		}
		$header ? print_header($handle, $name) : NULL;
	   	return $handle;
	}
	
	// write data into the file	
	function write_in_file($handle, $content, $filename)	{
		if (strcmp($content, "") && !fwrite($handle, $content)) {
			echo _("Can't access to needed file").$filename; 
			exit();
		}
	}
	
	// Put text in good format	
	function print_line($data1, $data2)	{
	  $len = strlen($data1);
	  if ($len <= 9)
	    return "\t" . $data1 . "\t\t\t\t" . $data2 . "\n";
	  else if ($len > 9 && $len <= 18)
	    return "\t" . $data1 . "\t\t\t" . $data2 . "\n";
	  else if ($len >= 19 && $len <= 27)
	    return "\t" . $data1 . "\t\t" . $data2 . "\n";
	  else if ($len > 27)
	    return "\t" . $data1 . "\t" . $data2 . "\n";
	}
	
	function removeSpecialChar($str){
		$str = str_replace('#BR#', "\\n", $str);
		$str = str_replace('#T#', "\\t", $str);
		$str = str_replace('#R#', "\\r", $str);
		$str = str_replace('#S#', "/", $str);
		$str = str_replace('#BS#', "\\", $str);
		return $str;
	}
	
	function verifyIfMustBeGenerated($id, $gbArr, $ret){
		$BP = false;
		if (isset($gbArr[$id]))
			$BP = true;	
		return $BP;
	}
	
/*
Function to replace PHP's parse_ini_file() with much fewer restritions, and
a matching function to write to a .INI file, both of which are binary safe.

Version 1.0

Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>

Sections can use any character excluding ASCII control characters and ASCII
DEL.  (You may even use [ and ] characters as literals!)

Keys can use any character excluding ASCII control characters, ASCII DEL,
ASCII equals sign (=), and not start with the user-defined comment
character.

Values are binary safe (encoded with C-style backslash escape codes) and may
be enclosed by double-quotes (to retain leading & trailing spaces).

User-defined comment character can be any non-white-space ASCII character
excluding ASCII opening bracket ([).

readINIfile() is case-insensitive when reading sections and keys, returning
an array with lower-case keys.
writeINIfile() writes sections and keys with first character capitalization.
Invalid characters are converted to ASCII dash / hyphen (-).  Values are
always enclosed by double-quotes.

writeINIfile() also provides a method to automatically prepend a comment
header from ASCII text with line breaks, regardless of whether CRLF, LFCR,
CR, or just LF line break sequences are used!  (All line breaks are
translated to CRLF)

Modified for Oreon by Christophe Coraboeuf
*/

	function readINIfile ($filename, $commentchar) {
	  $array1 = array();
	  $array2 = array();
	  $array1 = file($filename);
	  $section = '';
	  foreach ($array1 as $filedata) {
	   $dataline = trim($filedata);
	   $firstchar = substr($dataline, 0, 1);
	   if ($firstchar!=$commentchar && $dataline!='') {
	     //It's an entry (not a comment and not a blank line)
	     if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
	       //It's a section
	       $section = strtoupper(substr($dataline, 1, -1));
	     }else{
	       //It's a key...
	       $delimiter = strpos($dataline, '=');
	       if ($delimiter > 0) {
	         //...with a value
	         $key = strtoupper(trim(substr($dataline, 0, $delimiter)));
	         $value = trim(substr($dataline, $delimiter + 1));
	         if (substr($value, 0, 1) == '"' && substr($value, -1, 1) == '"') { $value = substr($value, 1, -1); }
	         $array2[$section][$key] = stripcslashes($value);
	       }else{
	         //...without a value
	         $array2[$section][strtoupper(trim($dataline))]='';
	       }
	     }
	   }else{
	     //It's a comment or blank line.  Ignore.
	   }
	  }
	  return $array2;
	}
	
	function writeINIfile ($filename, $array1, $commentchar, $commenttext) {
	  	$handle = fopen($filename, 'wb');
	  	if ($commenttext!='') {
	   		$comtext = $commentchar.
	     	str_replace($commentchar, "\r\n".$commentchar,
	       	str_replace ("\r", $commentchar,
	        str_replace("\n", $commentchar,
	        str_replace("\n\r", $commentchar,
	        str_replace("\r\n", $commentchar, $commenttext)))));
	   		if (substr($comtext, -1, 1)==$commentchar && substr($comtext, -1, 1)!=$commentchar) {
	     		$comtext = substr($comtext, 0, -1);
	   		}
	   		fwrite ($handle, $comtext."\r\n");
	  	}
	 	foreach ($array1 as $sections => $items) {
	   		//Write the section
	   		if (isset($section)) { fwrite ($handle, "\r\n"); }
	   		//$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
	   		$section = strtoupper(preg_replace('/[\0-\37]|\177/', "-", $sections));
	   		fwrite ($handle, "[".$section."]\r\n");
	   		foreach ($items as $keys => $values) {
	     		//Write the key/value pairs
	     		$key = strtoupper(preg_replace('/[\0-\37]|=|\177/', "-", $keys));
	     		if (substr($key, 0, 1)==$commentchar) { $key = '-'.substr($key, 1); }
	   			//  if (substr($values, 0, 1) == '"' && substr($values, -1, 1) == '"') { $values = substr($values, 1, -1); }
	     		$value = ucfirst(addcslashes($values,''));
	     		fwrite ($handle, '    '.$key.'='.$value."\r\n");
	   		}
	  	}
	  	fclose($handle);
	}
?>