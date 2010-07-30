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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/hostgroup_dependency/listHostGroupDependency.php $
 * SVN : $Id: listHostGroupDependency.php 10473 2010-05-19 21:25:56Z jmathis $
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
		
	isset($_GET["list"]) ? $list = $_GET["list"] : $list = NULL;

	$rq = "SELECT COUNT(*) FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupParent_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupChild_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0";
	
	if (isset($search))
		$rq .= " AND (dep_name LIKE '".htmlentities($search, ENT_QUOTES)."' OR dep_description LIKE '%".htmlentities($search, ENT_QUOTES)."%')";
	$DBRESULT = & $pearDB->query($rq);
	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
	
	include("./include/common/checkPagination.php");
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 *  start header menu
	 */
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_description", _("Alias"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	
	/*
	 * List dependancies
	 */
	$rq = "SELECT dep_id, dep_name, dep_description FROM dependency dep";
	$rq .= " WHERE (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupParent_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_hostgroupChild_relation dhgpr WHERE dhgpr.dependency_dep_id = dep.dep_id) > 0";
	
	if ($search)
		$rq .= " AND (dep_name LIKE '".htmlentities($search, ENT_QUOTES)."' OR dep_description LIKE '%".htmlentities($search, ENT_QUOTES)."%')";
	$rq .= " LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);
	
	$search = tidySearchKey($search, $advanced_search);
	
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	#Different style between each lines
	$style = "one";
	
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $dep =& $DBRESULT->fetchRow(); $i++) {		
		$moptions = "";
		$selectedElements =& $form->addElement('checkbox', "select[".$dep['dep_id']."]");	
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$dep['dep_id']."]'></input>";
		$elemArr[$i] = array(	"MenuClass"=>"list_".$style, 
								"RowMenu_select"=>$selectedElements->toHtml(),
								"RowMenu_name"=>htmlentities($dep["dep_name"]),
								"RowMenu_link"=>"?p=".$p."&o=c&dep_id=".$dep['dep_id'],
								"RowMenu_description"=>htmlentities($dep["dep_description"]),
								"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";	
	}
	$tpl->assign("elemArr", $elemArr);
	
	# Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

	/*
	 * Toolbar
	 */
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
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
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
	$tpl->display("listHostGroupDependency.ihtml");
?>