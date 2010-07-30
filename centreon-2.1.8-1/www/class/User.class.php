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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/class/User.class.php $
 * SVN : $Id: User.class.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

require_once("centreonACL.class.php");
require_once("centreonLog.class.php");

class User	{

	var $user_id;
	var $name;    
	var $alias;
	var $passwd;
	var $email;
	var $lang;
	var $charset;
	var $version;
	var $admin;
	var $limit;
	var $num;
	var $gmt;
	var $is_admin;
	var $groupList;
	var $groupListStr;
	var $access;
	var $log;
	var $userCrypted;
	
	# User LCA
	# Array with elements ID for loop test
	var $lcaTopo;
	
	# String with elements ID separated by commas for DB requests
	var $lcaTStr;
	  
  	function User($user = array(), $nagios_version = NULL)  {
		global $pearDB;
		
		$this->user_id = $user["contact_id"];
		$this->name = html_entity_decode($user["contact_name"], ENT_QUOTES);
		$this->alias = html_entity_decode($user["contact_alias"], ENT_QUOTES);
		$this->email = html_entity_decode($user["contact_email"], ENT_QUOTES);
		$this->lang = $user["contact_lang"];
		$this->charset = "UTF-8";
		$this->passwd = $user["contact_passwd"];
		$this->admin = $user["contact_admin"];
		$this->version = $nagios_version;
	  	$this->gmt = $user["contact_location"];
	  	$this->is_admin = NULL;
	  	/*
	  	 * Initiate ACL
	  	 */
	  	$this->access = new CentreonACL($this->user_id, $this->admin);
	  	$this->lcaTopo =& $this->access->topology;
	  	$this->lcaTStr =& $this->access->topologyStr;
	  	/*
	  	 * Initiate Log Class
	  	 */
	  	$this->log = new CentreonUserLog($this->user_id, $pearDB);
  		$this->userCrypted = md5($this->alias);
  	}
    
  	public function showDiv($div_name = NULL) {
  		global $pearDB;
  		
  		if (!isset($div_name))
  			return 0;
  		
  		$query = "SELECT cp_value " .
  				"FROM contact_param " .
  				"WHERE cp_contact_id = '".$this->user_id."' " .
  				"AND cp_key LIKE '_Div_".$div_name."' LIMIT 1" ;
  		$DBRESULT =& $pearDB->query($query);
  		while ($row  =& $DBRESULT->fetchRow())
  			return $row['cp_value'];  		
  		return "1";  		
  	}
  
  	function getAllTopology($pearDB){
	  	$DBRESULT =& $pearDB->query("SELECT topology_page FROM topology WHERE topology_page IS NOT NULL");	
		while ($topo =& $DBRESULT->fetchRow())
			if (isset($topo["topology_page"]))
				$lcaTopo[$topo["topology_page"]] = 1;
		unset($topo);
		$DBRESULT->free();
		return $lcaTopo;
  	}
  	
  	/*
  	 * Check if user is admin or had ACL
  	 */
  	
  	function checkUserStatus($sid = NULL, $pearDB){
		$DBRESULT =& $pearDB->query("SELECT contact_admin, contact_id FROM session, contact WHERE session.session_id = '".$sid."' AND contact.contact_id = session.user_id");
		$admin =& $DBRESULT->fetchRow();
		$DBRESULT->free();
		
		$DBRESULT =& $pearDB->query("SELECT count(*) FROM `acl_group_contacts_relations` WHERE contact_contact_id = '".$admin["contact_id"]."'");
		$admin2 =& $DBRESULT->fetchRow();
		$DBRESULT->free();

		if ($admin["contact_admin"]){
			unset($admin);
			$this->is_admin = 1 ;
		} else if (!$admin2["count(*)"]) {
			unset($admin2);
			$this->is_admin = 1;			
		}
		$this->is_admin = 0;
	}
  	  
  // Get
  
  function get_id(){
  	return $this->user_id;
  }
  
  function get_name(){
  	return $this->name;
  }
    
  function get_email(){
  	return $this->email;
  }
  
  function get_alias(){
  	return $this->alias;
  }
  
  function get_version()	{
  	return $this->version;
  } 
  
  function get_lang(){
  	return $this->lang;
  }
  
  function get_passwd(){
  	return $this->passwd;
  }
  
  function get_admin(){
  	return $this->admin;
  }
   
  function is_admin(){
  	return $this->is_admin;
  }

  // Set
  
  function set_id($id)	{
  	$this->user_id = $id;
  }
  
  function set_name($name)	{
  	$this->name = $name;
  }
    
  function set_email($email)	{
  	$this->email = $email;
  }
  
  function set_lang($lang)	{
  	$this->lang = $lang;
  }
  
  function set_alias($alias)	{
  	$this->alias = $alias;
  }
  
  function set_version($version)	{
  	$this->version = $version;
  }
  
  function getMyGMT(){
  	return $this->gmt;
  }
} /* end class User */
?>