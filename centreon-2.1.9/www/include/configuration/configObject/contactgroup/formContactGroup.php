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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configObject/contactgroup/formContactGroup.php $
 * SVN : $Id: formContactGroup.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
 	if (!isset($oreon))
 		exit();

	/*
	 * Form Rules
	 */
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["cg_name"]));
	}	

	/*
	 * Database retrieve information for Contact
	 */
	$cg = array();
	if (($o == "c" || $o == "w") && $cg_id)	{
		/*
		 * Get host Group information
		 */
		$DBRESULT =& $pearDB->query("SELECT * FROM `contactgroup` WHERE `cg_id` = '".$cg_id."' LIMIT 1");
		
		/*
		 * Set base value
		 */	
		$cg = array_map("myDecode", $DBRESULT->fetchRow());
		
		/*
		 * Set Contact Childs
		 */
		$DBRESULT =& $pearDB->query("SELECT DISTINCT `contact_contact_id` FROM `contactgroup_contact_relation` WHERE `contactgroup_cg_id` = '".$cg_id."'");
		for ($i = 0; $contacts =& $DBRESULT->fetchRow(); $i++)
			$cg["cg_contacts"][$i] = $contacts["contact_contact_id"];
		$DBRESULT->free();
	}

	/*
	 * Contacts comes from DB -> Store in $contacts Array
	 */
	$contacts = array();
	$DBRESULT =& $pearDB->query("SELECT `contact_id`, `contact_name` FROM `contact` ORDER BY `contact_name`");
	while ($contact =& $DBRESULT->fetchRow())
		$contacts[$contact["contact_id"]] = $contact["contact_name"];
	unset($contact);
	$DBRESULT->free();
	
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 300px; height: 100px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"60");
	$template 		= "<table style='border:0px;'><tr><td>{unselected}</td><td align='center'>{add}<br /><br /><br />{remove}</td><td>{selected}</td></tr></table>";

	/*
	 * form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', _("Add a Contact Group"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Contact Group"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Contact Group"));

	/*
	 * Contact basic information
	 */
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'cg_name', _("Contact Group Name"), $attrsText);
	$form->addElement('text', 'cg_alias', _("Alias"), $attrsText);
	
	/*
	 * Contacts Selection
	 */
	$form->addElement('header', 'notification', _("Relations"));
	
    $ams1 =& $form->addElement('advmultiselect', 'cg_contacts', _("Linked Contacts"), $contacts, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams1->setButtonAttributes('remove', array('value' => _("Delete")));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	/*
	 * Further informations
	 */
	$form->addElement('header', 'furtherInfos', _("Additional Information"));
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Enabled"), '1');
	$cgActivation[] = &HTML_QuickForm::createElement('radio', 'cg_activate', null, _("Disabled"), '0');
	$form->addGroup($cgActivation, 'cg_activate', _("Status"), '&nbsp;');
	$form->setDefaults(array('cg_activate' => '1'));
	$form->addElement('textarea', 'cg_comment', _("Comments"), $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'cg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	/*
	 * Set rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('cg_name', 'myReplace');
	$form->addRule('cg_name', _("Compulsory Name"), 'required');
	$form->addRule('cg_alias', _("Compulsory Alias"), 'required');
	$form->registerRule('exist', 'callback', 'testContactGroupExistence');
	$form->addRule('cg_name', _("Name is already in use"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	if ($o == "w")	{
		/*
		 * Just watch a Contact Group information
		 */
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cg_id."'"));
	    $form->setDefaults($cg);
		$form->freeze();
	} else if ($o == "c")	{
		/*
		 * Modify a Contact Group information
		 */
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cg);
	} else if ($o == "a")	{
		/*
		 * Add a Contact Group information
		 */
		$subA =& $form->addElement('submit', 'submitA', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	}
	
	$valid = false;
	if ($form->validate())	{
		
		$cgObj =& $form->getElement('cg_id');
		
		if ($form->getSubmitValue("submitA"))
			$cgObj->setValue(insertContactGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateContactGroupInDB($cgObj->getValue());
		
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&cg_id=".$cgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]) { 
		require_once($path."listContactGroup.php");
	} else {
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		
		$tpl->display("formContactGroup.ihtml");
	}
?>