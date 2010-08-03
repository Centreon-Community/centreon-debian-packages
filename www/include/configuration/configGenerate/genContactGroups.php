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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genContactGroups.php $
 * SVN : $Id: genContactGroups.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();

	if (!is_dir($nagiosCFGPath.$tab['id']."/")) {
		mkdir($nagiosCFGPath.$tab['id']."/");
	}

	$handle = create_file($nagiosCFGPath.$tab['id']."/contactgroups.cfg", $oreon->user->get_name());
	$DBRESULT =& $pearDB->query("SELECT * FROM contactgroup ORDER BY `cg_name`");
	$contactGroup = array();
	$i = 1;
	$str = NULL;
	while($contactGroup =& $DBRESULT->fetchRow())	{
		$BP = false;
		array_key_exists($contactGroup["cg_id"], $gbArr[1]) ? $BP = true : NULL;
		if ($BP)	{
			$ret["comment"] ? ($str .= "# '" . $contactGroup["cg_name"] . "' contactgroup definition " . $i . "\n") : NULL ;
			if ($ret["comment"] && $contactGroup["cg_comment"])	{
				$comment = array();
				$comment = explode("\n", $contactGroup["cg_comment"]);
				foreach ($comment as $cmt)
					$str .= "# ".$cmt."\n";
			}
			$str .= "define contactgroup{\n";
			if ($contactGroup["cg_name"]) $str .= print_line("contactgroup_name", $contactGroup["cg_name"]);
			if ($contactGroup["cg_alias"]) $str .= print_line("alias", $contactGroup["cg_alias"]);
			$contact = array();
			$strTemp = NULL;
			$DBRESULT2 =& $pearDB->query("SELECT cct.contact_id, cct.contact_name FROM contactgroup_contact_relation ccr, contact cct WHERE ccr.contactgroup_cg_id = '".$contactGroup["cg_id"]."' AND ccr.contact_contact_id = cct.contact_id ORDER BY `contact_name`");
			while($contact =& $DBRESULT2->fetchRow())	{
				$BP = false;				
				array_key_exists($contact["contact_id"], $gbArr[0]) ? $BP = true : $BP = false;
				if ($BP)
					$strTemp != NULL ? $strTemp .= ", ".$contact["contact_name"] : $strTemp = $contact["contact_name"];
			}
			$DBRESULT2->free();
			$str .= print_line("members", $strTemp);
			unset($contact);
			unset($strTemp);
			$str .= "}\n\n";
			$i++;
		}
		unset($contactGroup);
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath.$tab['id']."/contactgroups.cfg");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
	unset($i);
?>