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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/options/oreon/modules/modules.php $
 * SVN : $Id: modules.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
	
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["list"]) ? $mG = $_GET["list"] : $mG = NULL;
	isset($_POST["list"]) ? $mP = $_POST["list"] : $mP = NULL;
	$mG ? $list = $mG : $list = $mP;
	
	isset($_GET["id"]) ? $mG = $_GET["id"] : $mG = NULL;
	isset($_POST["id"]) ? $mP = $_POST["id"] : $mP = NULL;
	$mG ? $id = $mG : $id = $mP;
	
	isset($_GET["name"]) ? $nameG = $_GET["name"] : $nameG = NULL;	
	isset($_POST["name"]) ? $nameP = $_POST["name"] : $nameP = NULL;
	$nameG ? $name = $nameG : $name = $nameP;
		
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the options dir
	 */	
	$path = "./include/options/oreon/modules/";
	
	require_once "./include/common/common-Func.php";
	require_once $path ."DB-Func.php";
	
	if ($list)
		require_once($path."listModules.php");
	else	{
		switch ($o)	{
			case "i" : require_once($path."formModule.php"); break;
			case "u" : require_once($path."formModule.php"); break;
			case "d" : require_once($path."listModules.php"); break;
			case "w" : require_once($path."formModule.php"); break;
			default : require_once($path."listModules.php");  break;
		}
	}
?>