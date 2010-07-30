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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/traps/formTraps.php $
 * SVN : $Id: formTraps.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	if (!isset($oreon))
		exit();

	#
	## Database retrieve information for Trap
	#
	
	function myDecodeTrap($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES);
		return($arg);
	}

	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("traps_name")));
	}
	
	$trap = array();
	$mnftr = array(NULL=>NULL);
	$mnftr_id = -1;
	if (($o == "c" || $o == "w") && $traps_id)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM traps WHERE traps_id = '".$traps_id."' LIMIT 1");
		# Set base value
		$trap = array_map("myDecodeTrap", $DBRESULT->fetchRow());
		$DBRESULT->free();
	}
	$DBRESULT =& $pearDB->query("SELECT id, alias FROM traps_vendor");
	while($rmnftr =& $DBRESULT->fetchRow()){
		$mnftr[$rmnftr["id"]] = $rmnftr["alias"];
	}
	$DBRESULT->free();
	
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"50");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Trap definition"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Trap definition"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Trap definition"));

	#
	## Command information
	#
	$form->addElement('text', 'traps_name', _("Trap name"), $attrsText);
	$form->addElement('select', 'traps_status', _("Status"), array(0=>_("Ok"), 1=>_("Warning"), 2=>_("Critical"), 3=>_("Unknown")));
	$form->addElement('select', 'manufacturer_id', _("Vendor Name"), $mnftr);
	$form->addElement('textarea', 'traps_comments', _("Comments"), $attrsTextarea);

	/*
	 * Three possibilities : 	- submit result
	 * 							- execute a special command
	 * 							- resubmit a scheduling force 
	 */

	/*
	 * submit result 
	 */
	$form->addElement('text', 'traps_oid', _("OID"), $attrsText);
	$form->addElement('text', 'traps_args', _("Output Message"), $attrsText);

	$form->addElement('checkbox', 'traps_submit_result_enable', _("Submit result"));
	$form->setDefaults(1);
	
	/*
	 * Schedule svc check forced
	 */
	$form->addElement('checkbox', 'traps_reschedule_svc_enable', _("Reschedule Associated Servcies"));
	$form->setDefaults(0);
	
	
	/*
	 * execute commande
	 */
	$form->addElement('text', 'traps_execution_command', _("Special Command"), $attrsText);
	$form->addElement('checkbox', 'traps_execution_command_enable', _("Execute special command"));
	$form->setDefaults(0);

	#
	## Further informations
	#
	$form->addElement('hidden', 'traps_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));
	
	#
	## Form Rules
	#

	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('traps_name', 'myReplace');
	$form->addRule('traps_name', _("Compulsory Name"), 'required');
	$form->addRule('traps_oid', _("Compulsory Name"), 'required');
	$form->addRule('manufacturer_id', _("Compulsory Name"), 'required');
	$form->addRule('traps_args', _("Compulsory Name"), 'required');
	$form->registerRule('exist', 'callback', 'testTrapExistence');
	$form->addRule('traps_oid', _("A same Oid element already exists"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	#
	##End of form definition
	#

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Just watch a Command information
	if ($o == "w")	{
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$traps_id."'"));
	    $form->setDefaults($trap);
		$form->freeze();
	}
	# Modify a Command information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($trap);
	}
	# Add a Command information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$trapObj =& $form->getElement('traps_id');
		if ($form->getSubmitValue("submitA")) 
			$trapObj->setValue(insertTrapInDB());
		else if ($form->getSubmitValue("submitC"))
			updateTrapInDB($trapObj->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$trapObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action =& $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listTraps.php");
	else	{
		##Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);
		
		$tpl->assign('subtitle1', _("Action 1 : Submit result to Nagios"));
		$tpl->assign('subtitle2', _("Action 2 : Force service check rescheduling "));
		$tpl->assign('subtitle3', _("Action 3 : Execute a Command"));
		
		$tpl->display("formTraps.ihtml");
	}
?>