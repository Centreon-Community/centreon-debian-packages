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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genContacts.php $
 * SVN : $Id: genContacts.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	if (!isset($oreon))
		exit();
	
	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/contacts.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM contact ORDER BY `contact_name`");
	$contact = array();
	$i = 1;
	$str = NULL;
	while ($contact =& $DBRESULT->fetchRow())	{
		$BP = false;
		array_key_exists($contact["contact_id"], $gbArr[0]) ? $BP = true : NULL;
		if ($BP)	{
			$ret["comment"] ? ($str .= "# '".$contact["contact_name"]."' contact definition ".$i."\n") : NULL;
			if ($ret["comment"] && $contact["contact_comment"])	{
				$comment = array();
				$comment = explode("\n", $contact["contact_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define contact{\n";
			if ($contact["contact_name"]) $str .= print_line("contact_name", $contact["contact_name"]);
			if ($contact["contact_alias"]) $str .= print_line("alias", $contact["contact_alias"]);
			// Nagios 2 : Contact Groups in Contact
			if ($oreon->user->get_version() >= 2)	{
				$contactGroup = array();
				$strTemp = NULL;
				$DBRESULT2 =& $pearDB->query("SELECT cg.cg_name, cg.cg_id FROM contactgroup_contact_relation ccr, contactgroup cg WHERE ccr.contact_contact_id = '".$contact["contact_id"]."' AND ccr.contactgroup_cg_id = cg.cg_id ORDER BY `cg_name`");
				while($contactGroup =& $DBRESULT2->fetchRow())	{
					$BP = false;
					array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
					if ($BP)
						$strTemp != NULL ? $strTemp .= ", ".$contactGroup["cg_name"] : $strTemp = $contactGroup["cg_name"];
				}
				$DBRESULT2->free();
				if ($strTemp) $str .= print_line("contactgroups", $strTemp);
				unset($contactGroup);
				unset($strTemp);
			}
			// Timeperiod for host & service
			$timeperiod = array();
			$DBRESULT2 =& $pearDB->query("SELECT cct.timeperiod_tp_id AS cctTP1, cct.timeperiod_tp_id2 AS cctTP2, tp.tp_id, tp.tp_name FROM contact cct, timeperiod tp WHERE cct.contact_id = '".$contact["contact_id"]."' AND (tp.tp_id = cct.timeperiod_tp_id OR tp.tp_id = cct.timeperiod_tp_id2) ORDER BY `cctTP1`");
			while($timeperiod =& $DBRESULT2->fetchRow())	{
				$timeperiod["cctTP1"] == $timeperiod["tp_id"] ? $str .= print_line("host_notification_period", $timeperiod["tp_name"]) : NULL;
				$timeperiod["cctTP2"] == $timeperiod["tp_id"] ? $str .= print_line("service_notification_period", $timeperiod["tp_name"]) : NULL;
			}
			$DBRESULT2->free();
			unset($timeperiod);
			if ($contact["contact_host_notification_options"]) $str .= print_line("host_notification_options", $contact["contact_host_notification_options"]);
			if ($contact["contact_service_notification_options"]) $str .= print_line("service_notification_options", $contact["contact_service_notification_options"]);
			// Host & Service notification command
			$command = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM contact_hostcommands_relation chr, command cmd WHERE chr.contact_contact_id = '".$contact["contact_id"]."' AND chr.command_command_id = cmd.command_id ORDER BY `command_name`");
			while($command =& $DBRESULT2->fetchRow())
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("host_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			$command = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cmd.command_name FROM contact_servicecommands_relation csr, command cmd WHERE csr.contact_contact_id = '".$contact["contact_id"]."' AND csr.command_command_id = cmd.command_id ORDER BY `command_name`");
			while($command =& $DBRESULT2->fetchRow())
				$strTemp != NULL ? $strTemp .= ", ".$command["command_name"] : $strTemp = $command["command_name"];
			$DBRESULT2->free();
			if ($strTemp) $str .= print_line("service_notification_commands", $strTemp);
			unset($command);
			unset($strTemp);
			// Misc
			if ($contact["contact_email"]) $str .= print_line("email", $contact["contact_email"]);
			if ($contact["contact_pager"]) $str .= print_line("pager", $contact["contact_pager"]);
			$str .= "}\n\n";
			$i++;
		}
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/contacts.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($contact);
	unset($str);
	unset($i);
?>