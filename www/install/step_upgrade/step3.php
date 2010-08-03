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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/install/step_upgrade/step3.php $
 * SVN : $Id: step3.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */

	include_once $centreon_path . "/www/class/centreonDB.class.php";
    
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");
		
	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();

	$DBRESULT =& $pearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$confNDO =& $DBRESULT->fetchRow();
	$DBRESULT->free();

$return_false = 0;

aff_header("Centreon Upgrade Wizard", "Verifying Configuration", 3);	?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr">
  	<tr>
    	<th align="left">Component</th>
    	<th style="text-align: right;">Status</th>
  	</tr>
  	<tr>
   		<td><b>PHP Version 5.x</b></td>
    	<td align="right"><?php
			$php_version = phpversion();
	       	if(str_replace(".", "", $php_version) < "500" ){
	         	echo "<b><span class=stop>Invalid version ($php_version) Installed</span></b>";
			  	$return_false = 1;
	       	} else {
	          	echo "<b><span class=go>OK (ver $php_version)</span></b>";
	       	}?>
     	</td>
  </tr>
  <tr>
    	<td><b>PHP Extension</b></td>
    	<td align="right">&nbsp;</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;MySQL</b></td>
    	<td align="right"><?php
			if (extension_loaded('mysql')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Critical: mysql.so not loaded in php.ini</font></b>';
		    	$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;GD</b></td>
    	<td align="right"><?php
			if (extension_loaded('gd')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Critical: gd.so not loaded in php.ini</font></b>';
		    	$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;LDAP</b></td>
    	<td align="right"><?php
			if (extension_loaded('ldap')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="warning">Warning: ldap.so not loaded in php.ini</font></b>';
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;XML Writer</b></td>
    	<td align="right"><?php
			if (extension_loaded('xmlwriter'))
          		echo '<b><span class="go">OK</font></b>';
			else {
				echo '<b><span class="warning">Warning: xmlwriter.so not loaded in php.ini</font></b>';
				$return_false = 1;	
			}	?>
		</td>
  </tr>
  <tr>
		<td><b>&nbsp;&nbsp;&nbsp;MB String</b></td>
		<td align="right"><?php
        	if (extension_loaded('mbstring'))
            	echo '<b><span class="go">OK</font></b>';
            else {
                echo '<b><span class="warning">Critical: php-mbstring functions are not installed</font></b>';
                $return_false = 1;
            }       ?>
       	</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;PHP-POSIX</b></td>
    	<td align="right"><?php
			if (function_exists('posix_getpwuid'))
          		echo '<b><span class="go">OK</font></b>';
			else {
				echo '<b><span class="stop">Critical: php-posix functions are not installed</font></b>';
				$return_false = 1;	
			}?>
		</td>
  </tr>
  <tr>
		<td><b>&nbsp;&nbsp;&nbsp;PEAR</b></td>
    	<td align="right"><?php
    		$tab_path = split(":", get_include_path());
    		$ok = 0;
    		foreach ($tab_path as $path){
    			if (file_exists($path. '/PEAR.php')){
    				$_SESSION["include_path"] = $path;
    				$ok = 1;
    			}
    		}
			if ($ok){
				echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Warning: PHP Pear not found <br />'. $pear_path . '/PEAR.php</font></b>';
			    $return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
		<td><b>MySQL access</b></td>
    	<td align="right"><?php
    		if ($pearDBndo->hasGrants("ALTER")){
				echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Critical: User for NDO must have Alter permissions on NDO table</font></b>';
			    $return_false = 1;
			}?>
		</td>
  </tr>
</table>
<?php
aff_middle();
$str = '';
if ($return_false)
	$str = "<input class='button' type='submit' name='Recheck' value='Recheck' />";
$str .= "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next'";
if ($return_false)
	$str .= " disabled";
$str .= " />";
print $str;
aff_footer();
?>