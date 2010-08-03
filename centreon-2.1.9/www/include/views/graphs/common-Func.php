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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/views/graphs/common-Func.php $
 * SVN : $Id: common-Func.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	function getServiceGroupCount($search = NULL)	{
		global $pearDB;

		if ($search != "")
			$DBRESULT =& $pearDB->query("SELECT count(sg_id) FROM `servicegroup` WHERE sg_name LIKE '%$search%'");
		else
			$DBRESULT =& $pearDB->query("SELECT count(sg_id) FROM `servicegroup`");
		$num_row =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		return $num_row["count(sg_id)"];
	}

	function getMyHostGraphs($host_id = NULL)	{
		global $pearDBO;
		if (!isset($host_id))
			return NULL;
		$tab_svc = array();

		$DBRESULT =& $pearDBO->query("SELECT `service_id` FROM `index_data` WHERE `host_id` = '".$host_id."' AND `hidden` = '0' AND `trashed` = '0' ORDER BY `service_description`");
		while ($row =& $DBRESULT->fetchRow())
			$tab_svc[$row["service_id"]] = 1;
		return $tab_svc;
	}
	
	function getHostGraphedList()	{
		global $pearDBO;
		$tab = array();
		$DBRESULT =& $pearDBO->query("SELECT `host_id` FROM `index_data` WHERE `hidden` = '0' AND `trashed` = '0' ORDER BY `host_name`");
		while ($row =& $DBRESULT->fetchRow())
			$tab[$row["host_id"]] = 1;
		return $tab;
	}
	
	function checkIfServiceSgIsEn($host_id = NULL, $service_id = NULL)	{
		global $pearDBO;
		if (!isset($host_id) || !isset($service_id))
			return NULL;
		$tab_svc = array();

		$DBRESULT =& $pearDBO->query("SELECT `service_id` FROM `index_data` WHERE `host_id` = '".$host_id."' AND `service_id` = '".$service_id."' AND `hidden` = '0' AND `trashed` = '0'");
		$num_row =& $DBRESULT->numRows();
		$DBRESULT->free();
		return $num_row;
	}

 
?>