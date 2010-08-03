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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/reporting/dashboard/ajaxReporting_js.php $
 * SVN : $Id: ajaxReporting_js.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	
	if ($type == "Service") {
		$arg = "id=".$service_id."&host_id=".$host_id;
	} else {
		$arg = "id=".$id;
	}

	$arg .= "&session=".session_id()."&color[UP]=".$oreon->optGen["color_up"]."&color[UNDETERMINED]=".$oreon->optGen["color_undetermined"].
			"&color[DOWN]=".$oreon->optGen["color_down"]."&color[UNREACHABLE]=".$oreon->optGen["color_unreachable"].
			"&color[OK]=".$oreon->optGen["color_ok"]."&color[WARNING]=".$oreon->optGen["color_warning"].
			"&color[CRITICAL]=".$oreon->optGen["color_critical"]."&color[UNKNOWN]=".$oreon->optGen["color_unknown"];
	$arg = str_replace("#", "%23", $arg);
	$url = "./include/reporting/dashboard/xmlInformations/GetXml".$type.".php?".$arg;
?>
<script type="text/javascript">

var tl;

function initTimeline() {
	var eventSource = new Timeline.DefaultEventSource();
	var bandInfos = [
	Timeline.createBandInfo({
			eventSource:    eventSource,
			width:          "70%", 
			intervalUnit:   Timeline.DateTime.DAY, 
			intervalPixels: 300
	    }), 
		Timeline.createBandInfo({
	    	showEventText:  false,
	   		eventSource:    eventSource,
	    	width:          "30%", 
	    	intervalUnit:   Timeline.DateTime.MONTH, 
		    intervalPixels: 300
		})
	];

	bandInfos[1].syncWith = 0;
	bandInfos[1].highlight = true;
	bandInfos[1].eventPainter.setLayout(bandInfos[0].eventPainter.getLayout());
	 		  	
	tl = Timeline.create(document.getElementById("my-timeline"), bandInfos);
	
	Timeline.loadXML('<?php echo $url ?>', function(xml, url) { eventSource.loadXML(xml, url); });
}

</script>