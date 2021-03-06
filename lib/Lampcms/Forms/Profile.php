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


namespace Lampcms\Forms;

use \Lampcms\Validate;

class Profile extends Form
{

	/**
	 * Name of form template file
	 * The name of actual template should be
	 * set in sub-class
	 *
	 * @var string
	 */
	protected $template = 'tplFormprofile';


	protected function init(){
		$this->setVar('submit', $this->_('Save'));
	}
	/**
	 * Concrete form validator for this form
	 * (non-PHPdoc)
	 * @see Form::doValidate()
	 */
	protected function doValidate(){

		$this->validateDob()->validateAvatar();
	}


	protected function validateDob(){
		$dob = $this->Registry->Request['dob'];
		if(!empty($dob) && !Validate::validateDob($dob)){
			$this->setError('dob', 'Invalid format of date string OR invalid values');
		}

		return $this;
	}


	/**
	 * If form hasUploads and has uploaded file 'profile_image'
	 * then: check that if does not have 'error' code
	 * theck that the 'size' > 0 and 'tmp_name' !== 'none' and not empty
	 * check that size < (MAX_AVATAR_FILE_SIZE in setting)
	 * check that if 'type' not empty and is one of allowed image formats
	 * If any of this pre-checks fail then delete the uploaded file
	 * and set the form error
	 *
	 * @return object $this
	 */
	protected function validateAvatar(){
		d('cp');
		if($this->hasUploads() && !empty($this->aUploads['profile_image'])){
			$a = $this->aUploads['profile_image'];

			if( !is_array($a) || (0 == $a['size'] && empty($a['name'])) ){
				d('avatar was not uploaded');

				return $this;
			}

			d('cp');

			/**
			 * If bad error code
			 */
			if(UPLOAD_ERR_OK !== $errCode = $a['error']){
				e('Upload of avatar failed with error code '.$a['error']);
				if(UPLOAD_ERR_FORM_SIZE === $errCode){
					$this->setError('profile_image', 'Uploaded file exceeds maximum allowed size');
					return $this;
				} elseif(UPLOAD_ERR_INI_SIZE === $errCode){
					$this->setError('profile_image', 'Uploaded file exceeds maximum upload size');
					return $this;
				} else {
					$this->setError('profile_image', 'There was an error uploading the avatar file');
					return $this;
				}
			} else {

				$maxSize = $this->Registry->Ini->MAX_AVATAR_UPLOAD_SIZE;
				d('$maxSize '.$maxSize);

				/**
				 * Check If NOT an image
				 */
				if(!empty($a['type'])){
					if('image' !== substr($a['type'], 0, 5)){
						$this->setError('profile_image', 'Uploaded file was not an image');
						return $this;
					}elseif('image/gif' === $a['type'] && !\function_exists('imagecreatefromgif')){
						$this->setError('profile_image', 'Gif image format is not supported at this time. Please upload an image in JPEG format');
						return $this;
					} elseif('image/png' === $a['type'] && !\function_exists('imagecreatefrompng')){
						$this->setError('profile_image', 'PNG image format is not supported at this time. Please upload an image in JPEG format');
						return $this;
					}
				}


				/**
				 * If image too large
				 */
				if(!empty($a['tmp_name'])){
					if(false === $size = @\filesize($a['tmp_name'])){
						$this->setError('profile_image', 'There was an error uploading the avatar file');
						return $this;
					}

					d('size: '.$size);

					if(($size / $maxSize) > 1.1){
						d('$size / $maxSize: '.$size / $maxSize);
						$this->setError('profile_image', 'File too large. It must be under '.($maxSize/1024000).'MB');
					}
				}
			}
		}

		return $this;
	}

}
