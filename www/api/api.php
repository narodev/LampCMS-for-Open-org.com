<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


include '../../!inc.php';

require($lampcmsClasses.'Base.php');
require($lampcmsClasses.'Api'.DIRECTORY_SEPARATOR.'Api.php');

try{

	$Request = $Registry->Request;
	$a  = $Request['a'];
	$v  = $Request->get('v', 'i', 1);

	d('a: '.$a.' $Request: '.print_r($Request->getArray(), 1));
	$controller = ucfirst($a);
	include($lampcmsClasses.'Api'.DIRECTORY_SEPARATOR.'v'.$v.DIRECTORY_SEPARATOR.$controller.'.php');
	$class = '\Lampcms\\Api\\v'.$v.'\\'.$controller;
	d('class: '.$class);

	$o = new $class($Registry);
	$Response = $o->getResponse();
	$Response->send();
	fastcgi_finish_request();

}catch(\OutOfBoundsException $e){
	/**
	 * Special case is OutOfBoundsException which
	 * is our special way of saying exit(); but do it
	 * gracefully - let it be caught here and then do nothing
	 * This is better than using exit() because on some servers
	 * exit may terminate the whole fastcgi process instead of just
	 * stopping this one script
	 */
	$errMessage = $e->getMessage();
	d('Got exit signal from '.$e->getTraceAtString());
	if(!empty(trim($errMessage))){
		echo '<div class="exit_error">'.$errMessage.'</div>';
	}
	fastcgi_finish_request();

}catch (\Exception $e){

	header("HTTP/1.0 500 Exception");
	header("Content-Type:text/html; charset=utf-8");
	$err = strip_tags($e->getMessage());
	echo $err;
	fastcgi_finish_request();

	$extra = (isset($_SERVER)) ? ' $_SERVER: '.print_r($_SERVER, 1) : ' no $_SERVER';
	$extra .= ' file: '.$e->getFile(). ' line: '.$e->getLine().' trace: '.$e->getTraceAsString();

	if(strlen(trim(constant('LAMPCMS_DEVELOPER_EMAIL'))) > 1){
		@mail(LAMPCMS_DEVELOPER_EMAIL, '500 Error in index.php', $err.$extra);
	}
}


