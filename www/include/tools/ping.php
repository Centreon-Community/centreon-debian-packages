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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/tools/ping.php $
 * SVN : $Id: ping.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
	 include("@CENTREON_ETC@/centreon.conf.php");
	 require_once ("../../$classdir/Session.class.php");
	 require_once ("../../$classdir/Oreon.class.php");

	 Session::start();
	
	 if (!isset($_SESSION["oreon"])) {
	 	// Quick dirty protection
	 	header("Location: ../../index.php");
		exit;
	 } else
	 	$oreon =& $_SESSION["oreon"];

	if (isset($_GET["host"]))
		$host = htmlentities($_GET["host"], ENT_QUOTES);
	else if (isset($_POST["host"]))
		$host = htmlentities($_POST["host"], ENT_QUOTES);
	else {
		print "Bad Request !";
		exit;
	}

	require ("Net/Ping.php");
	$ping = Net_Ping::factory();

	$msg = "";
	if (!PEAR::isError($ping))	{
    	$ping->setArgs(array("count" => 4));
		# patch for user that have PEAR Traceroute 0.21.1, remote exec possible Julien Cayssol
		$response = $ping->ping(escapeshellcmd($host));
		foreach ($response->getRawData() as $key => $data)
   			$msg .= $data ."<br />";
		print $msg;
	}

?>
