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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/nagiosStats/nagiosStats.php $
 * SVN : $Id: nagiosStats.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	if (!isset($oreon))
		exit(); 
	
	require_once "./include/monitoring/common-Func.php";

	require_once 'HTML/QuickForm.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	unset($tpl);
	unset($path);

	/*
	 * Time period select
	 */	
	$form = new HTML_QuickForm('form', 'post', "?p=".$p);	
	$time_period = array(NULL 			=> NULL,
						"last3hours"	=> _("Last 3 hours"),
						"today" 		=> _("Today"),
						"yesterday" 	=> _("Yesterday"),
						"last4days" 	=> _("Last 4 days"),
						"lastweek" 		=> _("Last week"),
						"lastmonth" 	=> _("Last month"),
						"last6month" 	=> _("Last 6 months"),
						"lastyear" 		=> _("Last year"));
	
	$selTP =& $form->addElement('select', 'start', _("Select time period :"), $time_period, array("onChange" =>"this.form.submit();"));	
	if (isset($_POST["start"])) {		
		$form->setDefaults(array('start' => $_POST["start"]));
	} else {
		$form->setDefaults(array('start' => "today"));
	}

	/*
	 * Get Poller List
	 */
	$tab_nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT * FROM `nagios_server` WHERE `ns_activate` = 1 ORDER BY `localhost` DESC");
	while ($nagios =& $DBRESULT->fetchRow())
		$tab_nagios_server[$nagios['id']] = $nagios['name'];
	
	$host_list = array();
	$tab_server = array();
	$cpt = 0;
	foreach ($tab_nagios_server as $key => $value){
		$host_list[$key] = $value;
		$tab_server[$cpt] = $value;
		$cpt++;
	}

	$options = array(	"active_host_check" => "nagios_active_host_execution.rrd", 
						"active_service_check" => "nagios_active_service_execution.rrd", 						
						"active_host_last" => "nagios_active_host_last.rrd",
						"active_service_last" => "nagios_active_service_last.rrd",						
						"host_latency" => "nagios_active_host_latency.rrd",
						"service_latency" => "nagios_active_service_latency.rrd",						
						"host_states" => "nagios_hosts_states.rrd",
						"service_states" => "nagios_services_states.rrd",
						"cmd_buffer" => "nagios_cmd_buffer.rrd");
		
	$path = "./include/nagiosStats/";
		
	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");	
	
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	/*
	 * Assign values
	 */
	 
	$tpl->assign('form', $renderer->toArray());
	
	if (isset($_POST["start"]))
		$tpl->assign('startPeriod', $_POST["start"]);		
	
	if (isset($host_list) && $host_list)
		$tpl->assign('host_list', $host_list);
		
	if (isset($tab_server) && $tab_server)
		$tpl->assign('tab_server', $tab_server);	
		
	$tpl->assign("p", $p);
	$tpl->assign("options", $options);
	$tpl->assign("session_id", session_id());
	$tpl->display("nagiosStats.ihtml");
?>