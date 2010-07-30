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
 * SVN : $URL: http://svn.centreon.com/branches/centreon-2.1/www/include/monitoring/status/Common/commonJS.php $
 * SVN : $Id: commonJS.php 10473 2010-05-19 21:25:56Z jmathis $
 * 
 */
  
	if (!isset($oreon))
		exit();

?>

function getXhrC(){
	if (window.XMLHttpRequest) // Firefox et autres
	   var xhrC = new XMLHttpRequest();
	else if (window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrC = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrC = new ActiveXObject("Microsoft.XMLHTTP");
            }
	} else { // XMLHttpRequest non support2 par le navigateur
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
	   var xhrC = false;
	}
	return xhrC;
}

function addORdelTab(_name){
	var d = document.getElementsByName('next_check_case');
	if (d[0].checked == true) {
		_nc = 1;
	} else {
		_nc = 0;
	}
	monitoring_refresh();
}

function advanced_options(id){
	var d = document.getElementById(id);
	if (d) {
		if (d.style.display == 'block') {
			d.style.display='none';
		} else {
			d.style.display='block';
		}
	}
}

function construct_selecteList_ndo_instance(id){
	if (!document.getElementById("select_instance")) {
		var select_index = new Array();
		var _select_instance = document.getElementById(id);
		var _select = document.createElement("select");
		_select.name = "select_instance";
		_select.id = "select_instance";
		_select.onchange = function() {
			_instance = this.value; 
			_default_instance = this.value;
			xhr = new XMLHttpRequest();
			xhr.open('GET','./include/monitoring/status/Common/updateContactParam.php?uid=<?php echo $oreon->user->user_id; ?>&instance_id='+this.value, true);
			xhr.send(null);
			xhr.onreadystatechange = function() { monitoring_refresh(); };
		};
		var k = document.createElement('option');
		k.value= "ALL";
		_select.appendChild(k);
		var i = 1;
<?php
		$DBRESULT =& $pearDBndo->query("SELECT `instance_id`, instance_name FROM `".getNDOPrefix()."instances`");
		while ($nagios_server =& $DBRESULT->fetchRow())	{
			?>
			var m = document.createElement('option');
			m.value= "<?php echo $nagios_server["instance_id"]; ?>";
			_select.appendChild(m);
			var n = document.createTextNode("<?php echo $nagios_server["instance_name"]; ?>   ");
			m.appendChild(n);
			_select.appendChild(m);
			select_index["<?php echo $nagios_server["instance_id"]; ?>"] = i;
			i++;
<?php 	}	?>
		_select.selectedIndex = select_index[_default_instance];
		_select_instance.appendChild(_select);
	}
}

function construct_HostGroupSelectList(id) {
	if (!document.getElementById("hostgroups")) {
		var select_index = new Array();
		var _select_hostgroups = document.getElementById(id);
		var _select = document.createElement("select");
		_select.name = "hostgroups";
		_select.id = "hostgroups";
		_select.onchange = function() { 
			_default_hg = this.value;
			xhr = new XMLHttpRequest();
			xhr.open('GET','./include/monitoring/status/Common/updateContactParamHostGroups.php?uid=<?php echo $oreon->user->user_id; ?>&hostgroups='+this.value, true);
			xhr.send(null);
			xhr.onreadystatechange = function() { 
				monitoring_refresh();
			};
		};
		var k = document.createElement('option');
		k.value= "0";
		_select.appendChild(k);
		var i = 1;
<?php
		$hg = array();
		if (!$oreon->user->access->admin) {
			$query = "SELECT DISTINCT hg.hg_alias " .
			 		"FROM hostgroup hg, acl_resources_hg_relations arhr " .
			 		"WHERE hg.hg_id = arhr.hg_hg_id " .
			 		"AND arhr.acl_res_id IN (".$oreon->user->access->getResourceGroupsString().") " .
			 		"AND hg.hg_activate = '1' ORDER BY hg.hg_alias"; 		
			 $DBRESULT =& $pearDB->query($query);
			 while ($data =& $DBRESULT->fetchRow()) {
			 	$hg[$data["hg_alias"]] = 1;
			 }
			 $DBRESULT->free();
			unset($data);
		}

		$DBRESULT =& $pearDBndo->query("SELECT DISTINCT `alias` FROM `".getNDOPrefix()."hostgroups` WHERE alias NOT LIKE 'meta_hostgroup' ORDER BY `alias`");
		while ($hostgroups =& $DBRESULT->fetchRow()) {
			if ($oreon->user->access->admin || ($oreon->user->access->admin == 0 && isset($hg[$hostgroups["alias"]]))) { ?>
				var m = document.createElement('option');
				m.value= "<?php echo $hostgroups["alias"]; ?>";
				_select.appendChild(m);
				var n = document.createTextNode("<?php echo $hostgroups["alias"]; ?>   ");
				m.appendChild(n);
				_select.appendChild(m);
				select_index["<?php echo $hostgroups["alias"]; ?>"] = i;
				i++;
<?php 		}	
		}
?>
		_select.selectedIndex = select_index[_default_hg];
		_select_hostgroups.appendChild(_select);
	}
}

function viewDebugInfo(_str) {
	if (_debug)	{
		_nb = _nb + 1;
		var mytable=document.getElementById("debugtable")
		var newrow=mytable.insertRow(0) //add new row to end of table
		var newcell=newrow.insertCell(0) //insert new cell to row
		newcell.innerHTML='<td>line:' + _nb + ' ' + _str + '</td>';
	}
}

function change_page(page_number) {
	viewDebugInfo('change page');
	_num = page_number;
	monitoring_refresh();
	pagination_changed();
	set_page(page_number);
}

function change_type_order(_type) {
	if (_sort_type != _type){
		_sort_type = _type;
		monitoring_refresh();
	}
}

function change_order(_odr) {
	if (_order == 'ASC'){
		_order = 'DESC';
	} else
		_order = 'ASC';
	monitoring_refresh();
}


function change_limit(l) {
	_limit= l;
	pagination_changed();
	monitoring_refresh();
	var _sel1 = document.getElementById('l1');
	for(i=0 ; _sel1[i] && _sel1[i].value != l ; i++)
		;
	_sel1.selectedIndex = i;
	set_limit(l);
}

var _numRows = 0;

function getVar(nomVariable) {
	var infos = location.href.substring(location.href.indexOf("?")+1, location.href.length)+"&";
	if (infos.indexOf("#")!=-1)
	infos = infos.substring(0,infos.indexOf("#"))+"&";
	var variable=''
	{
		nomVariable = nomVariable + "=";
		var taille = nomVariable.length;
		if (infos.indexOf(nomVariable)!=-1)
		variable = infos.substring(infos.indexOf(nomVariable)+taille,infos.length).substring(0,infos.substring(infos.indexOf(nomVariable)+taille,infos.length).indexOf("&"))
	}
	return variable;
}

function mk_img(_src, _alt)	{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
  	if (_img.complete){
  		_img.alt = _alt;	
  	} else {
  		_img.alt = "Image could not be generated"; 
  	}
	return _img;
}

function mk_imgOrder(_src, _alt)	{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
  	_img.style.paddingLeft = '10px';
  	_img.style.marginBottom = '0.5px';
  	if (_img.complete){
  		_img.alt = _alt;	
  	} else {
  		_img.alt = "Image could not be generated"; 
  	}
	return _img;
}

function mk_pagination(resXML){
	viewDebugInfo('mk pagination');

	var flag = 0;
	var infos = resXML.getElementsByTagName('i');

	var _nr = infos[0].getElementsByTagName('numrows')[0].firstChild.data;
	var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.data;
	var _nn = infos[0].getElementsByTagName("num")[0].firstChild.data;

	if (_numRows != _nr){
		_numRows = _nr;
		flag = 1;
	}
	if (_num != _nn){
		_num = _nn;
		flag = 1;
	}
	if (_limit != _nl){
		_limit = _nl;
		flag = 1;
	}
	if (flag == 1){
		pagination_changed();
	}
}

function mk_paginationFF(resXML){
	viewDebugInfo('mk pagination');

	var flag = 0;
	var infos = resXML.getElementsByTagName('i');
	if (infos[0]) {
		var _nr = infos[0].getElementsByTagName('numrows')[0].firstChild.data;
		var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.data;
		var _nn = infos[0].getElementsByTagName("num")[0].firstChild.data;
	
		if (_numRows != _nr){
			_numRows = _nr;
			flag = 1;
		}
		if (_num != _nn){
			_num = _nn;
			flag = 1;
		}
		if (_limit != _nl){
			_limit = _nl;
			flag = 1;
		}
		if (flag == 1){
			pagination_changed();
		}
	}
}

function pagination_changed(){
	viewDebugInfo('pagination_changed');
	
	// compute Max Page
	var page_max = 0;
	if ((_numRows % _limit) == 0)	{
		page_max =  Math.round( (_numRows / _limit));
	} else{
		page_max =  Math.round( (_numRows / _limit) + 0.5);
	}

	if (_num >= page_max && _numRows && _num > 0){
		viewDebugInfo('!!num!!'+_num);
		viewDebugInfo('!!max!!'+page_max);
		_num = Number(page_max) - 1;
		viewDebugInfo('new:'+_num);
		monitoring_refresh();
	}

	var p = getVar('p');
	var o = getVar('o');
	var search = '' + getVar('search');
	var _numnext = Number(_num) + 1;
	var _numprev = Number(_num) - 1;

<?php	
	for ($i = 1; $i <= 2; $i++) { ?>
	var _img_previous<?php echo $i; ?> 	= mk_img("./img/icones/16x16/arrow_left_blue.gif", "previous");
	var _img_next<?php echo $i; ?> 		= mk_img("./img/icones/16x16/arrow_right_blue.gif", "next");
	var _img_first<?php echo $i; ?> 	= mk_img("./img/icones/16x16/arrow_left_blue_double.gif", "first");
	var _img_last<?php echo $i; ?> 		= mk_img("./img/icones/16x16/arrow_right_blue_double.gif", "last");

	var _linkaction_right<?php echo $i; ?> = document.createElement("a");
	_linkaction_right<?php echo $i; ?>.href = '#' ;
	_linkaction_right<?php echo $i; ?>.indice = _numnext;
	_linkaction_right<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_right<?php echo $i; ?>.appendChild(_img_next<?php echo $i; ?>);

	var _linkaction_last<?php echo $i; ?> = document.createElement("a");
	_linkaction_last<?php echo $i; ?>.href = '#' ;
	_linkaction_last<?php echo $i; ?>.indice = page_max - 1;
	_linkaction_last<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_last<?php echo $i; ?>.appendChild(_img_last<?php echo $i; ?>);

	var _linkaction_first<?php echo $i; ?> = document.createElement("a");
	_linkaction_first<?php echo $i; ?>.href = '#' ;
	_linkaction_first<?php echo $i; ?>.indice = 0;
	_linkaction_first<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_first<?php echo $i; ?>.appendChild(_img_first<?php echo $i; ?>);

	var _linkaction_left<?php echo $i; ?> = document.createElement("a");
	_linkaction_left<?php echo $i; ?>.href = '#' ;
	_linkaction_left<?php echo $i; ?>.indice = _numprev;
	_linkaction_left<?php echo $i; ?>.onclick=function(){change_page(Number(this.indice))}
	_linkaction_left<?php echo $i; ?>.appendChild(_img_previous<?php echo $i; ?>);

	var _pagination<?php echo $i; ?> = document.getElementById('pagination<?php echo $i; ?>');
	
	_pagination<?php echo $i; ?>.innerHTML ='';
	if (_num > 0){
		_pagination<?php echo $i; ?>.appendChild(_linkaction_first<?php echo $i; ?>);
		_pagination<?php echo $i; ?>.appendChild(_linkaction_left<?php echo $i; ?>);
	}
<?php } 
	
	/*
	 * Page Number
	 */

for ($i = 1; $i <= 2; $i++) { ?>
	var istart = 0;
	for (i = 5, istart = _num; istart && i > 0 && istart > 0; i--)
		istart--;
	
	for (i2 = 0, iend = _num; ( iend <  (_numRows / _limit -1)) && ( i2 < (5 + i)); i2++)
		iend++;
	
	for (i = istart; i <= iend && page_max > 1; i++){
		var span_space = document.createElement("span");
		span_space.innerHTML = '&nbsp;';
		_pagination<?php echo $i; ?>.appendChild(span_space);

		var _linkaction_num = document.createElement("a");
  		_linkaction_num.href = '#' ;
  		_linkaction_num.indice = i;
  		_linkaction_num.onclick=function(){change_page(this.indice)};
		_linkaction_num.innerHTML = parseInt(i + 1);
		_linkaction_num.className = "otherPageNumber";
		
		if (i == _num)
			_linkaction_num.className = "currentPageNumber";
		_pagination<?php echo $i; ?>.appendChild(_linkaction_num);

		var span_space = document.createElement("span");
		span_space.innerHTML = '&nbsp;';
		_pagination<?php echo $i; ?>.appendChild(span_space);
	}

	if (_num < page_max - 1){
		_pagination<?php echo $i; ?>.appendChild(_linkaction_right<?php echo $i; ?>);
		_pagination<?php echo $i; ?>.appendChild(_linkaction_last<?php echo $i; ?>);
	}
<?php 
} ?>
	
	var _sel1 = document.getElementById('sel1');
	_sel1.innerHTML ='';

	var _sel2 = document.getElementById('sel2');
	_sel2.innerHTML ='';

	var sel1 = document.createElement('select');
	sel1.name = 'l';
	sel1.id = 'l1';
	sel1.onchange = function() { change_limit(this.value) };
	
	var sel2 = document.createElement('select');
	sel2.name = 'l';
	sel2.id = 'l2';
	sel2.onchange = function() { change_limit(this.value) };

	var _max = 100;
	if (_limit > 100) {
		_max = 1000;
	}
	
	var _index;
	for (i = 10, j = 0 ; i <= 100 ; i += 10, j++) {		
		var k = document.createElement('option');
		k.value = i;		
		sel1.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}	
	for (i = 200; i <= 500 ; i += 100, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel1.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}
	
	for (i = 10, j = 0; i <= 100 ; i += 10, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel2.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}
	for (i = 200; i <= 500 ; i += 100, j++) {
		var k = document.createElement('option');
		k.value = i;
		sel2.appendChild(k);
		if (_limit == i) {
			_index = j;
		}
		var l = document.createTextNode(i);
		k.appendChild(l);
	}

	sel1.selectedIndex = _index;
	_sel1.appendChild(sel1);
	
	sel2.selectedIndex = _index;
	_sel2.appendChild(sel2);
}

function escapeURI(La){
	if (encodeURIComponent) {
    	return encodeURIComponent(La);
  	}
  	if (escape) {
  	  return escape(La)
  	}
}

function mainLoop(){
 	_currentInputField = document.getElementById('input_search');
  	_currentInputFieldValue = document.getElementById('input_search').value;
  	if ((_currentInputFieldValue.length >= 3 || _currentInputFieldValue.length == 0) && _oldInputFieldValue!=_currentInputFieldValue){
    	var valeur=escapeURI(_currentInputFieldValue);
		_search = valeur;		
		if (!_lock){
			monitoring_refresh();
			set_search(_search);						
			if ( _currentInputFieldValue.length >= 3)
				_currentInputField.className = "search_input_active";			
			else
				_currentInputField.className = "search_input";
		}
	}
	_oldInputFieldValue=_currentInputFieldValue;
	setTimeout("mainLoop()",222);
}

function set_limit(limit)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&limit="+limit+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_search(search)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&search="+search+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}

function set_page(page)	{
	var xhrM = getXhrC();
	xhrM.open("POST","./include/monitoring/status/Common/setHistory.php",true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	_var = "sid=<?php echo $sid; ?>&page="+page+"&url=<?php echo $url; ?>";
	xhrM.send(_var);
}