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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/class/centreonLang.class.php $
 * SVN : $Id: centreonHost.class.php 8114 2009-05-20 09:51:24Z jmathis $
 * 
 */
 
 /*
  *  Language management class
  */
 class CentreonLang {
 	private $charset;
 	private $lang;
 	private $path;
 	private $charsetList;
 	
 	/*
 	 *  Constructor
 	 */
 	function CentreonLang($centreon_path, $oreon = NULL) { 						
		if (isset($oreon)) {
			$this->lang = $oreon->user->lang;
			$this->charset = $oreon->user->charset;
		}
		else {
			$this->lang = "en_US";
			$this->charset = "UTF-8";
		}
		$this->path = $centreon_path;
		$this->setCharsetList();
 	}
 	
 	/*
 	 *  Sets list of charsets
 	 */
 	private function setCharsetList() {
 		$this->charsetList = array( "ISO-8859-1", 
									"ISO-8859-2",
									"ISO-8859-3", 
									"ISO-8859-4",
									"ISO-8859-5", 
									"ISO-8859-6",
									"ISO-8859-7",
									"ISO-8859-8",
									"ISO-8859-9",
									"UTF-80",
									"UTF-83",
									"UTF-84",
									"UTF-85",
									"UTF-86",
									"ISO-2022-JP",
									"ISO-2022-KR",
									"ISO-2022-CN",
									"WINDOWS-1251",
									"CP866",
									"KOI8",
									"KOI8-E",
									"KOI8-R",
									"KOI8-U",
									"KOI8-RU",
									"ISO-10646-UCS-2",
									"ISO-10646-UCS-4",
									"UTF-7",
									"UTF-8",
									"UTF-16",
									"UTF-16BE",
									"UTF-16LE",
									"UTF-32",
									"UTF-32BE",
									"UTF-32LE",
									"EUC-CN",
									"EUC-GB",
									"EUC-JP",
									"EUC-KR",
									"EUC-TW",
									"GB2312",
									"ISO-10646-UCS-2",
									"ISO-10646-UCS-4",
									"SHIFT_JIS");
		sort($this->charsetList);
 	}
 	
 	/*
 	 *  Binds lang to the current Centreon page
 	 */
 	public function bindLang() { 		
		putenv("LANG=$this->lang");
		setlocale(LC_ALL, $this->lang);
		bindtextdomain("messages", $this->path."www/locale/");
		bind_textdomain_codeset("messages", $this->charset); 
		textdomain("messages");
 	}
 	 	
 	/*
 	 *  Lang setter
 	 */
 	public function setLang($newLang){
 		$this->lang = $newLang;
 	}
 	
 	/*
 	 *  Returns lang that is being used
 	 */
 	public function getLang() {
 		return $this->lang;	
 	}
 	
 	/*
 	 *  Charset Setter
 	 */
 	public function setCharset($newCharset) {
 		$this->charset = $newCharset;
 	}
 	
 	/*
 	 *  Returns charset that is being used
 	 */
 	public function getCharset() {
 		return $this->charset;
 	}
 	
 	/*
 	 *  Returns an array with a list of charsets
 	 */
 	public function getCharsetList() {
 		return $this->charsetList;
 	}
 }
 
 ?>