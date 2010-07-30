<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/options/centStorage/viewData.php $
 * SVN : $Id: viewData.php 10395 2010-04-29 10:15:05Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();
	
	include("./include/common/autoNumLimit.php");	
	
	require_once './class/other.class.php';
	include_once("./include/monitoring/common-Func.php");
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	

	# start quickSearch form
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	

	#Path to the option dir
	$path = "./include/options/centStorage/";
	
	#PHP functions
	require_once("./include/options/oreon/generalOpt/DB-Func.php");
	require_once("./include/common/common-Func.php");
	require_once("./class/centreonDB.class.php");
	
	$pearDBO = new CentreonDB("centstorage");
	
	if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])){
		if ($_POST["o"] == "rg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$key."'");
			}	
		} else if ($_POST["o"] == "nrg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '0' WHERE id = '".$key."' AND `must_be_rebuild` = '1'");
			}
		} else if ($_POST["o"] == "ed" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("SELECT * FROM metrics WHERE  `index_id` = '".$key."'");
				while ($metrics =& $DBRESULT->fetchRow()){
					$DBRESULT2 =& $pearDBO->query("DELETE FROM metrics WHERE `metric_id` = '".$metrics['metric_id']."'");
				}
				$DBRESULT =& $pearDBO->query("DELETE FROM index_data WHERE `id` = '".$key."'");
			}
		} else if ($_POST["o"] == "hg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `hidden` = '1' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "nhg" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `hidden` = '0' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "lk" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `locked` = '1' WHERE id = '".$key."'");
			}
		} else if ($_POST["o"] == "nlk" && isset($_POST["select"])){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `locked` = '0' WHERE id = '".$key."'");
			}
		}
	}
	
	if (isset($_POST["o"]) && $_POST["o"] == "d" && isset($_POST["id"])){
		$DBRESULT =& $pearDBO->query("UPDATE index_data SET `trashed` = '1' WHERE id = '".$_POST["id"]."'");		
	}
	
	if (isset($_POST["o"]) && $_POST["o"] == "rb" && isset($_POST["id"])){
		$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$_POST["id"]."'");
	}
	
	$search_string = "";
	if (isset($search) && $search){
		$searchFormated = str_replace("/", "#S#", $search);
		$searchFormated = str_replace("\\", "#BS#", $searchFormated);
		$search_string = " WHERE `host_name` LIKE '%$searchFormated%' OR `service_description` LIKE '%$searchFormated%'";
	}
	
	$DBRESULT =& $pearDBO->query("SELECT COUNT(*) FROM `index_data`$search_string");
	$tmp =& $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
			
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");	
	$yesOrNo = array(0 => "No", 1 => "Yes", 2 => "Rebuilding");	
	//$yesOrNo = array(0 => "<input type='checkbox' hidden='1' disabled>", 1 => "<input type='checkbox' checked disabled>", 2 => "Rebuilding");	
		
	$DBRESULT =& $pearDBO->query("SELECT * FROM `index_data` $search_string ORDER BY `host_name`, `service_description` LIMIT ".$num * $limit.", $limit");
	$data = array();
	for ($i = 0;$index_data =& $DBRESULT->fetchRow();$i++){
		$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$index_data["id"]."'");
		$metric = "";
		for ($im = 0;$metrics =& $DBRESULT2->fetchRow();$im++){
			if ($im)
				$metric .= " - ";
			$metric .= "<a href='./main.php?p=5010602&o=mmtrc&index_id=".$index_data["id"]."'>".$metrics["metric_name"]."</a>";
			if (isset($metrics["unit_name"]) && $metrics["unit_name"])
				$metric .= " (".$metrics["unit_name"].") ";
		}
		$index_data["metrics_name"] = $metric;
		$index_data["service_description"] = str_replace("#S#", "/", $index_data["service_description"]);
		$index_data["service_description"] = str_replace("#BS#", "\\", $index_data["service_description"]);
		$index_data["service_description"] = "<a href='./main.php?p=5010602&o=msvc&index_id=".$index_data["id"]."'>".$index_data["service_description"]."</a>";
		$index_data["metrics_name"] = str_replace("#S#", "/", $index_data["metrics_name"]);
		$index_data["metrics_name"] = str_replace("#BS#", "\\", $index_data["metrics_name"]);
		
		$index_data["storage_type"] = $storage_type[$index_data["storage_type"]];
		$index_data["must_be_rebuild"] = $yesOrNo[$index_data["must_be_rebuild"]];
		$index_data["trashed"] = $yesOrNo[$index_data["trashed"]];
		$index_data["hidden"] = $yesOrNo[$index_data["hidden"]];
		if (isset($index_data["locked"]))
			$index_data["locked"] = $yesOrNo[$index_data["locked"]];	
		else
			$index_data["locked"] = $yesOrNo[0];
		$index_data["class"] = $tab_class[$i % 2];
		$data[$i] = $index_data;
	}

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	
	#
	##Toolbar select 
	#
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 5) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 6) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 7) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Empty all Service Data"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 && confirm('"._('Do you confirm the deletion ?')."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 5) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 6) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 7) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
	$form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "rg"=>_("Rebuild RRD Database"), "nrg"=>_("Stop rebuilding RRD Databases"), "ed"=>_("Empty all Service Data"), "hg"=>_("Hide graphs of selected Services"), "nhg"=>_("Stop hiding graphs of selected Services"), "lk"=>_("Lock Services"), "nlk"=>_("Unlock Services")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("data", $data);
	$tpl->assign("Host", _("Host"));
	$tpl->assign("Service", _("Service"));
	$tpl->assign("Metrics", _("Metrics"));
	$tpl->assign("RebuildWaiting", _("Rebuild Waiting"));
	$tpl->assign("Hidden", _("Hidden"));
	$tpl->assign("Locked", _("Locked"));
	$tpl->assign("StorageType", _("Storage Type"));
	$tpl->assign("Actions", _("Actions"));
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
    $tpl->display("viewData.ihtml");
?>