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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/options/oreon/generalOpt/rrdtool/form.php $
 * SVN : $Id: form.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit();

	$DBRESULT =& $pearDB->query("SELECT * FROM `options`");
	while ($opt =& $DBRESULT->fetchRow()) {
		$gopt[$opt["key"]] = myDecode($opt["value"]);
	}
	$DBRESULT->free();

	/*
	 * Var information to format the element
	 */
	$attrsText 		= array("size"=>"40");
	$attrsText2		= array("size"=>"5");

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Modify General Options"));
	
	/*
	 * Various information
	 */
	$form->addElement('text', 'rrdtool_path_bin', _("Directory + RRDTOOL Binary"), $attrsText);
	$form->addElement('text', 'rrdtool_version', _("RRDTool Version"), $attrsText2);
	
	/*
	 * Unit
	 */
	$form->addElement('header', 'unit_title', _("Unit Properties"));
	$form->addElement('text', 'rrdtool_unit_font', _("Font"), $attrsText2);
	$form->addElement('text', 'rrdtool_unit_fontsize', _("Font size"), $attrsText2);
	
	/*
	 * Title
	 */
	$form->addElement('header', 'title_title', _("Title Properties"));
	$form->addElement('text', 'rrdtool_title_font', _("Font"), $attrsText2);
	$form->addElement('text', 'rrdtool_title_fontsize', _("Font size"), $attrsText2);
	
	/*
	 * Axis
	 */
	$form->addElement('header', 'axis_title', _("Axis Properties"));
	$form->addElement('text', 'rrdtool_axis_font', _("Font"), $attrsText2);
	$form->addElement('text', 'rrdtool_axis_fontsize', _("Font size"), $attrsText2);
	
	/*
	 * Watermark
	 */
	$form->addElement('header', 'watermark_title', _("Watermark Properties"));
	$form->addElement('text', 'rrdtool_watermark_font', _("Font"), $attrsText2);
	$form->addElement('text', 'rrdtool_watermark_fontsize', _("Font size"), $attrsText2);
	
	$form->addElement('hidden', 'gopt_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	/*
	 * Form Rules
	 */
	function slash($elem = NULL)	{
		if ($elem)
			return rtrim($elem, "/")."/";
	}
	
	$form->applyFilter('__ALL__', 'myTrim');
	$form->addRule('rrdtool_path_bin', _("Can't execute binary"), 'is_executable_binary');
	$form->addRule('oreon_rrdbase_path', _("Can't write in directory"), 'is_writable_path');

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path.'rrdtool/', $tpl);

	$form->setDefaults($gopt);

	$subC =& $form->addElement('submit', 'submitC', _("Save"));
	$DBRESULT =& $form->addElement('reset', 'reset', _("Reset"));


    $valid = false;
	if ($form->validate())	{
		/*
		 * Update in DB
		 */
		updateRRDToolConfigData($form->getSubmitValue("gopt_id"));
		
		/*
		 * Update in Oreon Object
		 */
		$oreon->initOptGen($pearDB);
		
		$o = NULL;
   		$valid = true;
		$form->freeze();
	}
	if (!$form->validate() && isset($_POST["gopt_id"]))
	    print("<div class='msg' align='center'>"._("Impossible to validate, one or more field is incorrect")."</div>");

	$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=rrdtool'"));

	/*
	 * Apply a template definition
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign("genOpt_rrdtool_properties", _("RRDTool Properties"));
	$tpl->assign("genOpt_rrdtool_configurations", _("RRDTool Configuration"));
	$tpl->assign('valid', $valid);
	$tpl->display("form.ihtml");
?>