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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/contactgroup/contactGroup.php $
 * SVN : $Id: contactGroup.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["cg_id"]) ? $cG = $_GET["cg_id"] : $cG = NULL;
	isset($_POST["cg_id"]) ? $cP = $_POST["cg_id"] : $cP = NULL;
	$cG ? $cg_id = $cG : $cg_id = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	/*
	 * Pear library
	 */
	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/contactgroup/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		case "a" : 
			/*
			 * Add a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break; 
		case "w" : 
			/*
			 * Watch a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break;
		case "c" : 
			/*
			 * Modify a contactgroup
			 */
			require_once($path."formContactGroup.php"); 
			break;
		case "s" : 
			/*
			 * Activate a contactgroup
			 */
			enableContactGroupInDB($cg_id); 
			require_once($path."listContactGroup.php"); 
			break;
		case "u" : 
			/*
			 * Desactivate a contactgroup
			 */
			disableContactGroupInDB($cg_id); 
			require_once($path."listContactGroup.php"); 
			break;
		case "m" : 
			/*
			 * Duplicate n contact group
			 */
			multipleContactGroupInDB(isset($select) ? $select : array(), $dupNbr); 
			require_once($path."listContactGroup.php"); 
			break;
		case "d" : 
			/*
			 * 
			 */
			deleteContactGroupInDB(isset($select) ? $select : array()); 
			require_once($path."listContactGroup.php"); 
			break;
		default : 
			/*
			 * Delete n contact group
			 */
			require_once($path."listContactGroup.php"); 
			break;
	}
?>