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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/menu/userMenuPreferences.php $
 * SVN : $Id: userMenuPreferences.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";
	require_once $centreon_path."/www/class/Oreon.class.php";
	require_once $centreon_path."/www/class/Session.class.php";
	
	session_start();
	if(!isset($_SESSION['oreon']) || !isset($_GET['div']) || !isset($_GET['uid']))
		exit();
	$oreon = $_SESSION['oreon'];	
	 
	$pearDB = new CentreonDB();
	
	/*
	 * Check session id
	 */
	$session =& $pearDB->query("SELECT user_id FROM `session` WHERE session_id = '".htmlentities(session_id(), ENT_QUOTES)."' AND user_id = '".htmlentities($_GET['uid'], ENT_QUOTES)."'");
	if (!$session->numRows()){
		exit;
	}
	
	if (isset($_GET['div']) && isset($_GET['uid'])) {
		
		$my_div = htmlentities($_GET['div'], ENT_QUOTES);
		$my_uid = htmlentities($_GET['uid'], ENT_QUOTES);
		
		$query = "SELECT cp_value FROM contact_param WHERE cp_contact_id = '".$my_uid."' AND cp_key = '_Div_".$my_div."' LIMIT 1";
		$DBRESULT =& $pearDB->query($query);
		if ($DBRESULT->numRows()) {		
			$row =& $DBRESULT->fetchRow();
			if ($row['cp_value'] == "1")
				$update_val = "0"; 
			else
				$update_val = "1";
			$query2 = "UPDATE contact_param set cp_value = '".$update_val."' WHERE cp_contact_id = '".$my_uid."' AND cp_key = '_Div_".$my_div."'";		
		} else
			$query2 = "INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) VALUES ('_Div_".$my_div."', '0', '".$my_uid."')";
		$pearDB->query($query2);		
	}
?>