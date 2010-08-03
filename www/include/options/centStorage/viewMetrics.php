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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/options/centStorage/viewMetrics.php $
 * SVN : $Id: viewMetrics.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();
		
	if ((isset($_POST["o1"]) && $_POST["o1"]) || (isset($_POST["o2"]) && $_POST["o2"])){
		if ((defined($_POST["o1"]) && $_POST["o1"] == "rg") || (defined($_POST["o2"]) && $_POST["o2"] == "rg")){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '1' WHERE id = '".$key."'");
			}	
		} else if ((defined($_POST["o1"]) && $_POST["o1"] == "nrg") || (defined($_POST["o2"]) && $_POST["o2"] == "nrg")){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE index_data SET `must_be_rebuild` = '0' WHERE `id` = '".$key."' AND `must_be_rebuild` = '1'");
			}
		} else if ($_POST["o1"] == "ed" || $_POST["o2"] == "ed"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("SELECT * FROM metrics WHERE `metric_id` = '".$key."'");
				while ($metrics =& $DBRESULT->fetchRow()){
					$DBRESULT2 =& $pearDBO->query("DELETE FROM data_bin WHERE `id_metric` = '".$metrics['metric_id']."'");
					$DBRESULT2 =& $pearDBO->query("DELETE FROM metrics WHERE `metric_id` = '".$metrics['metric_id']."'");
				}
			}
		} else if ($_POST["o1"] == "hg" || $_POST["o2"] == "hg"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE metrics SET `hidden` = '1' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "nhg" || $_POST["o2"] == "nhg"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE metrics SET `hidden` = '0' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "lk" || $_POST["o2"] == "lk"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE metrics SET `locked` = '1' WHERE `metric_id` = '".$key."'");
			}
		} else if ($_POST["o1"] == "nlk" || $_POST["o2"] == "nlk"){
			$selected = $_POST["select"];
			foreach ($selected as $key => $value){
				$DBRESULT =& $pearDBO->query("UPDATE metrics SET `locked` = '0' WHERE `metric_id` = '".$key."'");
			}
		}
	}
		
	$search_string = "";
	if (isset($search) && $search)
		$search_string = " WHERE `host_name` LIKE '%$search%' OR `service_description` LIKE '%$search%'";
	
	$DBRESULT =& $pearDBO->query("SELECT COUNT(*) FROM metrics WHERE index_id = '".$_GET["index_id"]."'");
	$tmp =& $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
			
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$storage_type = array(0 => "RRDTool", 2 => "RRDTool & MySQL");	
	$yesOrNo = array(NULL => "No", 0 => "No", 1 => "Yes", 2 => "Rebuilding");	
	
	$DBRESULT2 =& $pearDBO->query("SELECT * FROM metrics WHERE index_id = '".$_GET["index_id"]."'");
	unset($data);
	for ($im = 0;$metrics =& $DBRESULT2->fetchRow();$im++){
		$metric = array();
		$DBRESULT3 =& $pearDBO->query("SELECT COUNT(*) FROM data_bin WHERE id_metric = '".$metrics["metric_id"]."'");
		$nb_value =& $DBRESULT3->fetchRow();
		$metric["nb"] = $nb_value["COUNT(*)"];	
		$metric["metric_id"] = $metrics["metric_id"];
		$metric["class"] = $tab_class[$im % 2];
		$metric["metric_name"] = $metrics["metric_name"];
		$metric["metric_name"] = str_replace("#S#", "/", $metric["metric_name"]);
		$metric["metric_name"] = str_replace("#BS#", "\\", $metric["metric_name"]);
		$metric["unit_name"] = $metrics["unit_name"];
		$metric["hidden"] = $yesOrNo[$metrics["hidden"]];
		$metric["locked"] = $yesOrNo[$metrics["locked"]];
		$metric["min"] = $metrics["min"];
		$metric["max"] = $metrics["max"];
		$metric["warn"] = $metrics["warn"];
		$metric["crit"] = $metrics["crit"];
		$data[$im] = $metric;
		unset($metric);
	}

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	
	## Toolbar select 

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
	$tpl->assign("Metric", _("Metric"));
	$tpl->assign("Unit", _("Unit"));
	$tpl->assign("Warning", _("Warning"));
	$tpl->assign("Critical", _("Critical"));
	$tpl->assign("Min", _("Min"));
	$tpl->assign("Max", _("Max"));
	$tpl->assign("NumberOfValues", _("Number of values"));
	$tpl->assign("Hidden", _("Hidden"));
	$tpl->assign("Locked", _("Locked"));
	
	$tpl->assign("data", $data);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
    $tpl->display("viewMetrics.ihtml");
?>