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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genTimeperiods.php $
 * SVN : $Id: genTimeperiods.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
	
	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/"))
		mkdir($nagiosCFGPath.$tab['id']."/");
	
	$handle = create_file($nagiosCFGPath.$tab['id']."/timeperiods.cfg", $oreon->user->get_name());
	
	/*
	 * Generate Standart Timeperiod
	 */
	$timeperiods = array();
	$i = 1;
	$str = NULL;
	$DBRESULT =& $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
	while ($timePeriod =& $DBRESULT->fetchRow()) {
		$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"] . "' timeperiod definition " . $i . "\n") : NULL;
		$str .= "define timeperiod{\n";
		if ($timePeriod["tp_name"]) 
			$str .= print_line("timeperiod_name", $timePeriod["tp_name"]);
		if ($timePeriod["tp_alias"]) 
			$str .= print_line("alias", $timePeriod["tp_alias"]);
		if ($timePeriod["tp_sunday"]) 
			$str .= print_line("sunday", $timePeriod["tp_sunday"]);
		if ($timePeriod["tp_monday"]) 
			$str .= print_line("monday", $timePeriod["tp_monday"]);
		if ($timePeriod["tp_tuesday"]) 
			$str .= print_line("tuesday", $timePeriod["tp_tuesday"]);
		if ($timePeriod["tp_wednesday"]) 
			$str .= print_line("wednesday", $timePeriod["tp_wednesday"]);
		if ($timePeriod["tp_thursday"]) 
			$str .= print_line("thursday", $timePeriod["tp_thursday"]);
		if ($timePeriod["tp_friday"]) 
			$str .= print_line("friday", $timePeriod["tp_friday"]);
		if ($timePeriod["tp_saturday"]) 
			$str .= print_line("saturday", $timePeriod["tp_saturday"]);
		$str .= "}\n\n";
		$i++;
		$timeperiods[$timePeriod["tp_id"]] = $timePeriod["tp_name"];
		unset($timePeriod);
	}	
	
	if ($oreon->CentreonGMT->used() == 1) {
		$GMTList = $oreon->CentreonGMT->listGTM;
		foreach ($GMTList as $gmt => $value) {
			$DBRESULT =& $pearDB->query("SELECT * FROM `timeperiod` ORDER BY `tp_name`");
			while ($timePeriod =& $DBRESULT->fetchRow())	{
				$PeriodBefore 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$Period 		= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				$PeriodAfter 	= array("monday" => "", "tuesday" => "", "wednesday" => "", "thursday" => "", "friday" => "", "saturday" => "", "sunday" => "");
				
				$ret["comment"] ? ($str .= "# '" . $timePeriod["tp_name"]."_GMT".$gmt . "' timeperiod definition " . $i . "\n") : NULL;
				$str .= "define timeperiod{\n";
				if ($timePeriod["tp_name"]) 
					$str .= print_line("timeperiod_name", $timePeriod["tp_name"]."_GMT".$gmt);
	
				if ($timePeriod["tp_alias"]) 
					$str .= print_line("alias", $timePeriod["tp_alias"]);
	
				if ($timePeriod["tp_sunday"])
					ComputeGMTTime("sunday", "saturday", "monday", $gmt, $timePeriod["tp_sunday"]);
				
				if ($timePeriod["tp_monday"]) 
					ComputeGMTTime("monday", "sunday", "tuesday", $gmt, $timePeriod["tp_monday"]);
				
				if ($timePeriod["tp_tuesday"]) 
					ComputeGMTTime("tuesday", "monday", "wednesday", $gmt, $timePeriod["tp_tuesday"]);
				
				if ($timePeriod["tp_wednesday"])
					ComputeGMTTime("wednesday", "tuesday", "thursday", $gmt, $timePeriod["tp_wednesday"]);
				
				if ($timePeriod["tp_thursday"]) 
					ComputeGMTTime("thursday", "wednesday", "friday", $gmt, $timePeriod["tp_thursday"]);
				
				if ($timePeriod["tp_friday"]) 
					ComputeGMTTime("friday", "thursday", "saturday", $gmt, $timePeriod["tp_friday"]);
				
				if ($timePeriod["tp_saturday"]) 
					ComputeGMTTime("saturday", "friday", "sunday", $gmt, $timePeriod["tp_saturday"]);
				
				$i++;
				$timeperiods[$timePeriod["tp_id"]] = $timePeriod["tp_name"];
				unset($timePeriod);
				foreach ($Period as $day => $value){
					if (strlen($PeriodAfter[$day].$Period[$day].$PeriodBefore[$day]))
					  $str .= print_line($day, $PeriodAfter[$day].($Period[$day] != "" && $PeriodAfter[$day] != "" ? "," : "").$Period[$day].($PeriodBefore[$day] != "" && ($PeriodAfter[$day] != "" || $Period[$day] != "") ? "," : "").$PeriodBefore[$day]);
				}
				$str .= "}\n\n";
			}
			
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/timeperiods.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>