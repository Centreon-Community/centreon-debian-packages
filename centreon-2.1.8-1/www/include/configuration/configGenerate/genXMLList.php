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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/configuration/configGenerate/genXMLList.php $
 * SVN : $Id: genXMLList.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
 
	if (!isset($oreon))
		exit();
	
	unlink($XMLConfigPath."osm_list.xml");
	$handle = create_file($XMLConfigPath."osm_list.xml", $oreon->user->get_name(), false);
	$str = NULL;
	$str = "<osm_list>\n";
	$str .= "<elements>\n";
	
	#
	##	Listing
	#
	
	# Host List
	foreach($gbArr[2] as $key => $value)	{
		$DBRESULT =& $pearDB->query("SELECT host_name, host_template_model_htm_id, host_address, host_register FROM host, extended_host_information ehi WHERE host_id = '".$key."' AND ehi.host_host_id = host_id LIMIT 1");
		$host = $DBRESULT->fetchRow();
		if ($host["host_register"])	{
			if (!$host["host_name"])
				$host["host_name"] = getMyHostName($host['host_template_model_htm_id']);
			if (!$host["host_address"])
				$host["host_address"] = getMyHostAddress($host['host_template_model_htm_id']);
			$str .= "<h id='".$key."' name='".html_entity_decode($host["host_name"], ENT_QUOTES)."' address='".$host["host_address"]."'";
			$str .= " gps='false'";
			$str .= "/>\n";
		}
		else
			unset($gbArr[2][$key]);
	}
	# Host Group List
	foreach($gbArr[3] as $key => $value)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup WHERE hg_id = '".$key."'");
		$hostGroup = $DBRESULT->fetchRow();
		$str .= "<hg id='".$key."' name='".html_entity_decode($hostGroup["hg_name"], ENT_QUOTES)."'";
		$str .= " gps='false'";	
		$str .= "/>\n";
	}
	# Services List
	foreach($gbArr[4] as $key => $value)	{		
		$DBRESULT =& $pearDB->query("SELECT DISTINCT sv.service_description, sv.service_template_model_stm_id, service_register, hsr.host_host_id, hsr.hostgroup_hg_id FROM service sv, host_service_relation hsr WHERE sv.service_id = '".$key."' AND hsr.service_service_id = sv.service_id");
		while ($sv =& $DBRESULT->fetchRow())	{
			if ($sv["service_register"])	{
				if (!$sv["service_description"])
					$sv["service_description"] = getMyServiceName($sv['service_template_model_stm_id']);
				if ($sv["host_host_id"]){
					$sv["service_description"] = str_replace("#S#", "/", $sv["service_description"]);
					$sv["service_description"] = str_replace("#BS#", "\\", $sv["service_description"]);
					$str .= "<sv id='".$sv["host_host_id"]."_".$key."' name='".$sv["service_description"]."'/>\n";
				} else if ($sv["hostgroup_hg_id"])	{
					$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$sv["hostgroup_hg_id"]."'");
					while ($host =& $DBRESULT2->fetchRow())
						if (array_key_exists($host["host_host_id"], $gbArr[2])){
							$sv["service_description"] = str_replace("#S#", "/", $sv["service_description"]);
							$sv["service_description"] = str_replace("#BS#", "\\", $sv["service_description"]);
							$str .= "<sv id='".$host["host_host_id"]."_".$key."' name='".$sv["service_description"]."'/>\n";
						}
					$DBRESULT2->free();
				}
			}
			else
				unset($gbArr[4][$key]);
		}
	}
	# Service Group List
	foreach($gbArr[5] as $key => $value)	{		
		$DBRESULT =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$key."'");
		$serviceGroup = $DBRESULT->fetchRow();
		$str .= "<sg id='".$key."' name='".html_entity_decode($serviceGroup["sg_name"], ENT_QUOTES)."'";
		$str .= " gps='false'";
		$str .= "/>\n";
	}
	
	# Meta Service
	foreach($gbArr[7] as $key => $value)	{		
		$DBRESULT =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$key."'");
		$osm = $DBRESULT->fetchRow();
		$str .= "<ms id='".$key."' name='".html_entity_decode($osm["meta_name"], ENT_QUOTES)."'/>\n";
		$DBRESULT->free();
	}
	$str .= "</elements>\n";
	
	#
	##	Dependencies
	#
	$str .= "<dependencies>\n";
	
	#	Host
	foreach($gbArr[2] as $key => $value)	{
		$DBRESULT =& $pearDB->query("SELECT host_template_model_htm_id AS tpl, host_register FROM host WHERE host_id = '".$key."'");
		$host = $DBRESULT->fetchRow();
		$str .= "<h id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		# Host Groups
		$DBRESULT =& $pearDB->query("SELECT hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.host_host_id = '".$key."'");
		while($hostGroup =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .="<hg id='".$hostGroup["hostgroup_hg_id"]."'/>\n";
		}
		$DBRESULT->free();
		# Hosts
		$DBRESULT =& $pearDB->query("SELECT hpr.host_parent_hp_id FROM host_hostparent_relation hpr WHERE hpr.host_host_id = '".$key."'");
		//if (!$DBRESULT->numRows() && $host["tpl"])
		//	$DBRESULT =& getMyHostParents($host["tpl"]);
		while($host =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_parent_hp_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_parent_hp_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_parent_hp_id"]."'/>\n";
		}
		$str .= "</prts>\n";
		$DBRESULT->free();
		## Childs
		$str .= "<chds>\n";
		# Hosts
		$DBRESULT =& $pearDB->query("SELECT host_host_id FROM host_hostparent_relation WHERE host_parent_hp_id = '".$key."'");
		while($host =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_host_id"]."'/>\n";
		}
		$DBRESULT->free();
		# Services from Host
		$DBRESULT =& $pearDB->query("SELECT hsr.service_service_id FROM host_service_relation hsr WHERE hsr.host_host_id = '".$key."'");
		while($service =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<sv id='".$key."_".$service["service_service_id"]."'/>\n";
		}
		$DBRESULT->free();
		# Services from Host Group
		$DBRESULT =& $pearDB->query("SELECT hgr.hostgroup_hg_id FROM hostgroup_relation hgr WHERE hgr.host_host_id = '".$key."'");
		while($hostGroup =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($hostGroup["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	{
				$DBRESULT2 =& $pearDB->query("SELECT hsr.service_service_id FROM host_service_relation hsr WHERE hsr.hostgroup_hg_id = '".$hostGroup["hostgroup_hg_id"]."'");
				while($service =& $DBRESULT2->fetchRow())	{
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$str .= "<sv id='".$key."_".$service["service_service_id"]."'/>\n";
				}	
				$DBRESULT2->free();
			}
		}		
		$str .= "</chds>\n";
		$str .= "</h>\n";
	}
	# HostGroup
	foreach($gbArr[3] as $key => $value)	{
		$str .= "<hg id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";		
		$DBRESULT =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$key."'");
		while($host =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($host["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)
				$str .= "<h id='".$host["host_host_id"]."'/>\n";
		}
		$DBRESULT->free();
		$str .= "</chds>\n";
		$str .= "</hg>\n";
	}
	# Service
	foreach($gbArr[4] as $key => $value)	{
		$DBRESULT =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$key."'");
		while ($sv =& $DBRESULT->fetchRow())	{
			if ($sv["host_host_id"])	{
				$str .= "<sv id='".$sv["host_host_id"]."_".$key."'>\n";								
				## Parents
				$str .= "<prts>\n";
				$str .= "<h id='".$sv["host_host_id"]."'/>\n";
				$str .= "</prts>\n";						
				## Childs
				$str .= "<chds>\n";
				$str .= "</chds>\n";
				$str .= "</sv>\n";
			}
			else if ($sv["hostgroup_hg_id"])	{
				$DBRESULT2 =& $pearDB->query("SELECT DISTINCT host_host_id FROM hostgroup_relation WHERE hostgroup_hg_id = '".$sv["hostgroup_hg_id"]."'");
				while ($host =& $DBRESULT2->fetchRow())
					if (array_key_exists($host["host_host_id"], $gbArr[2]))	{
						$str .= "<sv id='".$host["host_host_id"]."_".$key."'>\n";				
						## Parents
						$str .= "<prts>\n";
						$str .= "<h id='".$host["host_host_id"]."'/>\n";
						$str .= "</prts>\n";						
						## Childs
						$str .= "<chds>\n";
						$str .= "</chds>\n";
						$str .= "</sv>\n";
					}
				$DBRESULT2->free();
			}			
		}
		$DBRESULT->free();
	}
	# ServiceGroup
	foreach($gbArr[5] as $key => $value)	{
		$str .= "<sg id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";
		$DBRESULT =& $pearDB->query("SELECT sgr.service_service_id FROM servicegroup_relation sgr WHERE sgr.servicegroup_sg_id = '".$key."'");
		while($service =& $DBRESULT->fetchRow())	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($service["service_service_id"], $gbArr[4]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	{
				$DBRESULT2 =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service["service_service_id"]."'");
				while($service2 =& $DBRESULT2->fetchRow())	{
					$BP = false;
					if ($ret["level"]["level"] == 1)	{
						array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
						array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					}
					else if ($ret["level"]["level"] == 2)	{
						array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
						array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
					}
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						if ($service2["hostgroup_hg_id"])	{
							$DBRESULT3 =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$service2["hostgroup_hg_id"]."'");
							while($service3 =& $DBRESULT3->fetchRow())	{
								$BP = false;
								if ($ret["level"]["level"] == 1)
									array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 2)
									array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
								else if ($ret["level"]["level"] == 3)
									$BP = true;
								if ($BP)
									$str .= "<sv id='".$service3["host_host_id"]."_".$service["service_service_id"]."'/>\n";
							}	
							unset($service3);
							$DBRESULT3->free();						
						}
						else
							$str .= "<sv id='".$service2["host_host_id"]."_".$service["service_service_id"]."'/>\n";
					}
				}
				$DBRESULT2->free();
			}
		}
		$DBRESULT->free();
		$str .= "</chds>\n";
		$str .= "</sg>\n";
	}
	
	
	# Meta Service
	foreach($gbArr[7] as $key => $value)	{
		$str .= "<ms id='".$key."'>\n";
		## Parents
		$str .= "<prts>\n";
		$str .= "</prts>\n";
		
		## Childs
		$str .= "<chds>\n";
		$DBRESULT =& $pearDB->query("SELECT meta_select_mode, regexp_str FROM meta_service WHERE meta_id = '".$key."'");
		$meta =& $DBRESULT->fetchrow();
		$DBRESULT->free();
		# Regexp mode
		if ($meta["meta_select_mode"] == 2)	{
			$DBRESULT =& $pearDB->query("SELECT service_id FROM service WHERE service_description LIKE '".$meta["regexp_str"]."'");
			while($service =& $DBRESULT->fetchRow())	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($service["service_id"], $gbArr[4]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$DBRESULT2 =& $pearDB->query("SELECT hsr.host_host_id, hsr.hostgroup_hg_id FROM host_service_relation hsr WHERE hsr.service_service_id = '".$service["service_id"]."'");
					while($service2 =& $DBRESULT2->fetchRow())	{
						$BP = false;
						if ($ret["level"]["level"] == 1)	{
							array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
							array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
						}
						else if ($ret["level"]["level"] == 2)	{
							array_key_exists($service2["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
							array_key_exists($service2["hostgroup_hg_id"], $gbArr[3]) ? $BP = true : NULL;
						}
						else if ($ret["level"]["level"] == 3)
							$BP = true;
						if ($BP)	{
							if ($service2["hostgroup_hg_id"])	{
								$DBRESULT3 =& $pearDB->query("SELECT hgr.host_host_id FROM hostgroup_relation hgr WHERE hgr.hostgroup_hg_id = '".$service2["hostgroup_hg_id"]."'");
								while($service3 =& $DBRESULT3->fetchRow())	{
									$BP = false;
									if ($ret["level"]["level"] == 1)
										array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
									else if ($ret["level"]["level"] == 2)
										array_key_exists($service3["host_host_id"], $gbArr[2]) ? $BP = true : NULL;
									else if ($ret["level"]["level"] == 3)
										$BP = true;
									if ($BP)
										$str .= "<sv id='".$service3["host_host_id"]."_".$service["service_id"]."'/>\n";
								}	
								unset($service3);
								$DBRESULT3->free();						
							}
							else
								$str .= "<sv id='".$service2["host_host_id"]."_".$service["service_id"]."'/>\n";
						}
					}
					$DBRESULT2->free();
				}
			}
			$DBRESULT->free();
		}
		else if ($meta["meta_select_mode"] == 1)	{
			require_once("./class/centreonDB.class.php");
			
			$pearDBO = new CentreonDB("centstorage");
			
			$DBRESULT =& $pearDB->query("SELECT meta_id, host_id, metric_id FROM meta_service_relation msr WHERE meta_id = '".$key."' AND activate = '1'");
			while($metric =& $DBRESULT->fetchRow())	{
				$BP = false;
				if ($ret["level"]["level"] == 1)
					array_key_exists($metric["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 2)
					array_key_exists($metric["host_id"], $gbArr[2]) ? $BP = true : NULL;
				else if ($ret["level"]["level"] == 3)
					$BP = true;
				if ($BP)	{
					$DBRESULT2 =& $pearDBO->query("SELECT service_description FROM metrics m, index_data i WHERE m.metric_id = '".$metric["metric_id"]."' and m.index_id=i.id");
					$OService =& $DBRESULT2->fetchRow();
					$sv_id =& getMyServiceID($OService["service_description"], $metric["host_id"]);
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($sv_id, $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($sv_id, $gbArr[4]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)
						$str .= "<sv id='".$metric["host_id"]."_".$sv_id."'/>\n";
					$DBRESULT2->free();
				}
			}
			$DBRESULT->free();
		}
		$str .= "</chds>\n";		
		$str .= "</ms>\n";
	}
	
	$str .= "</dependencies>\n";
	$str .= "</osm_list>";
	write_in_file($handle, $str, $XMLConfigPath."osm_list.xml");
	fclose($handle);
	$DBRESULT->free();
	unset($str);
?>