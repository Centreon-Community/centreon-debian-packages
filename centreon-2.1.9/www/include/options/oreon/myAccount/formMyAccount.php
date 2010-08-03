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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/options/oreon/myAccount/formMyAccount.php $
 * SVN : $Id: formMyAccount.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset ($oreon))
		exit ();
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	require_once "./include/common/common-Func.php";
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
		
	#Path to the configuration dir
	$path = "./include/options/oreon/myAccount/";
	
	#PHP Functions
	require_once $path."DB-Func.php";
	
	#
	## Database retrieve information for the User
	#
	$cct = array();
	if ($o == "c")	{	
		$DBRESULT =& $pearDB->query("SELECT contact_id, contact_name, contact_alias, contact_lang, contact_email, contact_pager FROM contact WHERE contact_id = '".$oreon->user->get_id()."' LIMIT 1");
		# Set base value
		$cct = array_map("myDecode", $DBRESULT->fetchRow());
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Langs -> $langs Array
	$langs = array();
	$langs = getLangs();	
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"35");

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	$form->addElement('header', 'title', _("Change my settings"));

	#
	## Basic information
	#
	$form->addElement('header', 'information', _("General Information"));
	$form->addElement('text', 'contact_name', _("Name"), $attrsText);
	$form->addElement('text', 'contact_alias', _("Alias / Login"), $attrsText);
	$form->addElement('text', 'contact_email', _("Email"), $attrsText);
	$form->addElement('text', 'contact_pager', _("Pager"), $attrsText);
	$form->addElement('password', 'contact_passwd', _("Password"), $attrsText);
	$form->addElement('password', 'contact_passwd2', _("Confirm password"), $attrsText);
    $form->addElement('select', 'contact_lang', _("Language"), $langs);

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["contact_name"]));
	}
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('contact_name', 'myReplace');
	$form->addRule('contact_name', _("Compulsory name"), 'required');
	$form->addRule('contact_alias', _("Compulsory alias"), 'required');
	$form->addRule('contact_email', _("Valid Email"), 'required');
	$form->addRule(array('contact_passwd', 'contact_passwd2'), _("Passwords do not match"), 'compare');
	$form->registerRule('exist', 'callback', 'testExistence');
	$form->addRule('contact_name', _("Name already in use"), 'exist');
	$form->registerRule('existAlias', 'callback', 'testAliasExistence');
	$form->addRule('contact_alias', _("Name already in use"), 'existAlias');
	$form->setRequiredNote("<font style='color: red;'>*</font>"._("Required fields"));

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Modify a contact information
	if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', _("Save"));
		$res =& $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cct);
	}
	
	if ($form->validate())	{
		updateContactInDB($oreon->user->get_id());
		if ($form->getSubmitValue("contact_passwd"))
			$oreon->user->passwd = md5($form->getSubmitValue("contact_passwd"));
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c'"));
		$form->freeze();
	}
	#Apply a template definition	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());	
	$tpl->assign('o', $o);		
	$tpl->display("formMyAccount.ihtml");
?>