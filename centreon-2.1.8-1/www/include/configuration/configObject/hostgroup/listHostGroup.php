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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/hostgroup/listHostGroup.php $
 * SVN : $Id: listHostGroup.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	
	/*
	 * Search
	 */
	$SearchTool = NULL;
	if (isset($search) && $search)	
		$SearchTool = " WHERE (hg_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR hg_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%')";
	
	$request = "SELECT COUNT(*) FROM hostgroup $SearchTool";
	
	$DBRESULT =& $pearDB->query($request);
	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	/*
	 *  Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	/*
	 * start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_hostAct", _("Enabled Hosts"));
	$tpl->assign("headerMenu_hostDeact", _("Disabled Hosts"));
	$tpl->assign("headerMenu_hostgroupAct", _("Enabled HostGroups"));
	$tpl->assign("headerMenu_hostgroupDeact", _("Disabled HostGroups"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Hostgroup list
	 */
	 
	$rq = "SELECT hg_id, hg_name, hg_alias, hg_activate FROM hostgroup $SearchTool ORDER BY hg_name LIMIT ".$num * $limit .", $limit"; 
	$DBRESULT =& $pearDB->query($rq);
	
	$search = tidySearchKey($search, $advanced_search);
	
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	/*
	 * Different style between each lines
	 */
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	$elemArr = array();
	for ($i = 0; $hg =& $DBRESULT->fetchRow(); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$hg['hg_id']."]");	
		$moptions = "";
		if ($hg["hg_activate"])
			$moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='main.php?p=".$p."&hg_id=".$hg['hg_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$hg['hg_id']."]'></input>";
		
		/* 
		 * Check Nbr of Host / hg 
		 */
		$nbrhostAct = array();
		$nbrhostDeact = array();
		$nbrhostgroupAct = array();
		$nbrhostgroupDeact = array();
		
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_relation hgr, host WHERE hostgroup_hg_id = '".$hg['hg_id']."' AND host.host_id = hgr.host_host_id AND host.host_register = '1' AND host.host_activate = '1'";
		$DBRESULT2 =& $pearDB->query($rq);
		$nbrhostAct = $DBRESULT2->fetchRow();
		
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_relation hgr, host WHERE hostgroup_hg_id = '".$hg['hg_id']."' AND host.host_id = hgr.host_host_id AND host.host_register = '1' AND host.host_activate = '0'";
		$DBRESULT2 =& $pearDB->query($rq);
		$nbrhostDeact = $DBRESULT2->fetchRow();
		
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_hg_relation hgr, hostgroup WHERE hg_parent_id = '".$hg['hg_id']."' AND hostgroup.hg_id = hgr.hg_child_id AND hostgroup.hg_activate = '1'";
		$DBRESULT2 =& $pearDB->query($rq);
		$nbrhostgroupAct = $DBRESULT2->fetchRow();
		
		$rq = "SELECT COUNT(*) as nbr FROM hostgroup_hg_relation hgr, hostgroup WHERE hg_parent_id = '".$hg['hg_id']."' AND hostgroup.hg_id = hgr.hg_child_id AND hostgroup.hg_activate = '0'";
		$DBRESULT2 =& $pearDB->query($rq);
		$nbrhostgroupDeact = $DBRESULT2->fetchRow();
		
		
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$hg["hg_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&hg_id=".$hg['hg_id'],
						"RowMenu_desc"=>$hg["hg_alias"],
						"RowMenu_status"=>$hg["hg_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_hostAct"=>$nbrhostAct["nbr"],
						"RowMenu_hostDeact"=>$nbrhostDeact["nbr"],
						"RowMenu_hostgroupAct"=>$nbrhostgroupAct["nbr"],
						"RowMenu_hostgroupDeact"=>$nbrhostgroupDeact["nbr"],
						"RowMenu_options"=>$moptions);
		/*
		 * Switch color line 
		 */
		$style != "two" ? $style = "two" : $style = "one";	
	}
	$tpl->assign("elemArr", $elemArr);
	
	/*
	 * Different messages we put in the template
	 */
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 4) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listHostGroup.ihtml");
?>