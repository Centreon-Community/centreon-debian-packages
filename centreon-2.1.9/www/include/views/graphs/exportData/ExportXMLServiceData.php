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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/views/graphs/exportData/ExportXMLServiceData.php $
 * SVN : $Id: ExportXMLServiceData.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	function get_error($str){
		echo $str."<br />";
		exit(0);
	}

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path."www/class/centreonDB.class.php");
	include_once($centreon_path."www/class/centreonXML.class.php");

	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset($_GET["index"]) ? $index = $_GET["index"] : $index = NULL;
	isset($_POST["index"]) ? $index = $_POST["index"] : $index = $index;

	$path = "./include/views/graphs/graphODS/";

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	$DBRESULT =& $pearDBO->query("SELECT host_name, service_description FROM index_data WHERE id = '$index'");
	while ($res =& $DBRESULT->fetchRow()){
		$hName = $res["host_name"];
		$sName = $res["service_description"];
	}	

	header("Content-Type: application/xml");
	if (isset($hName) && isset($sName))
		header("Content-disposition: filename=".$hName."_".$sName.".xml");
	else
		header("Content-disposition: filename=".$index.".xml");

	$DBRESULT =& $pearDBO->query("SELECT metric_id FROM metrics, index_data WHERE metrics.index_id = index_data.id AND id = '$index'");
	while ($index_data =& $DBRESULT->fetchRow()){	
		$DBRESULT2 =& $pearDBO->query("SELECT ctime,value FROM data_bin WHERE id_metric = '".$index_data["metric_id"]."' AND ctime >= '".$_GET["start"]."' AND ctime < '".$_GET["end"]."'");
		while ($data =& $DBRESULT2->fetchRow()){
			if (!isset($datas[$data["ctime"]]))
				$datas[$data["ctime"]] = array();
			$datas[$data["ctime"]][$index_data["metric_id"]] = $data["value"];
		}
	}
	$buffer = new CentreonXML();
	$buffer->startElement("root");
	$buffer->startElement("datas");	
	foreach ($datas as $key => $tab){
		$buffer->startElement("data");
		$buffer->writeAttribute("no", $key);		
		foreach($tab as $value)
			$buffer->writeElement("metric", $value);			
		$buffer->endElement();		
	}
	$buffer->endElement();
	$buffer->endElement();
	$buffer->output();
	exit();
?>