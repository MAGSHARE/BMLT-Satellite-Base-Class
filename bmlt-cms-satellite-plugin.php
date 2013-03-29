<?php
/****************************************************************************************//**
*   \file   bmlt-cms-satellite-plugin.php                                                   *
*                                                                                           *
*   \brief  This is a generic CMS plugin class for a BMLT satellite client.                 *
*   \version 3.0.3                                                                          *
*                                                                                           *
*   This file is part of the BMLT Common Satellite Base Class Project. The project GitHub   *
*   page is available here: https://github.com/MAGSHARE/BMLT-Common-CMS-Plugin-Class        *
*                                                                                           *
*   This file is part of the Basic Meeting List Toolbox (BMLT).                             *
*                                                                                           *
*   Find out more at: http://bmlt.magshare.net                                              *
*                                                                                           *
*   BMLT is free software: you can redistribute it and/or modify                            *
*   it under the terms of the GNU General Public License as published by                    *
*   the Free Software Foundation, either version 3 of the License, or                       *
*   (at your option) any later version.                                                     *
*                                                                                           *
*   BMLT is distributed in the hope that it will be useful,                                 *
*   but WITHOUT ANY WARRANTY; without even the implied warranty of                          *
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                           *
*   GNU General Public License for more details.                                            *
*                                                                                           *
*   You should have received a copy of the GNU General Public License                       *
*   along with this code.  If not, see <http://www.gnu.org/licenses/>.                      *
********************************************************************************************/

// define ( '_DEBUG_MODE_', 1 ); //Uncomment for easier JavaScript debugging.

// Include the satellite driver class.
require_once ( dirname ( __FILE__ ).'/BMLT-Satellite-Driver/bmlt_satellite_controller.class.php' );

global $bmlt_localization;  ///< Use this to control the localization.

$tmp_local = 'en';  ///< We will always fall back to English. It is possible that the plugin may not be localized to the desired language.

if ( isset ( $bmlt_localization ) && $bmlt_localization && file_exists ( dirname ( __FILE__ )."/lang/lang_".$bmlt_localization.".php" ) )
    {
    $tmp_local = $bmlt_localization;
    }

require_once ( dirname ( __FILE__ )."/lang/lang_".$tmp_local.".php" );

/***********************************************************************/
/** \brief	This is an open-source JSON encoder that allows us to support
	older versions of PHP (before the <a href="http://us3.php.net/json_encode">json_encode()</a> function
	was implemented). It uses json_encode() if that function is available.
	
	This is from <a href="http://www.bin-co.com/php/scripts/array2json/">Bin-Co.com</a>.
	
	This crap needs to be included to be aboveboard and legal. You can still re-use the code, but
	you need to make sure that the comments below this are included:
	

	Copyright (c) 2004-2007, Binny V Abraham

	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without modification, are permitted provided
	that the following conditions are met:
	
	*	Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
	*	Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer
		in the documentation and/or other materials provided with the distribution.
	
	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING,
	BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
	IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
	OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
	PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
	OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
	EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
function array2json (
					$arr	///< An associative string, to be encoded as JSON.
					)
	{
	if(function_exists('json_encode'))
		{
		return json_encode($arr); //Lastest versions of PHP already has this functionality.
		}
	
	$parts = array();
	$is_list = false;

	//Find out if the given array is a numerical array
	$keys = array_keys($arr);
	$max_length = count($arr)-1;
	if(($keys[0] == 0) and ($keys[$max_length] == $max_length))
		{//See if the first key is 0 and last key is length - 1
		$is_list = true;
		for ($i=0; $i<count($keys); $i++)
			{ //See if each key correspondes to its position
			if($i != $keys[$i])
				{ //A key fails at position check.
				$is_list = false; //It is an associative array.
				break;
				}
			}
		}

	foreach($arr as $key=>$value)
		{
		if(is_array($value))
			{ //Custom handling for arrays
			if($is_list)
				{
				$parts[] = array2json($value); /* :RECURSION: */
				}
			else
				{
				$parts[] = '"' . $key . '":' . array2json($value); /* :RECURSION: */
				}
				
			}
		else
			{
			$str = '';
			if ( !$is_list )
				{
				$str = '"' . $key . '":';
				}

			//Custom handling for multiple data types
			if ( is_numeric($value) )
				{
				$str .= $value; //Numbers
				}
			elseif ( $value === false )
				{
				$str .= 'false'; //The booleans
				}
			elseif ( $value === true )
				{
				$str .= 'true';
				}
			elseif ( isset ($value) && $value )
				{
				$str .= '"' . addslashes($value) . '"'; //All other things
				}
			// :TODO: Is there any more datatype we should be in the lookout for? (Object?)

			$parts[] = $str;
			}
		}
	
	$json = implode ( ',', $parts );
	
	if( $is_list )
		{
		return '[' . $json . ']'; //Return numerical JSON
		}
	else
		{
		return '{' . $json . '}'; //Return associative JSON
		}
	}

function BMLTPlugin_weAreMobile($in_http_vars)
    {
    $language = BMLTPlugin::mobile_sniff_ua($in_http_vars);
    return ($language == 'wml') || ($language == 'xhtml_mp') || ($language == 'smartphone');
    }

/****************************************************************************************//**
*   \class BMLTPlugin                                                                       *
*                                                                                           *
*   \brief This is the class that implements and encapsulates the plugin functionality.     *
*   A single instance of this is created, and manages the plugin.                           *
*                                                                                           *
*   This plugin registers errors by echoing HTML comments, so look at the source code of    *
*   the page if things aren't working right.                                                *
********************************************************************************************/
class BMLTPlugin extends BMLT_Localized_BaseClass
{
    /************************************************************************************//**
    *                           STATIC DATA MEMBERS (SINGLETON)                             *
    ****************************************************************************************/
    
    /// This is a SINGLETON pattern. There can only be one...
    static  $g_s_there_can_only_be_one = null;                              ///< This is a static variable that holds the single instance.
    
    /************************************************************************************//**
    *                           STATIC DATA MEMBERS (DEFAULTS)                              *
    *   In Version 2, these are all ignored:                                                *
    *       $default_bmlt_fullscreen                                                        *
    *       $default_support_old_browsers                                                   *
    *       $default_sb_array                                                               *
    ****************************************************************************************/
    
    static  $adminOptionsName = "BMLTAdminOptions";                         ///< The name, in the database, for the version 1 options for this plugin.
    static  $admin2OptionsName = "BMLT2AdminOptions";                       ///< These options are for version 2.
    
    // These are the old settings that we still care about.
    static  $default_rootserver = '';                                       ///< This is the default root BMLT server URI.
    static  $default_map_center_latitude = 29.764377375163125;              ///< This is the default basic search map center latitude
    static  $default_map_center_longitude = -95.4931640625;                 ///< This is the default basic search map center longitude
    static  $default_map_zoom = 8;                                          ///< This is the default basic search map zoom level
    static  $default_details_map_zoom = 11;                                 ///< This is the default basic search map zoom level
    static  $default_location_checked = 0;                                  ///< If nonzero, then the "This is a location" checkbox will be preselected.
    static  $default_location_services = 0;                                 ///< This tells the new default implementation whether or not location services should be available only for mobile devices.
    static  $default_new_search = '';                                       ///< If this is set to something, then a new search uses the exact URI.
    static  $default_gkey = '';                                             ///< This is only necessary for older versions.
    static  $default_additional_css = '';                                   ///< This is additional CSS that is inserted inline into the <head> section.
    static  $default_initial_view = '';                                     ///< The initial view for old-style BMLT. It can be 'map', 'text', 'advanced', 'advanced map', 'advanced text' or ''.
    static  $default_theme = 'default';                                     ///< This is the default for the "style theme" for the plugin. Different settings can have different themes.
    static  $default_language = 'en';                                       ///< The default language is English, but the root server can override.
    static  $default_language_string = 'English';                           ///< The default language is English, and the name is spelled out, here.
    static  $default_distance_units = 'mi';                                 ///< The default distance units are miles.
    static  $default_grace_period = 15;                                     ///< The default grace period for the mobile search (in minutes).
    static  $default_time_offset = 0;                                       ///< The default time offset from the main server (in hours).
    static  $default_duration = '1:30';                                     ///< The default duration of meetings.

    /************************************************************************************//**
    *                               STATIC DATA MEMBERS (MISC)                              *
    ****************************************************************************************/
    
    static  $local_options_success_time = 2000;                             ///< The number of milliseconds a success message is displayed.
    static  $local_options_failure_time = 5000;                             ///< The number of milliseconds a failure message is displayed.

    /************************************************************************************//**
    *                                  DYNAMIC DATA MEMBERS                                 *
    ****************************************************************************************/
    
    var $my_driver = null;              ///< This will contain an instance of the BMLT satellite driver class.
    var $my_params = null;              ///< This will contain the $this->my_http_vars and $_POST query variables.
    var $my_http_vars = null;           ///< This will hold all of the query arguments.
    
    /************************************************************************************//**
    *                                    FUNCTIONS/METHODS                                  *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *   \brief Get the instance                                                             *
    *                                                                                       *
    *   \return An instance  of BMLTPlugin                                                  *
    ****************************************************************************************/
    static function get_plugin_object ()
        {
        return self::$g_s_there_can_only_be_one;
        }
    
    /****************************************************************************************//**
    *   \brief Checks the UA of the caller, to see if it should return XHTML Strict or WML.     *
    *                                                                                           *
    *   NOTE: This is very, very basic. It is not meant to be a studly check, like WURFL.       *
    *                                                                                           *
    *   \returns A string. The supported type ('xhtml', 'xhtml_mp' or 'wml')                    *
    ********************************************************************************************/
    static function mobile_sniff_ua (   $in_http_vars   ///< The query variables.
                                    )
    {
        if ( isset ( $in_http_vars['WML'] ) && (intval ( $in_http_vars['WML'] ) == 1) )
            {
            $language = 'wml';
            }
        elseif ( isset ( $in_http_vars['WML'] ) && (intval ( $in_http_vars['WML'] ) == 2) )
            {
            $language = 'xhtml_mp';
            }
        else
            {
            if (!isset($_SERVER['HTTP_ACCEPT']))
                {
                return false;
                }
        
            $http_accept = explode (',', $_SERVER['HTTP_ACCEPT']);
        
            $accept = array();
        
            foreach ($http_accept as $type)
                {
                $type = strtolower(trim(preg_replace('/\;.*$/', '', preg_replace ('/\s+/', '', $type))));
        
                $accept[$type] = true;
                }
        
            $language = 'xhtml';
        
            if (isset($accept['text/vnd.wap.wml']))
                {
                $language = 'wml';
        
                if (isset($accept['application/xhtml+xml']) || isset($accept['application/vnd.wap.xhtml+xml']))
                    {
                    $language = 'xhtml_mp';
                    }
                }
            else
                {
                if (    preg_match ( '/ipod/i', $_SERVER['HTTP_USER_AGENT'] )
                    ||  preg_match ( '/ipad/i', $_SERVER['HTTP_USER_AGENT'] )
                    ||  preg_match ( '/iphone/i', $_SERVER['HTTP_USER_AGENT'] )
                    ||  preg_match ( '/android/i', $_SERVER['HTTP_USER_AGENT'] )
                    ||  preg_match ( '/blackberry/i', $_SERVER['HTTP_USER_AGENT'] )
                    ||  preg_match ( "/opera\s+mini/i", $_SERVER['HTTP_USER_AGENT'] )
                    ||  isset ( $in_http_vars['simulate_smartphone'] )
                    )
                    {
                    $language = 'smartphone';
                    }
                }
            }
        return $language;
    }
        
    /************************************************************************************//**
    *                           ACCESSORS AND INTERNAL FUNCTIONS                            *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *   \brief Accessor: This gets the driver object.                                       *
    *                                                                                       *
    *   \returns a reference to the bmlt_satellite_controller driver object                 *
    ****************************************************************************************/
    function &get_my_driver ()
        {
        return $this->my_driver;
        }
    
    /************************************************************************************//**
    *   \brief Loads a parameter list.                                                      *
    *                                                                                       *
    *   \returns a string, containing the joined parameters.                                *
    ****************************************************************************************/
    static function get_params ( $in_array )
        {
        $my_params = '';

        foreach ( $in_array as $key => $value )
            {
            if ( ($key != 'lang_enum') && isset ( $in_array['direct_simple'] ) || (!isset ( $in_array['direct_simple'] ) && $key != 'switcher') && ($key != 'redirect_ajax_json') )    // We don't propagate switcher or the language.
                {
                if ( isset ( $value ) && is_array ( $value ) && count ( $value ) )
                    {
                    foreach ( $value as $val )
                        {
                        if ( isset ( $val ) &&  is_array ( $val ) && count ( $val ) )
                            {
                            // This stupid, stupid, kludgy dance, is because Drupal 7
                            // Doesn't seem to acknowledge the existence of the join() or
                            // implode() functions, and puts out a notice.
                            $val_ar = '';
                            
                            foreach ( $val as $v )
                                {
                                if ( $val_ar )
                                    {
                                    $val_ar .= ',';
                                    }
                                
                                $val_ar .= $v;
                                }
                                
                            $val = strval ( $val_ar );
                            }
                        elseif ( !isset ( $val ) )
                            {
                            $val = '';
                            }

                        $my_params .= '&'.urlencode ( $key ) ."[]=". urlencode ( $val );
                        }
                    $key = null;
                    }
                
                if ( $key )
                    {
                    $my_params .= '&'.urlencode ( $key );
                    
                    if ( $value )
                        {
                        $my_params .= "=". urlencode ( $value );
                        }
                    }
                }
            }
        return $my_params;
        }
    
    /************************************************************************************//**
    *   \brief Loads the parameter list.                                                    *
    ****************************************************************************************/
    function load_params ( )
        {
        $this->my_params = self::get_params ( $this->my_http_vars );
        }

    /************************************************************************************//**
    *   \brief This will parse the given text, to see if it contains the submitted code.    *
    *                                                                                       *
    *   The code can be contained in EITHER an HTML comment (<!--CODE-->), OR a double-[[]] *
    *   notation.                                                                           *
    *                                                                                       *
    *   \returns Boolean true if the code is found (1 or more instances), OR an associative *
    *   array of data that is associated with the code (anything within parentheses). Null  *
    *   is returned if there is no shortcode detected.                                      *
    ****************************************************************************************/
    static function get_shortcode ( $in_text_to_parse,  ///< The text to search for shortcodes
                                    $in_code            ///< The code that w're looking for.
                                    )
        {
        $ret = null;
        
        $code_regex_html = "\<\!\-\-\s?".preg_quote ( strtolower ( trim ( $in_code ) ) )."\s?(\(.*?\))?\s?\-\-\>";
        $code_regex_brackets = "\[\[\s?".preg_quote ( strtolower ( trim ( $in_code ) ) )."\s?(\(.*?\))?\s?\]\]";
        
        $matches = array();
      
        if ( preg_match ( '#'.$code_regex_html.'#i', $in_text_to_parse, $matches ) || preg_match ( '#'.$code_regex_brackets.'#i', $in_text_to_parse, $matches ) )
            {
            if ( !isset ( $matches[1] ) || !($ret = trim ( $matches[1], '()' )) ) // See if we have any parameters.
                {
                $ret = true;
                }
            }
        
        return $ret;
        }

    /************************************************************************************//**
    *   \brief This will parse the given text, to see if it contains the submitted code.    *
    *                                                                                       *
    *   The code can be contained in EITHER an HTML comment (<!--CODE-->), OR a double-[[]] *
    *   notation.                                                                           *
    *                                                                                       *
    *   \returns A string, consisting of the new text.                                      *
    ****************************************************************************************/
    static function replace_shortcode ( $in_text_to_parse,      ///< The text to search for shortcodes
                                        $in_code,               ///< The code that w're looking for.
                                        $in_replacement_text    ///< The text we'll be replacing the shortcode with.
                                        )
        {
        $code_regex_html = "#(\<p[^\>]*?\>)?\<\!\-\-\s?".preg_quote ( strtolower ( trim ( $in_code ) ) )."\s?(\(.*?\))?\s?\-\-\>(\<\/p>)?#i";
        $code_regex_brackets = "#(\<p[^\>]*?\>)?\[\[\s?".preg_quote ( strtolower ( trim ( $in_code ) ) )."\s?(\(.*?\))?\s?\]\](\<\/p>)?#i";

        $ret = preg_replace ( $code_regex_html, $in_replacement_text, $in_text_to_parse, 1 );
        $ret = preg_replace ( $code_regex_brackets, $in_replacement_text, $ret, 1 );
        
        return $ret;
        }
    
    /************************************************************************************//**
    *                               OPTIONS MANAGEMENT                                      *
    *****************************************************************************************
    *   This takes some 'splainin'.                                                         *
    *                                                                                       *
    *   The admin2 options track how many servers we're tracking, and allow the admin to    *
    *   increment by 1. The first options don't have a number. "Numbered" options begin at  *
    *   2. You are allowed to save new options at 1 past the current number of options. You *
    *   delete options by decrementing the number in the admin2 options (the index). If you *
    *   re-increment the options, you will see the old values. It is possible to reset to   *
    *   default, and you do that by specifying an option number less than 0 (-1).           *
    *                                                                                       *
    *   The reason for this funky, complex game, is so we can have multiple options, and we *
    *   don't ignore old options from previous versions.                                    *
    *                                                                                       *
    *   I considered setting up an abstracted, object-based system for managing these, but  *
    *   it's complex enough without the added overhead, and, besides, that would give a lot *
    *   more room for bugs. It's kinda hairy already, and the complexity is not great       *
    *   enough to justify designing a whole object subsystem for it.                        *
    ****************************************************************************************/
        
    /************************************************************************************//**
    *   \brief This gets the default admin options from the object (not the DB).            *
    *                                                                                       *
    *   \returns an associative array, with the default option settings.                    *
    ****************************************************************************************/
    protected function geDefaultBMLTOptions ()
        {
        // These are the defaults. If the saved option has a different value, it replaces the ones in here.
        return array (  'root_server' => self::$default_rootserver,
                        'map_center_latitude' => self::$default_map_center_latitude,
                        'map_center_longitude' => self::$default_map_center_longitude,
                        'map_zoom' => self::$default_map_zoom,
                        'bmlt_new_search_url' => self::$default_new_search,
                        'bmlt_initial_view' => self::$default_initial_view,
                        'additional_css' => self::$default_additional_css,
                        'id' => strval ( time() + intval(rand(0, 999))),   // This gives the option a unique slug
                        'setting_name' => '',
                        'bmlt_location_checked'=> self::$default_location_checked,
                        'bmlt_location_services' => self::$default_location_services,
                        'theme' => self::$default_theme,
                        'distance_units' => self::$default_distance_units,
                        'grace_period' => self::$default_grace_period,
                        'default_duration' => self::$default_duration,
                        'time_offset' => self::$default_time_offset
                        );
        }
    
    /************************************************************************************//**
    *   \brief This gets the admin options from the database.                               *
    *                                                                                       *
    *   \returns an associative array, with the option settings.                            *
    ****************************************************************************************/
    function getBMLTOptions ( $in_option_number = null  /**<    It is possible to store multiple options.
                                                                If there is a number here (>1), that will be used.
                                                                If <0, a new option will be returned (not saved).
                                                        */
                            )
        {
        $BMLTOptions = $this->geDefaultBMLTOptions(); // Start off with the defaults.
        
        // Make sure we aren't resetting to default.
        if ( ($in_option_number == null) || (intval ( $in_option_number ) > 0) )
            {
            $option_number = null;
            // If they want a certain option number, then it needs to be greater than 1, and within the number we have assigned.
            if ( (intval ( $in_option_number ) > 1) && (intval ( $in_option_number ) <= $this->get_num_options ( ) ) )
                {
                $option_number = '_'.intval( $in_option_number );
                }
        
            // These are the standard options.
            $old_BMLTOptions = $this->cms_get_option ( self::$adminOptionsName.$option_number );
            
            if ( is_array ( $old_BMLTOptions ) && count ( $old_BMLTOptions ) )
                {
                foreach ( $old_BMLTOptions as $key => $value )
                    {
                    if ( isset ( $BMLTOptions[$key] ) ) // We deliberately ignore old settings that no longer apply.
                        {
                        $BMLTOptions[$key] = $value;
                        }
                    }
                }
        
            // Strip off the trailing slash.
            $BMLTOptions['root_server'] = preg_replace ( "#\/$#", "", trim($BMLTOptions['root_server']), 1 );
            }
        
        return $BMLTOptions;
        }
    
    /************************************************************************************//**
    *   \brief This gets the admin options from the database, but by using the option id.   *
    *                                                                                       *
    *   \returns an associative array, with the option settings.                            *
    ****************************************************************************************/
    function getBMLTOptions_by_id ( $in_option_id,              ///< The option ID. It cannot be optional.
                                    &$out_option_number = null  ///< This can be optional. A reference to an integer that will be given the option number.
                                    )
        {
        $BMLTOptions = null;
        
        if ( isset ( $out_option_number ) )
            {
            $out_option_number = 0;
            }
        
        if ( !$in_option_id )
            {
            $BMLTOptions = $this->getBMLTOptions ( 1 );
            
            if ( isset ( $out_option_number ) )
                {
                $out_option_number = 1;
                }
            }
        else
            {
            $count = $this->get_num_options ( );
            
            // We sort through the available options, looking for the ID.
            for ( $i = 1; $i <= $count; $i++ )
                {
                $option_number = '';
                
                if ( $i > 1 )   // We do this, for compatibility with older options.
                    {
                    $option_number = "_$i";
                    }
                
                $name = self::$adminOptionsName.$option_number;
                $temp_BMLTOptions = $this->cms_get_option ( $name );
                
                if ( is_array ( $temp_BMLTOptions ) && count ( $temp_BMLTOptions ) )
                    {
                    if ( $temp_BMLTOptions['id'] == $in_option_id )
                        {
                        $BMLTOptions = $temp_BMLTOptions;
                        // If they want to know the ID, we supply it here.
                        if ( isset ( $out_option_number ) )
                            {
                            $out_option_number = $i;
                            }
                        break;
                        }
                    }
                else
                    {
                    echo "<!-- BMLTPlugin ERROR (getBMLTOptions_by_id)! No options found for $name! -->";
                    }
                }
            }
        
        return $BMLTOptions;
        }
    
    /************************************************************************************//**
    *   \brief This updates the database with the given options.                            *
    *                                                                                       *
    *   \returns a boolean. true if success.                                                *
    ****************************************************************************************/
    function setBMLTOptions (   $in_options,            ///< An array. The options to be stored. If no number is supplied in the next parameter, the ID is used.
                                $in_option_number = 1   ///< It is possible to store multiple options. If there is a number here, that will be used.
                            )
        {
        $ret = false;
        
        if ( ($in_option_number == null) || (intval($in_option_number) < 1) || (intval($in_option_number) > ($this->get_num_options ( ) + 1)) )
            {
            $in_option_number = 0;
            $this->getBMLTOptions_by_id ( $in_options['id'], $in_option_number );
            }
        
        if ( intval ( $in_option_number ) > 0 )
            {
            $option_number = null;
            // If they want a certain option number, then it needs to be greater than 1, and within the number we have assigned (We can also increase by 1).
            if ( (intval ( $in_option_number ) > 1) && (intval ( $in_option_number ) <= ($this->get_num_options ( ) + 1)) )
                {
                $option_number = '_'.intval( $in_option_number );
                }
            $in_option_number = (intval ( $in_option_number ) > 1) ? intval ( $in_option_number ) : 1;

            $name = self::$adminOptionsName.$option_number;
            
            // If this is a new option, then we also update the admin 2 options, incrementing the number of servers.
            if ( intval ( $in_option_number ) == ($this->get_num_options ( ) + 1) )
                {
                $in_options['id'] = strval ( time() + intval(rand(0, 999)));   // This gives the option a unique slug
                $admin2Options = array ('num_servers' => intval( $in_option_number ));

                $this->setAdmin2Options ( $admin2Options );
                }
            
            $this->cms_set_option ( $name, $in_options );
            
            $ret = true;
            }
        else
            {
            echo "<!-- BMLTPlugin ERROR (setBMLTOptions)! The option number ($in_option_number) is out of range! -->";
            }
            
        return $ret;
        }
        
    /************************************************************************************//**

    *   \brief This gets the admin 2 options from the database.                             *
    *                                                                                       *
    *   \returns an associative array, with the option settings.                            *
    ****************************************************************************************/
    function getAdmin2Options ( )
        {
        $bmlt2_BMLTOptions = null;
        
        // We have a special set of options for version 2.
        $old_BMLTOptions = $this->cms_get_option ( self::$admin2OptionsName );
        
        if ( is_array ( $old_BMLTOptions ) && count ( $old_BMLTOptions ) )
            {
            foreach ( $old_BMLTOptions as $key => $value )
                {
                $bmlt2_BMLTOptions[$key] = $value;
                }
            }
        else
            {
            $bmlt2_BMLTOptions = array ('num_servers' => 1 );
            $this->setAdmin2Options ( $old_BMLTOptions );
            }
        
        return $bmlt2_BMLTOptions;
        }
    
    /************************************************************************************//**
    *   \brief This updates the database with the given options (Admin2 options).           *
    *                                                                                       *
    *   \returns a boolean. true if success.                                                *
    ****************************************************************************************/
    function setAdmin2Options ( $in_options ///< An array. The options to be stored.
                                )
        {
        $ret = false;
        
        if ( $this->cms_set_option ( self::$admin2OptionsName, $in_options ) )
            {
            $ret = true;
            }
        
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Gets the number of active options.                                           *
    *                                                                                       *
    *   \returns an integer. The number of options.                                         *
    ****************************************************************************************/
    function get_num_options ( )
        {
        $ret = 1;
        $opts = $this->getAdmin2Options();
        if ( isset ( $opts['num_servers'] ) )
            {
            $ret = intval ( $opts['num_servers'] );
            }
        else    // If the options weren't already set, we create them now.
            {
            $opts = array ( 'num_servers' => 1 );
            $this->setAdmin2Options ( $opts );
            }
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Makes a new set of options, set as default.                                  *
    *                                                                                       *
    *   \returns An integer. The index of the options (It will always be the number of      *
    *   initial options, plus 1. Null if failed.                                            *
    ****************************************************************************************/
    function make_new_options ( )
        {
        $opt = $this->getBMLTOptions ( -1 );
        $ret = null;
        
        // If we successfully get the options, we save them, in order to put them in place
        if ( is_array ( $opt ) && count ( $opt ) )
            {
            $this->setBMLTOptions ( $BMLTOptions, $this->get_num_options ( ) + 1 );
            $ret = $this->get_num_options ( );
            }
        else
            {
            echo "<!-- BMLTPlugin ERROR (make_new_options)! Failed to create new options! -->";
            }
        
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Deletes the options by ID.                                                   *
    *                                                                                       *
    *   \returns a boolean. true if success.                                                *
    ****************************************************************************************/
    function delete_options_by_id ( $in_option_id   ///< The ID of the option to delete.
                                    )
        {
        $ret = false;
        
        $option_num = 0;
        $this->getBMLTOptions_by_id ( $in_option_id, $option_num ); // We just want the option number.
        
        if ( $option_num > 0 )  // If it's 1, we'll let the next function register the error.
            {
            $ret = $this->delete_options ( $option_num );
            }
        
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Deletes the indexed options.                                                 *
    *                                                                                       *
    *   This is a bit of a delicate operation, because we need to re-index all of the other *
    *   options, beyond the one being deleted.                                              *
    *                                                                                       *
    *   You cannot delete the first options (1), if they are the only ones.                 *
    *                                                                                       *
    *   \returns a boolean. true if success.                                                *
    ****************************************************************************************/
    function delete_options ( $in_option_number /**<    The index of the option to delete.
                                                        It can be 1 -> the number of available options.
                                                        For safety's sake, this cannot be optional.
                                                        We cannot delete the first (primary) option if there are no others.
                                                */
                            )
        {
        $first_num = intval ( $in_option_number );

        $ret = false;
        
        if ( $first_num )
            {
            $last_num = $this->get_num_options ( );
            
            if ( (($first_num > 1) && ($first_num <= $last_num )) || (($first_num == 1) && ($last_num > 1)) )
                {
                /*
                    OK. At this point, we know which option we'll be deleting. The way we "delete"
                    the option is to cascade all the ones after it down, and then we delete the last one.
                    If this is the last one, then there's no need for a cascade, and we simply delete it.
                */
                
                for ( $i = $first_num; $i < $last_num; $i++ )
                    {
                    $opt = $this->getBMLTOptions ( $i + 1 );
                    $this->setBMLTOptions ( $opt, $i );
                    }
                
                $option_number = "_$last_num";
                
                // Delete the selected option
                $option_name = self::$adminOptionsName.$option_number;
                
                $this->cms_delete_option ( $option_name );
                
                // This actually decrements the number of available options.
                $admin2Options = array ('num_servers' => $last_num - 1);

                $this->setAdmin2Options ( $admin2Options );
                $ret = true;
                }
            else
                {
                if ( $first_num > 1 )
                    {
                    echo "<!-- BMLTPlugin ERROR (delete_options)! Option request number out of range! It must be between 1 and $last_num -->";
                    }
                elseif ( $first_num == 1 )
                    {
                    echo "<!-- BMLTPlugin ERROR (delete_options)! You can't delete the last option! -->";
                    }
                else
                    {
                    echo "<!-- BMLTPlugin ERROR (delete_options)! -->";
                    }
                }
            }
        else
            {
            echo "<!-- BMLTPlugin ERROR (delete_options)! Option request number ($first_num) out of range! -->";
            }
        
        return $ret;
        }
    
    /************************************************************************************//**
    *                      ADMIN PAGE DISPLAY AND PROCESSING FUNCTIONS                      *
    ****************************************************************************************/

    /************************************************************************************//**
    *   \brief This does any admin actions necessary.                                       *
    ****************************************************************************************/
    function process_admin_page ( &$out_option_number   ///< If an option number needs to be selected, it is set here.
                                )
        {
        $out_option_number = 1;
        
        $timing = self::$local_options_success_time;    // Success is a shorter fade, but failure is longer.
        $ret = '<div id="BMLTPlugin_Message_bar_div" class="BMLTPlugin_Message_bar_div">';
            if ( isset ( $this->my_http_vars['BMLTPlugin_create_option'] ) )
                {
                $out_option_number = $this->make_new_options ( );
                if ( $out_option_number )
                    {
                    $new_options = $this->getBMLTOptions ( $out_option_number );
                    $def_options = $this->getBMLTOptions ( 1 );
                    
                    $new_options = $def_options;
                    unset ( $new_options['setting_name'] );
                    unset ( $new_options['id'] );
                    unset ( $new_options['theme'] );
                    $this->setBMLTOptions ( $new_options, $out_option_number );
                    
                    $ret .= '<h2 id="BMLTPlugin_Fader" class="BMLTPlugin_Message_bar_success">';
                        $ret .= $this->process_text ( self::$local_options_create_success );
                    $ret .= '</h2>';
                    }
                else
                    {
                    $timing = self::$local_options_failure_time;
                    $ret .= '<h2 id="BMLTPlugin_Fader" class="BMLTPlugin_Message_bar_fail">';
                        $ret .= $this->process_text ( self::$local_options_create_failure );
                    $ret .= '</h2>';
                    }
                }
            elseif ( isset ( $this->my_http_vars['BMLTPlugin_delete_option'] ) )
                {
                $option_index = intval ( $this->my_http_vars['BMLTPlugin_delete_option'] );
        
                if ( $this->delete_options ( $option_index ) )
                    {
                    $ret .= '<h2 id="BMLTPlugin_Fader" class="BMLTPlugin_Message_bar_success">';
                        $ret .= $this->process_text ( self::$local_options_delete_success );
                    $ret .= '</h2>';
                    }
                else
                    {
                    $timing = self::$local_options_failure_time;
                    $ret .= '<h2 id="BMLTPlugin_Fader" class="BMLTPlugin_Message_bar_fail">';
                        $ret .= $this->process_text ( self::$local_options_delete_failure );
                    $ret .= '</h2>';
                    }
                }
            else
                {
                $ret .= '<h2 id="BMLTPlugin_Fader" class="BMLTPlugin_Message_bar_fail">&nbsp;</h2>';
                }
            $ret .= '<script type="text/javascript">g_BMLTPlugin_TimeToFade = '.$timing.';BMLTPlugin_StartFader()</script>';
        $ret .= '</div>';
        return $ret;
        }
        
    /************************************************************************************//**
    *   \brief Returns the HTML for the admin page.                                         *
    *                                                                                       *
    *   \returns a string. The XHTML for the page.                                          *
    ****************************************************************************************/
    function return_admin_page ( )
        {
        $selected_option = 1;
        $process_html = $this->process_admin_page($selected_option);
        $options_coords = array();

        $html = '<div class="BMLTPlugin_option_page" id="BMLTPlugin_option_page_div">';
            $html .= '<noscript class="no_js">'.$this->process_text ( self::$local_noscript ).'</noscript>';
            $html .= '<div id="BMLTPlugin_options_container" style="display:none">';    // This is displayed using JavaScript.
                $html .= '<h1 class="BMLTPlugin_Admin_h1">'.$this->process_text ( self::$local_options_title ).'</h1>';
                $html .= $process_html;
                $html .= '<form class="BMLTPlugin_sheet_form" id="BMLTPlugin_sheet_form" action ="'.$this->get_admin_form_uri().'" method="get" onsubmit="function(){return false}">';
                    $html .= '<fieldset class="BMLTPlugin_option_fieldset" id="BMLTPlugin_option_fieldset">';
                        $html .= '<legend id="BMLTPlugin_legend" class="BMLTPlugin_legend">';
                            $count = $this->get_num_options();
                                
                            if ( $count > 1 )
                                {
                                $html .= '<select id="BMLTPlugin_legend_select" onchange="BMLTPlugin_SelectOptionSheet(this.value,'.$count.')">';
                                    for ( $i = 1; $i <= $count; $i++ )
                                        {
                                        $options = $this->getBMLTOptions ( $i );
                                        
                                        if ( is_array ( $options ) && count ( $options ) && isset ( $options['id'] ) )
                                            {
                                            $options_coords[$i] = array ( 'lat' => $options['map_center_latitude'], 'lng' => $options['map_center_longitude'], 'zoom' => $options['map_zoom'] );
                                            
                                            $html .= '<option id="BMLTPlugin_option_sel_'.$i.'" value="'.$i.'"';
                                            
                                            if ( $i == $selected_option )
                                                {
                                                $html .= ' selected="selected"';
                                                }
                                            
                                            $html .= '>';
                                                if ( isset ( $options['setting_name'] ) && $options['setting_name'] )
                                                    {
                                                    $html .= htmlspecialchars ( $options['setting_name'] );
                                                    }
                                                else
                                                    {
                                                    $html .= $this->process_text ( self::$local_options_prefix ).$i;
                                                    }
                                            $html .= '</option>';
                                            }
                                        else
                                            {
                                            echo "<!-- BMLTPlugin ERROR (admin_page)! Options not found for $i! -->";
                                            }
                                        }
                                $html .= '</select>';
                                }
                            elseif ( $count == 1 )
                                {
                                $options = $this->getBMLTOptions ( 1 );
                                $options_coords[1] = array ( 'lat' => $options['map_center_latitude'], 'lng' => $options['map_center_longitude'], 'zoom' => $options['map_zoom'] );
                                if ( isset ( $options['setting_name'] ) && $options['setting_name'] )
                                    {
                                    $html .= htmlspecialchars ( $options['setting_name'] );
                                    }
                                else
                                    {
                                    $html .= $this->process_text ( self::$local_options_prefix ).'1';
                                    }
                                }
                            else
                                {
                                echo "<!-- BMLTPlugin ERROR (admin_page)! No options! -->";
                                }
                        $html .= '</legend>';
                        for ( $i = 1; $i <= $count; $i++ )
                            {
                            $html .= $this->display_options_sheet ( $i, (($i == $selected_option) ? 'block' : 'none') );
                            }
                    $html .= '</fieldset>';
                $html .= '</form>';
                $html .= '<div class="BMLTPlugin_toolbar_line_bottom">';
                    $html .= '<form action ="'.$this->get_admin_form_uri().'" method="post">';
                        $html .= '<div id="BMLTPlugin_bottom_button_div" class="BMLTPlugin_bottom_button_div">';
                            if ( $count > 1 )
                                {
                                $html .= '<div class="BMLTPlugin_toolbar_button_line_left">';
                                    $html .= '<script type="text/javascript">';
                                        $html .= "var c_g_delete_confirm_message='".$this->process_text ( self::$local_options_delete_option_confirm )."';";
                                    $html .= '</script>';
                                    $html .= '<input type="button" id="BMLTPlugin_toolbar_button_del" class="BMLTPlugin_delete_button" value="'.$this->process_text ( self::$local_options_delete_option ).'" onclick="BMLTPlugin_DeleteOptionSheet()" />';
                                $html .= '</div>';
                                }
                            
                            $html .= '<input type="submit" id="BMLTPlugin_toolbar_button_new" class="BMLTPlugin_create_button" name="BMLTPlugin_create_option" value="'.$this->process_text ( self::$local_options_add_new ).'" />';
                            
                            $html .= '<div class="BMLTPlugin_toolbar_button_line_right">';
                                $html .= '<input id="BMLTPlugin_toolbar_button_save" type="button" value="'.$this->process_text ( self::$local_options_save ).'" onclick="BMLTPlugin_SaveOptions()" />';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</form>';
                $html .= '</div>';
                $html .= '<div class="BMLTPlugin_toolbar_line_map">';
                    $html .= '<h2 class="BMLTPlugin_map_label_h2">'.$this->process_text ( self::$local_options_map_label ).'</h2>';
                    $html .= '<div class="BMLTPlugin_Map_Div" id="BMLTPlugin_Map_Div"></div>';
                    $html .= '<script type="text/javascript">' . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "BMLTPlugin_DirtifyOptionSheet(true);" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');    // This sets up the "Save Changes" button as disabled.
                        // This is a trick I use to hide irrelevant content from non-JS browsers. The element is drawn, hidden, then uses JS to show. No JS, no element.
                        $html .= "document.getElementById('BMLTPlugin_options_container').style.display='block';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_no_name = '".$this->process_text ( self::$local_options_no_name_string )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_no_root = '".$this->process_text ( self::$local_options_no_root_server_string )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_no_search = '".$this->process_text ( self::$local_options_no_new_search_string )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_root_canal = '".self::$local_options_url_bad."';";
                        $html .= "var c_g_BMLTPlugin_success_message = '".$this->process_text ( self::$local_options_save_success )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_failure_message = '".$this->process_text ( self::$local_options_save_failure )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_success_time = ".intval ( self::$local_options_success_time ).";" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_failure_time = ".intval ( self::$local_options_failure_time ).";" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_unsaved_prompt = '".$this->process_text ( self::$local_options_unsaved_message )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_test_server_success = '".$this->process_text ( self::$local_options_test_server_success )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_test_server_failure = '".$this->process_text ( self::$local_options_test_server_failure )."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var c_g_BMLTPlugin_coords = new Array();" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var g_BMLTPlugin_TimeToFade = ".intval ( self::$local_options_success_time ).";" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= "var g_BMLTPlugin_no_gkey_string = '".$this->process_text ( self::$local_options_no_gkey_string)."';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        if ( is_array ( $options_coords ) && count ( $options_coords ) )
                            {
                            foreach ( $options_coords as $value )
                                {
                                $html .= 'c_g_BMLTPlugin_coords[c_g_BMLTPlugin_coords.length] = {';
                                $f = true;
                                foreach ( $value as $key2 => $value2 )
                                    {
                                    if ( $f )
                                        {
                                        $f = false;
                                        }
                                    else
                                        {
                                        $html .= ',';
                                        }
                                    $html .= "'".htmlspecialchars ( $key2 )."':";
                                    $html .= "'".htmlspecialchars ( $value2 )."'";
                                    }
                                $html .= '};';
                                }
                            }
                        $url = $this->get_plugin_path();
                        $url = htmlspecialchars ( $url.'google_map_images' );
                        $html .= "var c_g_BMLTPlugin_admin_google_map_images = '$url';" . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                        $html .= 'BMLTPlugin_admin_load_map();' . (defined ( '_DEBUG_MODE_' ) ? "\n" : '');
                    $html .= '</script>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
        
        return $html;
        }
            
    /************************************************************************************//**
    *   \brief This will return the HTML for one sheet of options in the admin page.        *
    *                                                                                       *
    *   \returns The XHTML to be displayed.                                                 *
    ****************************************************************************************/
    function display_options_sheet ($in_options_index = 1,  ///< The options index. If not given, the first (main) ones are used.
                                    $display_mode = 'none'  ///< If this page is to be displayed, make it 'block'.
                                    )
        {
        $ret = '';

        $in_options_index = intval ( $in_options_index );
        
        if ( ($in_options_index < 1) || ($in_options_index > $this->get_num_options()) )
            {
            echo "<!-- BMLTPlugin Warning (display_options_sheet)! $in_options_index is out of range! Using the first options. -->";
            $in_options_index = 1;
            }
        
        $options = $this->getBMLTOptions ( $in_options_index );
        
        if ( is_array ( $options ) && count ( $options ) && isset ( $options['id'] ) )
            {
            $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'javascript.js" type="text/javascript"></script>';
            $ret .= '<div class="BMLTPlugin_option_sheet" id="BMLTPlugin_option_sheet_'.$in_options_index.'_div" style="display:'.htmlspecialchars ( $display_mode ).'">';
                $ret .= '<h2 class="BMLTPlugin_option_id_h2">'.$this->process_text ( self::$local_options_settings_id_prompt ).htmlspecialchars ( $options['id'] ).'</h2>';
                $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                    $id = 'BMLTPlugin_option_sheet_name_'.$in_options_index;
                    $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_name_label ).'</label>';
                        $string = (isset ( $options['setting_name'] ) && $options['setting_name'] ? $options['setting_name'] : $this->process_text ( self::$local_options_no_name_string ) );
                    $ret .= '<input class="BMLTPlugin_option_sheet_line_name_text" id="'.htmlspecialchars ( $id ).'" type="text" value="'.htmlspecialchars ( $string ).'"';
                    $ret .= ' onfocus="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_name_string ).'\',false)"';
                    $ret .= ' onblur="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_name_string ).'\',true)"';
                    $ret .= ' onchange="BMLTPlugin_DirtifyOptionSheet()" onkeyup="BMLTPlugin_DirtifyOptionSheet()" />';
                $ret .= '</div>';
                $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                    $id = 'BMLTPlugin_option_sheet_root_server_'.$in_options_index;
                    $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_rootserver_label ).'</label>';
                        $string = (isset ( $options['root_server'] ) && $options['root_server'] ? $options['root_server'] : $this->process_text ( self::$local_options_no_root_server_string ) );
                    $ret .= '<input class="BMLTPlugin_option_sheet_line_root_server_text" id="'.htmlspecialchars ( $id ).'" type="text" value="'.htmlspecialchars ( $string ).'"';
                    $ret .= ' onfocus="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_root_server_string).'\',false)"';
                    $ret .= ' onblur="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_root_server_string).'\',true)"';
                    $ret .= ' onchange="BMLTPlugin_DirtifyOptionSheet()" onkeyup="BMLTPlugin_DirtifyOptionSheet()" />';
                    $ret .= '<div class="BMLTPlugin_option_sheet_Test_Button_div">';
                        $ret .= '<input type="button" value="'.$this->process_text ( self::$local_options_test_server ).'" onclick="BMLTPlugin_TestRootUri_call()" title="'.$this->process_text ( self::$local_options_test_server_tooltip ).'" />';
                        $ret .= '<div class="BMLTPlugin_option_sheet_NEUT" id="BMLTPlugin_option_sheet_indicator_'.$in_options_index.'"></div>';
                        $ret .= '<div class="BMLTPlugin_option_sheet_Version" id="BMLTPlugin_option_sheet_version_indicator_'.$in_options_index.'"></div>';
                    $ret .= '</div>';
                $ret .= '</div>';
                $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                    $id = 'BMLTPlugin_option_sheet_new_search_'.$in_options_index;
                    $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_new_search_label ).'</label>';
                        $string = (isset ( $options['bmlt_new_search_url'] ) && $options['bmlt_new_search_url'] ? $options['bmlt_new_search_url'] : $this->process_text ( self::$local_options_no_new_search_string ) );
                    $ret .= '<input class="BMLTPlugin_option_sheet_line_new_search_text" id="'.htmlspecialchars ( $id ).'" type="text" value="'.htmlspecialchars ( $string ).'"';
                    $ret .= ' onfocus="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_new_search_string).'\',false)"';
                    $ret .= ' onblur="BMLTPlugin_ClickInText(this.id,\''.$this->process_text ( self::$local_options_no_new_search_string).'\',true)"';
                    $ret .= ' onchange="BMLTPlugin_DirtifyOptionSheet()" onkeyup="BMLTPlugin_DirtifyOptionSheet()" />';
                $ret .= '</div>';
                $dir_res = opendir ( dirname ( __FILE__ ).'/themes' );
                if ( $dir_res )
                    {
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                        $id = 'BMLTPlugin_option_sheet_theme_'.$in_options_index;
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_theme_prompt ).'</label>';
                        $ret .= '<select id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            while ( false !== ( $file_name = readdir ( $dir_res ) ) )
                                {
                                if ( !preg_match ( '/^\./', $file_name ) && is_dir ( dirname ( __FILE__ ).'/themes/'.$file_name ) )
                                    {
                                    $ret .= '<option value="'.htmlspecialchars ( $file_name ).'"';
                                    if ( $file_name == $options['theme'] )
                                        {
                                        $ret .= ' selected="selected"';
                                        }
                                    $ret .= '>'.htmlspecialchars ( $file_name ).'</option>';
                                    }
                                }
                        $ret .= '</select>';
                    $ret .= '</div>';
                    }
                $ret .= '<div class="BMLTPlugin_option_sheet_line_div BMLTPlugin_additional_css_line">';
                    $id = 'BMLTPlugin_option_sheet_additional_css_'.$in_options_index;
                    $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_more_styles_label ).'</label>';
                    $ret .= '<textarea class="BMLTPlugin_option_sheet_additional_css_textarea" id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                    $ret .= htmlspecialchars ( $options['additional_css'] );
                    $ret .= '</textarea>';
                $ret .= '</div>';
                $ret .= '<fieldset class="BMLTPlugin_option_sheet_mobile_settings_fieldset">';
                    $ret .= '<legend class="BMLTPlugin_gmap_caveat_legend">'.$this->process_text ( self::$local_options_mobile_legend ).'</legend>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                        $id = 'BMLTPlugin_option_sheet_distance_units_'.$in_options_index;
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_distance_prompt ).'</label>';
                        $ret .= '<select id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            $ret .= '<option value="mi"';
                            if ( 'mi' == $options['distance_units'] )
                                {
                                $ret .= ' selected="selected"';
                                }
                            $ret .= '>'.$this->process_text ( self::$local_options_miles ).'</option>';
                            $ret .= '<option value="km"';
                            if ( 'km' == $options['distance_units'] )
                                {
                                $ret .= ' selected="selected"';
                                }
                            $ret .= '>'.$this->process_text ( self::$local_options_kilometers ).'</option>';
                        $ret .= '</select>';
                    $ret .= '</div>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                        $id = 'BMLTPlugin_option_sheet_initial_view_'.$in_options_index;
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_initial_view_prompt ).'</label>';
                        $ret .= '<select id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            foreach ( self::$local_options_initial_view as $value => $prompt )
                                {
                                $ret .= '<option value="'.htmlspecialchars ( $value ).'"';
                                if ( $value == $options['bmlt_initial_view'] )
                                    {
                                    $ret .= ' selected="selected"';
                                    }
                                $ret .= '>'.$this->process_text ( $prompt ).'</option>';
                                }
                        $ret .= '</select>';
                    $ret .= '</div>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div BMLTPlugin_location_checkbox_line">';
                        $id = 'BMLTPlugin_location_selected_checkbox_'.$in_options_index;
                        $ret .= '<div class="BMLTPlugin_option_sheet_checkbox_div"><input class="BMLTPlugin_option_sheet_line_location_checkbox" onchange="BMLTPlugin_DirtifyOptionSheet()" id="'.htmlspecialchars ( $id ).'" type="checkbox"'.($options['bmlt_location_checked'] == 1 ? ' checked="checked"' : '' ).'"></div>';
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_settings_location_checkbox_label ).'</label>';
                    $ret .= '</div>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div BMLTPlugin_location_checkbox_line">';
                        $id = 'BMLTPlugin_location_services_checkbox_'.$in_options_index;
                        $ret .= '<div class="BMLTPlugin_option_sheet_checkbox_div"><input class="BMLTPlugin_option_sheet_line_location_services_checkbox" onchange="BMLTPlugin_DirtifyOptionSheet()" id="'.htmlspecialchars ( $id ).'" type="checkbox"'.($options['bmlt_location_services'] == 1 ? ' checked="checked"' : '' ).'"></div>';
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_selectLocation_checkbox_text ).'</label>';
                    $ret .= '</div>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                        $id = 'BMLTPlugin_option_sheet_grace_period_'.$in_options_index;
                        $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_mobile_grace_period_label ).'</label>';
                        $ret .= '<select id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            for ( $minute = 0; $minute < 60; $minute += 5 )
                                {
                                $ret .= '<option value="'.$minute.'"';
                                if ( $minute == $options['grace_period'] )
                                    {
                                    $ret .= ' selected="selected"';
                                    }
                                $ret .= '>'.$minute.'</option>';
                                }
                        $ret .= '</select>';
                        $ret .= '<div class="BMLTPlugin_option_sheet_text_div">'.$this->process_text ( self::$local_options_grace_period_disclaimer ).'</div>';
                    $ret .= '</div>';
                    $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
                        $id = 'BMLTPlugin_option_sheet_duration';
                        $ret .= '<label for="'.htmlspecialchars ( $id.'_hour_'.$in_options_index ).'">'.$this->process_text ( self::$local_options_mobile_default_duration_label ).'</label>';
                        $ret .= '<select id="'.htmlspecialchars ( $id.'_hour_'.$in_options_index ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            $def = explode ( ':', $options['default_duration'] );
                            $def[0] = intval ( $def[0] );
                            $def[1] = intval ( $def[1] );
                            for ( $hour = 0; $hour < 3; $hour++ )
                                {
                                $ret .= '<option value="'.$hour.'"';
                                if ( intval ( $hour ) == $def[0] )
                                    {
                                    $ret .= ' selected="selected"';
                                    }
                                $ret .= '>'.$hour.'</option>';
                                }
                        $ret .= '</select>';
                        $ret .= '<select id="'.htmlspecialchars ( $id.'_minute_'.$in_options_index ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
                            for ( $minute = 0; $minute < 60; $minute += 5 )
                                {
                                $ret .= '<option value="'.$minute.'"';
                                if ( intval ( $minute ) == $def[1] )
                                    {
                                    $ret .= ' selected="selected"';
                                    }
                                $ret .= '>'.$minute.'</option>';
                                }
                        $ret .= '</select>';
                    $ret .= '</div>';
//                     $ret .= '<div class="BMLTPlugin_option_sheet_line_div">';
//                         $id = 'BMLTPlugin_option_sheet_time_offset_'.$in_options_index;
//                         $ret .= '<label for="'.htmlspecialchars ( $id ).'">'.$this->process_text ( self::$local_options_mobile_time_offset_label ).'</label>';
//                         $ret .= '<select id="'.htmlspecialchars ( $id ).'" onchange="BMLTPlugin_DirtifyOptionSheet()">';
//                             for ( $hour = -23; $hour < 24; $hour++ )
//                                 {
//                                 $ret .= '<option value="'.$hour.'"';
//                                 if ( $hour == $options['time_offset'] )
//                                     {
//                                     $ret .= ' selected="selected"';
//                                     }
//                                 $ret .= '>'.$hour.'</option>';
//                                 }
//                         $ret .= '</select>';
//                         $ret .= '<div class="BMLTPlugin_option_sheet_text_div">'.$this->process_text ( self::$local_options_time_offset_disclaimer ).'</div>';
//                     $ret .= '</div>';
                $ret .= '</fieldset>';
            $ret .= '</div>';
            }
        else
            {
            echo "<!-- BMLTPlugin ERROR (display_options_sheet)! Options not found for $in_options_index! -->";
            }
        
        return $ret;
        }
        
    /************************************************************************************//**
    *                                   GENERIC HANDLERS                                    *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *   \brief This does any admin actions necessary.                                       *
    ****************************************************************************************/
    function admin_ajax_handler ( )
        {
        // We only go here if we are in an AJAX call (This function dies out the session).
        if ( isset ( $this->my_http_vars['BMLTPlugin_Save_Settings_AJAX_Call'] ) )
            {
            $ret = 0;
            
            if ( isset ( $this->my_http_vars['BMLTPlugin_set_options'] ) )
                {
                $ret = 1;
                
                $num_options = $this->get_num_options();
                
                for ( $i = 1; $i <= $num_options; $i++ )
                    {
                    $options = $this->getBMLTOptions ( $i );
                    
                    if ( is_array ( $options ) && count ( $options ) )
                        {
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_name_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_name_'.$i] ) )
                                {
                                $options['setting_name'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_name_'.$i] );
                                }
                            else
                                {
                                $options['setting_name'] = '';
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_root_server_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_root_server_'.$i] ) )
                                {
                                $options['root_server'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_root_server_'.$i] );
                                }
                            else
                                {
                                $options['root_server'] = self::$default_rootserver;
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_new_search_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_new_search_'.$i] ) )
                                {
                                $options['bmlt_new_search_url'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_new_search_'.$i] );
                                }
                            else
                                {
                                $options['bmlt_new_search_url'] = self::$default_new_search;
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_initial_view_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_initial_view_'.$i] ) )
                                {
                                $options['bmlt_initial_view'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_initial_view_'.$i] );
                                }
                            else
                                {
                                $options['bmlt_initial_view'] = self::$default_initial_view;
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_theme_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_theme_'.$i] ) )
                                {
                                $options['theme'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_theme_'.$i] );
                                }
                            else
                                {
                                $options['theme'] = self::$default_theme;
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_additional_css_'.$i] ) )
                            {
                            if ( trim ( $this->my_http_vars['BMLTPlugin_option_sheet_additional_css_'.$i] ) )
                                {
                                $options['additional_css'] = trim ( $this->my_http_vars['BMLTPlugin_option_sheet_additional_css_'.$i] );
                                }
                            else
                                {
                                $options['additional_css'] = self::$default_additional_css;
                                }
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_latitude_'.$i] ) && floatVal ( $this->my_http_vars['BMLTPlugin_option_latitude_'.$i] ) )
                            {
                            $options['map_center_latitude'] = floatVal ( $this->my_http_vars['BMLTPlugin_option_latitude_'.$i] );
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_longitude_'.$i] ) && floatVal ( $this->my_http_vars['BMLTPlugin_option_longitude_'.$i] ) )
                            {
                            $options['map_center_longitude'] = floatVal ( $this->my_http_vars['BMLTPlugin_option_longitude_'.$i] );
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_zoom_'.$i] ) && intval ( $this->my_http_vars['BMLTPlugin_option_zoom_'.$i] ) )
                            {
                            $options['map_zoom'] = floatVal ( $this->my_http_vars['BMLTPlugin_option_zoom_'.$i] );
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_distance_units_'.$i] ) )
                            {
                            $options['distance_units'] = $this->my_http_vars['BMLTPlugin_option_sheet_distance_units_'.$i];
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_duration_hour_'.$i] ) && isset ( $this->my_http_vars['BMLTPlugin_option_sheet_duration_minute_'.$i] ) )
                            {
                            $options['default_duration'] = sprintf ( '%d:%02d', intval ( $this->my_http_vars['BMLTPlugin_option_sheet_duration_hour_'.$i] ), intval ( $this->my_http_vars['BMLTPlugin_option_sheet_duration_minute_'.$i] ) );
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_grace_period_'.$i] ) )
                            {
                            $options['grace_period'] = $this->my_http_vars['BMLTPlugin_option_sheet_grace_period_'.$i];
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_option_sheet_time_offset_'.$i] ) )
                            {
                            $options['time_offset'] = $this->my_http_vars['BMLTPlugin_option_sheet_time_offset_'.$i];
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_location_selected_checkbox_'.$i] ) )
                            {
                            $options['bmlt_location_checked'] = ($this->my_http_vars['BMLTPlugin_location_selected_checkbox_'.$i] != 0 ? 1 : 0);
                            }
                        
                        if ( isset ( $this->my_http_vars['BMLTPlugin_location_services_checkbox_'.$i] ) )
                            {
                            $options['bmlt_location_services'] = ($this->my_http_vars['BMLTPlugin_location_services_checkbox_'.$i] != 0 ? 1 : 0);
                            }
                        
                        if ( !$this->setBMLTOptions ( $options, $i ) )
                            {
                            $ret = 0;
                            break;
                            }
                        }
                    }
                }
            
            if ( ob_get_level () ) ob_end_clean(); // Just in case we are in an OB
            die ( strVal ( $ret ) );
            }
        elseif ( isset ( $this->my_http_vars['BMLTPlugin_AJAX_Call'] ) || isset ( $this->my_http_vars['BMLTPlugin_Fetch_Langs_AJAX_Call'] ) )
            {
            $ret = '';
            if ( isset ( $this->my_http_vars['BMLTPlugin_AJAX_Call_Check_Root_URI'] ) )
                {
                $uri = trim ( $this->my_http_vars['BMLTPlugin_AJAX_Call_Check_Root_URI'] );
                
                $test = new bmlt_satellite_controller ( $uri );
                if ( $uri && ($uri != self::$local_options_no_root_server_string ) && $test instanceof bmlt_satellite_controller )
                    {
                    if ( !$test->get_m_error_message() )
                        {
                        if ( isset ( $this->my_http_vars['BMLTPlugin_AJAX_Call'] ) )
                            {
                            $ret = trim($test->get_server_version());
                            
                            $ret = explode ( ".", $ret );
                            
                            if ( (intval ( $ret[0] ) < 1) || ((intval ( $ret[0] ) == 1) && (intval ( $ret[1] ) < 10))  || ((intval ( $ret[0] ) == 1) && (intval ( $ret[1] ) == 10) && (intval ( $ret[2] ) < 3)) )
                                {
                                $ret = '';
                                }
                            else
                                {
                                $ret = implode ( '.', $ret );
                                }
                            }
                        else
                            {
                            $slangs = $test->get_server_langs();
                            
                            if ( $slangs )
                                {
                                $langs = array();
                                foreach ( $slangs as $key => $value )
                                    {
                                    $langs[] = array ( $key, $value['name'], $value['default'] );
                                    }
                                
                                $ret = array2json ( $langs );
                                }
                            }
                        }
                    }
                }
            
            if ( ob_get_level () ) ob_end_clean(); // Just in case we are in an OB
            die ( $ret );
            }
        }
      
    /************************************************************************************//**
    *   \brief Handles some AJAX routes                                                     *
    *                                                                                       *
    *   This function is called after the page has loaded its custom fields, so we can      *
    *   figure out which settings we're using. If the settings support mobiles, and the UA  *
    *   indicates this is a mobile phone, we redirect the user to our fast mobile handler.  *
    ****************************************************************************************/
    function ajax_router ( )
        {
        // If this is a basic AJAX call, we drop out quickly (We're really just a router).
        if ( isset ( $this->my_http_vars['BMLTPlugin_mobile_ajax_router'] ) )
            {
            $options = $this->getBMLTOptions_by_id ( $this->my_http_vars['bmlt_settings_id'] ); // This is for security. We don't allow URIs to be directly specified. They must come from the settings.
            $uri = $options['root_server'].'/'.$this->my_http_vars['request'];
            if ( ob_get_level () ) ob_end_clean(); // Just in case we are in an OB
            die ( bmlt_satellite_controller::call_curl ( $uri ) );
            }
        else    // However, if it is a mobile call, we do the mobile thing, then drop out.
            {
            if ( isset ( $this->my_http_vars['BMLTPlugin_mobile'] ) )
                {
                $ret = $this->BMLTPlugin_fast_mobile_lookup ();
                
                if ( ob_get_level () )     ob_end_clean(); // Just in case we are in an OB
                
                $handler = null;
                
                if ( zlib_get_coding_type() === false )
                    {
                    $handler = "ob_gzhandler";
                    }
                
                ob_start($handler);
                    echo $ret;
                ob_end_flush();
                die ( );
                }
            else
                {
                if ( !isset ( $this->my_http_vars['bmlt_settings_id'] ) )
                    {
                    $this->my_http_vars['bmlt_settings_id'] = null; // Just to squash a warning.
                    }
                    
                $options = $this->getBMLTOptions_by_id ( $this->my_http_vars['bmlt_settings_id'] );
                
                $this->load_params ( );
                if ( isset ( $this->my_http_vars['redirect_ajax'] ) && $this->my_http_vars['redirect_ajax'] )
                    {
                    $url = $options['root_server']."/client_interface/xhtml/index.php?switcher=RedirectAJAX$this->my_params";
                    
                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    $ret = bmlt_satellite_controller::call_curl ( $url );
                    
                    $handler = null;
                    
                    if ( zlib_get_coding_type() === false )
                        {
                        $handler = "ob_gzhandler";
                        }
                    
                    ob_start($handler);
                        echo $ret;
                    ob_end_flush();
                    die ( );
                    }
                elseif ( isset ( $this->my_http_vars['redirect_ajax_json'] ) )
                    {
                    $url = $options['root_server']."/client_interface/json/index.php?".$this->my_http_vars['redirect_ajax_json'].$this->my_params;

                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    $ret = bmlt_satellite_controller::call_curl ( $url );
                    
                    $handler = null;
                    
                    if ( zlib_get_coding_type() === false )
                        {
                        $handler = "ob_gzhandler";
                        }
                    
                    ob_start($handler);
                        echo $ret;
                    ob_end_flush();
                    die ( );
                    }
                elseif ( isset ( $this->my_http_vars['direct_simple'] ) )
                    {
                    $root_server = $options['root_server']."/client_interface/simple/index.php";
                    $params = urldecode ( $this->my_http_vars['search_parameters'] );
                    $url = "$root_server?switcher=GetSearchResults&".$params;
                    $result = bmlt_satellite_controller::call_curl ( $url );
                    $result = preg_replace ( '|\<a |', '<a rel="nofollow external" ', $result );
                    // What all this does, is pick out the single URI in the search parameters string, and replace the meeting details link with it.
                    if ( preg_match ( '|&single_uri=|', $this->my_http_vars['search_parameters'] ) )
                        {
                        $single_uri = '';
                        $sp = explode ( '&', $this->my_http_vars['search_parameters'] );
                        foreach ( $sp as $s )
                            {
                            if ( preg_match ( '|single_uri=|', $s ) )
                                {
                                list ( $key, $single_uri ) = explode ( '=', $s );
                                break;
                                }
                            }
                        if ( $single_uri )
                            {
                            $result = preg_replace ( '|\<a [^>]*href="'.preg_quote($options['root_server']).'.*?single_meeting_id=(\d+)[^>]*>|', "<a rel=\"nofollow\" title=\"".$this->process_text ( self::$local_single_meeting_tooltip)."\" href=\"".$single_uri."=$1&amp;supports_ajax=yes\">", $result );
                            }
                        $result = preg_replace ( '|\<a rel="external"|','<a rel="nofollow external" title="'.$this->process_text ( self::$local_gm_link_tooltip).'"', $result );
                        }

                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    
                    $handler = null;
                    
                    if ( zlib_get_coding_type() === false )
                        {
                        $handler = "ob_gzhandler";
                        }
                    
                    ob_start($handler);
                        echo $result;
                    ob_end_flush();
                    die ( );
                    }
                elseif ( isset ( $this->my_http_vars['result_type_advanced'] ) && ($this->my_http_vars['result_type_advanced'] == 'booklet') )
                    {
                    $uri =  $options['root_server']."/local_server/pdf_generator/?list_type=booklet$this->my_params";
                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    header ( "Location: $uri" );
                    die();
                    }
                elseif ( isset ( $this->my_http_vars['result_type_advanced'] ) && ($this->my_http_vars['result_type_advanced'] == 'listprint') )
                    {
                    $uri =  $options['root_server']."/local_server/pdf_generator/?list_type=listprint$this->my_params";
                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    header ( "Location: $uri" );
                    die();
                    }
                }
            }
        }
    
    /************************************************************************************//**
    *   \brief Massages the page content.                                                   *
    *                                                                                       *
    *   \returns a string, containing the "massaged" content.                               *
    ****************************************************************************************/
    function content_filter ( $in_the_content   ///< The content in need of filtering.
                            )
        {
        $old_content = $in_the_content; // We check to see if we added anything.
        // Simple searches can be mixed in with other content.
        $in_the_content = $this->display_simple_search ( $in_the_content );

        $in_the_content = $this->display_changes ( $in_the_content );
        
        $in_the_content = $this->display_new_map_search ( $in_the_content );
        
        $in_the_content = $this->display_bmlt_nouveau ( $in_the_content );
        
        // This simply ensures that we remove any unused mobile shortcodes.
        $in_the_content = self::replace_shortcode ( $in_the_content, 'bmlt_mobile', '' );
        
        if ( $in_the_content != $old_content )  // If we made changes, we add a wrapper element, so we can have some strong specificity.
            {
            $in_the_content = "<div id=\"bmlt_page_items\" class=\"bmlt_page_items\">$in_the_content</div>";
            }
            
        return $in_the_content;
        }
        
    /************************************************************************************//**
    *   \brief This is a function that filters the content, and replaces a portion with the *
    *   "popup" search, if provided by the 'bmlt_simple_searches' custom field.             *
    *                                                                                       *
    *   \returns a string, containing the content.                                          *
    ****************************************************************************************/

    function display_popup_search ( $in_content,    ///< This is the content to be filtered.
                                    $in_text,       ///< The text that has the parameters in it.
                                    &$out_count     ///< This is set to 1, if a substitution was made.
                                    )
        {
        if ( $in_text && self::get_shortcode ( $in_content, 'simple_search_list' ) )
            {
            $display .= '';

            $text_ar = explode ( "\n", $in_text );
            
            if ( is_array ( $text_ar ) && count ( $text_ar ) )
                {
                $display .= '<noscript class="no_js">'.$this->process_text ( self::$local_noscript ).'</noscript>';
                $display .= '<div id="interactive_form_div" class="interactive_form_div" style="display:none"><form action="#" onsubmit="return false"><div>';
                $display .= '<label class="meeting_search_select_label" for="meeting_search_select">Find Meetings:</label> ';
                $display .= '<select id="meeting_search_select"class="simple_search_list" onchange="BMLTPlugin_simple_div_filler (this.value,this.options[this.selectedIndex].text);this.options[this.options.length-1].disabled=(this.selectedIndex==0)">';
                $display .= '<option disabled="disabled" selected="selected">'.$this->process_text ( self::$local_select_search ).'</option>';
                $lines_max = count ( $text_ar );
                $lines = 0;
                while ( $lines < $lines_max )
                    {
                    $line['parameters'] = trim($text_ar[$lines++]);
                    $line['prompt'] = trim($text_ar[$lines++]);
                    if ( $line['parameters'] && $line['prompt'] )
                        {
                        $uri = $this->get_ajax_base_uri().'?bmlt_settings_id='.$this->cms_get_page_settings_id($in_content).'&amp;direct_simple&amp;search_parameters='.urlencode ( $line['parameters'] );
                        $display .= '<option value="'.$uri.'">'.__($line['prompt']).'</option>';
                        }
                    }
                $display .= '<option disabled="disabled"></option>';
                $display .= '<option disabled="disabled" value="">'.$this->process_text ( self::$local_clear_search ).'</option>';
                $display .= '</select></div></form>';
                
                $display .= '<script type="text/javascript">';
                $display .= 'document.getElementById(\'interactive_form_div\').style.display=\'block\';';
                $display .= 'document.getElementById(\'meeting_search_select\').selectedIndex=0;';

                $options = $this->getBMLTOptions_by_id ( $this->cms_get_page_settings_id($in_content) );
                $url = $this->get_plugin_path();
                $img_url .= htmlspecialchars ( $url.'themes/'.$options['theme'].'/images/' );
                
                $display .= "var c_g_BMLTPlugin_images = '$img_url';";
                $display .= '</script>';
                $display .= '<div id="simple_search_container"></div></div>';
                }
            
            if ( $display )
                {
                $in_content = self::replace_shortcode ( $in_content, 'simple_search_list', $display );
            
                $out_count = 1;
                }
            }
        
        return $in_content;
        }
        
    /************************************************************************************//**
    *   \brief This function implements the new, Maps API V. 3 version of the "classic"     *
    *          BMLT search screen.                                                          *
    *                                                                                       *
    *   \returns a string, containing the content.                                          *
    ****************************************************************************************/
    function display_bmlt_nouveau ($in_content      ///< This is the content to be filtered.
                                    )
        {        
        $theshortcode = 'bmlt';
        
        $options_id = $this->cms_get_page_settings_id( $in_content );

        $in_content = str_replace ( '&#038;', '&', $in_content );   // This stupid kludge is because WordPress does an untoward substitution. Won't do anything unless WordPress has been naughty.
        
        $first = true;

        while ( $params = self::get_shortcode ( $in_content, $theshortcode ) )
            {
            if ( $params !== true && intval ( $params ) )
                {
                $options_id = intval ( $params );
                }
        
            $options = $this->getBMLTOptions_by_id ( $options_id );
            $uid = htmlspecialchars ( 'bmlt_nouveau_'.uniqid() );
        
            $the_new_content = '<noscript>'.$this->process_text ( self::$local_noscript ).'</noscript>';    // We let non-JS browsers know that this won't work for them.
        
            if ( $first )   // We only load this the first time.
                {
                // These are the basic global JavaScript properties.
                $the_new_content .= $this->BMLTPlugin_nouveau_map_search_global_javascript_stuff ( );
                // Most of the display is built in DOM, but this is how we get our localized strings into JS. We put them in globals.
                $the_new_content .= '<script type="text/javascript">';
                $the_new_content .= "var g_NouveauMapSearch_advanced_name_string ='".$this->process_text ( self::$local_nouveau_advanced_button )."';";
                $the_new_content .= "var g_NouveauMapSearch_map_name_string ='".$this->process_text ( self::$local_nouveau_map_button )."';";
                $the_new_content .= "var g_NouveauMapSearch_text_name_string ='".$this->process_text ( self::$local_nouveau_text_button )."';";
                $the_new_content .= "var g_Nouveau_text_go_button_string ='".$this->process_text ( self::$local_nouveau_text_go_button )."';";
                $the_new_content .= "var g_Nouveau_text_location_label_text ='".$this->process_text ( self::$local_nouveau_text_location_label_text )."';";
                $the_new_content .= "var g_Nouveau_text_item_default_text ='".$this->process_text ( self::$local_nouveau_text_item_default_text )."';";
                $the_new_content .= "var g_Nouveau_advanced_weekdays_disclosure_text ='".$this->process_text ( self::$local_nouveau_advanced_weekdays_disclosure_text )."';";
                $the_new_content .= "var g_Nouveau_advanced_formats_disclosure_text ='".$this->process_text ( self::$local_nouveau_advanced_formats_disclosure_text )."';";
                $the_new_content .= "var g_Nouveau_advanced_service_bodies_disclosure_text ='".$this->process_text ( self::$local_nouveau_advanced_service_bodies_disclosure_text )."';";
                $the_new_content .= "var g_Nouveau_no_search_results_text ='".$this->process_text ( self::$local_nouveau_cant_find_meetings_display )."';";
                $the_new_content .= "var g_Nouveau_cant_lookup_display ='".$this->process_text ( self::$local_nouveau_cant_lookup_display )."';";
                $the_new_content .= "var g_Nouveau_select_search_spec_text ='".$this->process_text ( self::$local_nouveau_select_search_spec_text )."';";
                $the_new_content .= "var g_Nouveau_select_search_results_text ='".$this->process_text ( self::$local_nouveau_select_search_results_text )."';";
                $the_new_content .= "var g_Nouveau_display_map_results_text ='".$this->process_text ( self::$local_nouveau_display_map_results_text )."';";
                $the_new_content .= "var g_Nouveau_display_list_results_text ='".$this->process_text ( self::$local_nouveau_display_list_results_text )."';";
            
                $the_new_content .= "var g_Nouveau_location_services_set_my_location_advanced_button ='".$this->process_text ( self::$local_nouveau_location_services_set_my_location_advanced_button )."';";
                $the_new_content .= "var g_Nouveau_location_services_find_all_meetings_nearby_button ='".$this->process_text ( self::$local_nouveau_location_services_find_all_meetings_nearby_button )."';";
                $the_new_content .= "var g_Nouveau_location_services_find_all_meetings_nearby_later_today_button ='".$this->process_text ( self::$local_nouveau_location_services_find_all_meetings_nearby_later_today_button )."';";
                $the_new_content .= "var g_Nouveau_location_services_find_all_meetings_nearby_tomorrow_button ='".$this->process_text ( self::$local_nouveau_location_services_find_all_meetings_nearby_tomorrow_button )."';";

                $the_new_content .= "var g_Nouveau_meeting_results_count_sprintf_format ='".self:: $local_nouveau_meeting_results_count_sprintf_format."';";
                $the_new_content .= "var g_Nouveau_meeting_results_selection_count_sprintf_format ='".self:: $local_nouveau_meeting_results_selection_count_sprintf_format."';";
                $the_new_content .= "var g_Nouveau_meeting_results_single_selection_count_sprintf_format ='".self:: $local_nouveau_meeting_results_single_selection_count_sprintf_format."';";
                $the_new_content .= "var g_Nouveau_single_time_sprintf_format ='".self:: $local_nouveau_single_time_sprintf_format."';";
                $the_new_content .= "var g_Nouveau_single_duration_sprintf_format_1_hr ='".self:: $local_nouveau_single_duration_sprintf_format_1_hr."';";
                $the_new_content .= "var g_Nouveau_single_duration_sprintf_format_mins ='".self:: $local_nouveau_single_duration_sprintf_format_mins."';";
                $the_new_content .= "var g_Nouveau_single_duration_sprintf_format_hrs ='".self:: $local_nouveau_single_duration_sprintf_format_hrs."';";
                $the_new_content .= "var g_Nouveau_single_duration_sprintf_format_hr_mins ='".self:: $local_nouveau_single_duration_sprintf_format_hr_mins."';";
                $the_new_content .= "var g_Nouveau_single_duration_sprintf_format_hrs_mins ='".self:: $local_nouveau_single_duration_sprintf_format_hrs_mins."';";
            
                $the_new_content .= "var g_Nouveau_location_sprintf_format_loc_street_info = '".self::$local_nouveau_location_sprintf_format_loc_street_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_loc_street = '".self::$local_nouveau_location_sprintf_format_loc_street."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_street_info = '".self::$local_nouveau_location_sprintf_format_street_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_loc_info = '".self::$local_nouveau_location_sprintf_format_loc_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_street = '".self::$local_nouveau_location_sprintf_format_street."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_loc = '".self::$local_nouveau_location_sprintf_format_loc."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_town_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_town_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_street_info_town_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_info_town_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_street_town_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_town_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_town_province_zip."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_town_province = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_town_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_town_province = '".self::$local_nouveau_location_sprintf_format_single_loc_street_town_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_town_province = '".self::$local_nouveau_location_sprintf_format_single_street_info_town_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_town_province = '".self::$local_nouveau_location_sprintf_format_single_loc_info_town_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_town_province = '".self::$local_nouveau_location_sprintf_format_single_street_town_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_town_province = '".self::$local_nouveau_location_sprintf_format_single_loc_town_province."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_town_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_town_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_town_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_town_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_town_zip = '".self::$local_nouveau_location_sprintf_format_single_street_info_town_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_town_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_info_town_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_town_zip = '".self::$local_nouveau_location_sprintf_format_single_street_town_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_town_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_town_zip."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_province_zip = '".self::$local_nouveau_location_sprintf_format_single_street_info_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_info_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_province_zip = '".self::$local_nouveau_location_sprintf_format_single_street_province_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_province_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_province_zip."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_province = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_province = '".self::$local_nouveau_location_sprintf_format_single_loc_street_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_province = '".self::$local_nouveau_location_sprintf_format_single_street_info_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_province = '".self::$local_nouveau_location_sprintf_format_single_loc_info_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_province = '".self::$local_nouveau_location_sprintf_format_single_street_province."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_province = '".self::$local_nouveau_location_sprintf_format_single_loc_province."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_street_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info_zip = '".self::$local_nouveau_location_sprintf_format_single_street_info_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_info_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_zip = '".self::$local_nouveau_location_sprintf_format_single_street_zip."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_zip = '".self::$local_nouveau_location_sprintf_format_single_loc_zip."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street_info = '".self::$local_nouveau_location_sprintf_format_single_loc_street_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_street = '".self::$local_nouveau_location_sprintf_format_single_loc_street."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street_info = '".self::$local_nouveau_location_sprintf_format_single_street_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc_info = '".self::$local_nouveau_location_sprintf_format_single_loc_info."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_street = '".self::$local_nouveau_location_sprintf_format_single_street."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_single_loc = '".self::$local_nouveau_location_sprintf_format_single_loc."';";

                $the_new_content .= "var g_Nouveau_location_sprintf_format_wtf ='".$this->process_text ( self::$local_nouveau_location_sprintf_format_wtf )."';";

                $the_new_content .= "var g_Nouveau_time_sprintf_format = '".self::$local_nouveau_time_sprintf_format."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_duration_title = '".self::$local_nouveau_location_sprintf_format_duration_title."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_duration_hour_only_title = '".self::$local_nouveau_location_sprintf_format_duration_hour_only_title."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_duration_hour_only_and_minutes_title = '".self::$local_nouveau_location_sprintf_format_duration_hour_only_and_minutes_title."';";
                $the_new_content .= "var g_Nouveau_location_sprintf_format_duration_hours_only_title = '".self::$local_nouveau_location_sprintf_format_duration_hours_only_title."';";
                $the_new_content .= "var g_Nouveau_am ='".$this->process_text ( self::$local_nouveau_am )."';";
                $the_new_content .= "var g_Nouveau_pm ='".$this->process_text ( self::$local_nouveau_pm )."';";
                $the_new_content .= "var g_Nouveau_noon ='".$this->process_text ( self::$local_nouveau_noon )."';";
                $the_new_content .= "var g_Nouveau_midnight ='".$this->process_text ( self::$local_nouveau_midnight )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_label_1 ='".$this->process_text ( self::$local_nouveau_advanced_map_radius_label_1 )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_label_2 ='".$this->process_text ( self::$local_nouveau_advanced_map_radius_label_2 )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_value_2_km ='".$this->process_text ( self::$local_nouveau_advanced_map_radius_value_km )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_value_2_mi ='".$this->process_text ( self::$local_nouveau_advanced_map_radius_value_mi )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_value_auto ='".$this->process_text ( self::$local_nouveau_advanced_map_radius_value_auto )."';";
                $the_new_content .= "var g_Nouveau_advanced_map_radius_value_array = [ ".self::$local_nouveau_advanced_map_radius_value_array." ];";
                $the_new_content .= "var g_Nouveau_meeting_details_link_title = '".$this->process_text ( self::$local_nouveau_meeting_details_link_title )."';";
                $the_new_content .= "var g_Nouveau_meeting_details_map_link_uri_format = '".htmlspecialchars ( self::$local_nouveau_meeting_details_map_link_uri_format )."';";
                $the_new_content .= "var g_Nouveau_meeting_details_map_link_text = '".$this->process_text ( self::$local_nouveau_meeting_details_map_link_text )."';";
                $the_new_content .= "var g_Nouveau_array_keys = {";
                    $first = true;
                    foreach ( self::$local_nouveau_prompt_array as $key => $value )
                        {
                        if ( !$first )
                            {
                            $the_new_content .= ',';
                            }
                        $first = false;
                        $the_new_content .= '"'.$key.'":';
                        $the_new_content .= '"'.$this->process_text ( $value ).'"';
                        }
                $the_new_content .= "};";
            
                $the_new_content .= 'var g_Nouveau_array_header_text = new Array ( "'.join ( '","', self::$local_nouveau_table_header_array ).'");';
                $the_new_content .= 'var g_Nouveau_weekday_long_array = new Array ( "'.join ( '","', self::$local_nouveau_weekday_long_array ).'");';
                $the_new_content .= 'var g_Nouveau_weekday_short_array = new Array ( "'.join ( '","', self::$local_nouveau_weekday_short_array ).'");';
                $the_new_content .= "var g_Nouveau_lookup_location_failed = '".$this->process_text ( self::$local_nouveau_lookup_location_failed )."';";              
                $the_new_content .= "var g_Nouveau_lookup_location_server_error = '".$this->process_text ( self::$local_nouveau_lookup_location_server_error )."';";              
                $the_new_content .= "var g_Nouveau_default_geo_width = -10;";
                $the_new_content .= "var g_Nouveau_default_details_map_zoom = ".self::$default_details_map_zoom.';';
                $the_new_content .= "var g_Nouveau_default_marker_aggregation_threshold_in_pixels = 8;";
                $the_new_content .= "var g_Nouveau_default_duration = '".$options['default_duration']."';";

                $the_new_content .= "var g_Nouveau_single_formats_label = '".$this->process_text ( self::$local_nouveau_single_formats_label )."';";
                $the_new_content .= "var g_Nouveau_single_service_body_label = '".$this->process_text ( self::$local_nouveau_single_service_body_label )."';";
                
                if ( isset ( $this->m_is_logged_in_user ) && $this->m_is_logged_in_user )
                    {
                    $the_new_content .= "var g_Nouveau_user_logged_in = 'true';";
                    }
            
                $the_new_content .= '</script>';
                $first = false;
                }
        
            $in_options_id = $options['id'];
        
            if ( defined ( '_DEBUG_MODE_' ) ) $the_new_content .= "\n"; // These just make the code easier to look at.
            // This is the overall container div.
            $the_new_content .= '<div id="'.$uid.'_container" class="bmlt_nouveau_container">';
                $single_meeting_id = isset ( $this->my_http_vars['single_meeting_id'] ) ? intval($this->my_http_vars['single_meeting_id']) : 0;
                // What we do here, is tell the client to create a global variable (in JS DOM), with a unique handler for this instance of the Nouveau search.
                $the_new_content .= '<script type="text/javascript">var g_instance_'.$uid.'_js_handler = new NouveauMapSearch ( \''.$uid.'\', \''.$options['bmlt_initial_view'].'\','.$options['map_center_latitude'].",".$options['map_center_longitude'].",".$options['map_zoom'].",'".$options['distance_units']."','".$this->get_plugin_path()."themes/".$options['theme']."','".htmlspecialchars ( $this->get_ajax_base_uri() )."?bmlt_settings_id=$in_options_id&redirect_ajax_json=', '', ".($options['bmlt_location_checked'] ? 'true' : 'false').", ".($options['bmlt_location_services'] == 0 || ($options['bmlt_location_services'] == 1 && BMLTPlugin_weAreMobile($this->my_http_vars)) ? 'true' : 'false').", ".$single_meeting_id.", ".$options['grace_period'].");</script>";
            $the_new_content .= '</div>';

            $in_content = self::replace_shortcode ( $in_content, $theshortcode, $the_new_content );
            }
            
        return $in_content;
        }
        
    /************************************************************************************//**
    *   \brief This is a function that filters the content, and replaces a portion with the *
    *   "simple" search                                                                     *
    *                                                                                       *
    *   \returns a string, containing the content.                                          *
    ****************************************************************************************/
    function display_simple_search ($in_content      ///< This is the content to be filtered.
                                    )
        {
        $options_id = $this->cms_get_page_settings_id( $in_content );
        
        $options = $this->getBMLTOptions_by_id ( $options_id );
        $root_server_root = $options['root_server'];

        $in_content = str_replace ( '&#038;', '&', $in_content );   // This stupid kludge is because WordPress does an untoward substitution. Won't do anything unless WordPress has been naughty.
        while ( $params = self::get_shortcode ( $in_content, 'bmlt_simple' ) )
            {
            $param_array = explode ( '##-##', $params );    // You can specify a settings ID, by separating it from the URI parameters with a ##-##.
            
            $params = null;
            
            if ( is_array ( $param_array ) && (count ( $param_array ) > 1) )
                {
                $options = $this->getBMLTOptions_by_id ( $param_array[0] );
                $root_server_root = $options['root_server'];
                $params = '?'.$param_array[1];
                }
            else
                {
                $params = (count ($param_array) > 0) ? '?'.$param_array[0] : null;
                }
            
            $uri = $root_server_root."/client_interface/simple/index.php".$params;

            $the_new_content = bmlt_satellite_controller::call_curl ( $uri );
            $in_content = self::replace_shortcode ( $in_content, 'bmlt_simple', $the_new_content );
            }
        return $in_content;
        }
        
    /************************************************************************************//**
    *   \brief This is a function that filters the content, and replaces a portion with the *
    *   "new map" search                                                                    *
    *                                                                                       *
    *   \returns a string, containing the content.                                          *
    ****************************************************************************************/
    function display_new_map_search ($in_content      ///< This is the content to be filtered.
                                    )
        {
        $options_id = $this->cms_get_page_settings_id( $in_content );

        $in_content = str_replace ( '&#038;', '&', $in_content );   // This stupid kludge is because WordPress does an untoward substitution. Won't do anything unless WordPress has been naughty.
        
        $first = true;

        while ( $params = self::get_shortcode ( $in_content, 'bmlt_map' ) )
            {
            if ( $params !== true && intval ( $params ) )
                {
                $options_id = intval ( $params );
                }
            
            $options = $this->getBMLTOptions_by_id ( $options_id );
            $uid = htmlspecialchars ( 'BMLTuid_'.uniqid() );
            
            $the_new_content = '<noscript>'.$this->process_text ( self::$local_noscript ).'</noscript>';    // We let non-JS browsers know that this won't work for them.
            
            if ( $first )   // We only load this the first time.
                {
                $the_new_content .= $this->BMLTPlugin_map_search_global_javascript_stuff ( );
                $first = false;
                }

            $the_new_content .= '<div class="bmlt_map_container_div bmlt_map_container_div_theme_'.htmlspecialchars ( $options['theme'] ).'" style="display:none" id="'.$uid.'">';  // This starts off hidden, and is revealed by JS.
                $the_new_content .= '<div class="bmlt_map_container_div_header">';  // This allows a CSS "hook."
                    $the_new_content .= $this->BMLTPlugin_map_search_location_options($options_id, $uid);   // This is the box of location search choices.
                    $the_new_content .= $this->BMLTPlugin_map_search_search_options($options_id, $uid);     // This is the box of basic search choices.
                    $the_new_content .= $this->BMLTPlugin_map_search_local_javascript_stuff ( $options_id, $uid );
                $the_new_content .= '</div>';
                $the_new_content .= '<div class="bmlt_search_map_div" id="'.$uid.'_bmlt_search_map_div"></div>';
                $the_new_content .= '<div class="bmlt_search_map_new_search_div" id="'.$uid.'_bmlt_search_map_new_search_div" style="display:none"><a href="javascript:c_ms_'.$uid.'.newSearchExt();">'.$this->process_text ( self::$local_new_map_js_new_search ).'</a></div>';
                $the_new_content .= '<script type="text/javascript">g_no_meetings_found="'.htmlspecialchars ( self::$local_cant_find_meetings_display ).'";document.getElementById(\''.$uid.'\').style.display=\'block\';c_ms_'.$uid.' = new MapSearch ( \''.htmlspecialchars ( $uid ).'\',\''.htmlspecialchars ( $options_id ).'\', document.getElementById(\''.$uid.'_bmlt_search_map_div\'), {\'latitude\':'.$options['map_center_latitude'].',\'longitude\':'.$options['map_center_longitude'].',\'zoom\':'.$options['map_zoom'].'} )</script>';
            $the_new_content .= '</div>';
            
            $in_content = self::replace_shortcode ( $in_content, 'bmlt_map', $the_new_content );
            }
            
        return $in_content;
        }

    /************************************************************************************//**
    *   \brief  This returns a div of location options to be applied to the map search.     *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_map_search_location_options(    $in_options_id, ///< The ID for the options to use for this implementation.
                                                        $in_uid         ///< This is the UID of the enclosing div.
                                                        )
        {
        $ret = '<div class="bmlt_map_container_div_location_options_div" id="'.$in_uid.'_location">';
            $ret .= '<div class="bmlt_map_options_loc">';
                $ret .= '<a class="bmlt_map_reveal_options" id="'.$in_uid.'_options_loc_a" href="javascript:var a=document.getElementById(\''.$in_uid.'_options_loc_a\');var b=document.getElementById(\''.$in_uid.'_options_loc\');if(b &amp;&amp; a){if(b.style.display==\'none\'){a.className=\'bmlt_map_hide_options\';b.style.display=\'block\';c_ms_'.$in_uid.'.openLocationSectionExt(document.getElementById(\''.$in_uid.'_location_text\'), document.getElementById(\''.$in_uid.'_location_submit\'));}else{a.className=\'bmlt_map_reveal_options\';b.style.display=\'none\';};};c_ms_'.$in_uid.'.recalculateMapExt()"><span>'.$this->process_text ( self::$local_new_map_option_loc_label ).'</span></a>';
                $ret .= '<div class="bmlt_map_container_div_search_options_div" id="'.$in_uid.'_options_loc" style="display:none">';
                    $ret .= '<form action="#" method="get" onsubmit="c_ms_'.$in_uid.'.lookupLocationExt(document.getElementById(\''.$in_uid.'_location_text\'), document.getElementById(\''.$in_uid.'_location_submit\'));return false">';
                        $ret .= '<fieldset class="bmlt_map_container_div_search_options_div_location_fieldset">';
                            $ret .= '<div class="location_radius_popup_div">';
                                $ret .= '<label for="">'.$this->process_text ( self::$local_new_map_option_loc_popup_label_1 ).'</label>';
                                $ret .= '<select class="bmlt_map_location_radius_popup" id="'.$in_uid.'_radius_select" onchange="c_ms_'.$in_uid.'.changeRadiusExt(true)">';
                                    $ret .= '<option value="" selected="selected">'.$this->process_text ( self::$local_new_map_option_loc_popup_auto ).'</option>';
                                    $ret .= '<option value="" disabled="disabled"></option>';
                                    $options = $this->getBMLTOptions_by_id ( $in_options_id );
                                    foreach ( self::$local_new_map_js_diameter_choices as $radius )
                                        {
                                        $ret .= '<option value="'.($radius / 2).'">'.($radius / 2).' '.$this->process_text ( (strtolower ($options['distance_units']) == 'km') ? self::$local_new_map_option_loc_popup_km : self::$local_new_map_option_loc_popup_mi ).'</option>';
                                        }
                                $ret .= '</select>';
                                $ret .= '<label for="">'.$this->process_text ( self::$local_new_map_option_loc_popup_label_2 ).'</label>';
                            $ret .= '</div>';
                            $ret .= '<fieldset class="location_text_entry_fieldset">';
                                $ret .= '<legend>'.$this->process_text ( self::$local_new_map_text_entry_fieldset_label ).'</legend>';
                                $def_text = $this->process_text ( self::$local_new_map_text_entry_default_text );
                                $ret .= '<div class="location_text_input_div">';
                                    $ret .= '<input type="text" class="location_text_input_item_blurred" value="'.$def_text.'" id="'.$in_uid.'_location_text" onfocus="c_ms_'.$in_uid.'.focusLocationTextExt(this, document.getElementById(\''.$in_uid.'_location_submit\'), false)" onblur="c_ms_'.$in_uid.'.focusLocationTextExt(this, document.getElementById(\''.$in_uid.'_location_submit\'), true)" onkeyup="c_ms_'.$in_uid.'.enterTextIntoLocationTextExt(this, document.getElementById(\''.$in_uid.'_location_submit\'))" />';
                                $ret .= '</div>';
                                $ret .= '<div class="location_text_submit_div">';
                                    $ret .= '<input type="button" disabled="disabled" class="location_text_submit_button" value="'.$this->process_text ( self::$local_new_map_location_submit_button_text ).'" id="'.$in_uid.'_location_submit" onclick="c_ms_'.$in_uid.'.lookupLocationExt(document.getElementById(\''.$in_uid.'_location_text\'), this)" />';
                                $ret .= '</div>';
                            $ret .= '</fieldset>';
                        $ret .= '</fieldset>';
                    $ret .= '</form>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief  This returns a div of search options to be applied to the map search.       *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_map_search_search_options(  $in_options_id, ///< The ID for the options to use for this implementation.
                                                    $in_uid         ///< This is the UID of the enclosing div.
                                                    )
        {
        $ret = '<div class="bmlt_map_container_div_search_options_div" id="'.$in_uid.'_options">';
            $ret .= '<div class="bmlt_map_options_1">';
                $ret .= '<a class="bmlt_map_reveal_options" id="'.$in_uid.'_options_1_a" href="javascript:var a=document.getElementById(\''.$in_uid.'_options_1_a\');var b=document.getElementById(\''.$in_uid.'_options_1\');if(b &amp;&amp; a){if(b.style.display==\'none\'){a.className=\'bmlt_map_hide_options\';b.style.display=\'block\'}else{a.className=\'bmlt_map_reveal_options\';b.style.display=\'none\'}};c_ms_'.$in_uid.'.recalculateMapExt()"><span>'.$this->process_text ( self::$local_new_map_option_1_label ).'</span></a>';
                $ret .= '<div class="bmlt_map_container_div_search_options_div" id="'.$in_uid.'_options_1" style="display:none">';
                    $ret .= '<form action="#" method="get" onsubmit="return false">';
                        $ret .= '<fieldset class="bmlt_map_container_div_search_options_div_weekdays_fieldset">';
                            $ret .= '<legend>'.$this->process_text ( self::$local_new_map_weekdays ).'</legend>';
                            $ret .= '<div class="bmlt_map_container_div_search_options_weekday_checkbox_div"><input title="'.$this->process_text ( self::$local_new_map_all_weekdays_title ).'" type="checkbox" id="weekday_'.$in_uid.'_0" checked="checked" onchange="c_ms_'.$in_uid.'.recalculateMapExt(this)" />';
                            $ret .= '<label title="'.$this->process_text ( self::$local_new_map_all_weekdays_title ).'" for="weekday_'.$in_uid.'_0">'.$this->process_text ( self::$local_new_map_all_weekdays ).'</label></div>';
                            for ( $index = 1;  $index < count ( self::$local_weekdays ); $index++ )
                                {
                                $weekday = self::$local_weekdays[$index];
                                $ret .= '<div class="bmlt_map_container_div_search_options_weekday_checkbox_div">';
                                    $ret .= '<input title="'.$this->process_text ( self::$local_new_map_weekdays_title.$weekday ).'." type="checkbox" id="weekday_'.$in_uid.'_'.htmlspecialchars ( $index ).'" onchange="c_ms_'.$in_uid.'.recalculateMapExt(this)" />';
                                    $ret .= '<label title="'.$this->process_text ( self::$local_new_map_weekdays_title.$weekday ).'." for="weekday_'.$in_uid.'_'.htmlspecialchars ( $index ).'">'.$this->process_text ( $weekday ).'</label>';
                                $ret .= '</div>';
                                }
                        $ret .= '</fieldset>';
                        $ret .= '<fieldset class="bmlt_map_container_div_search_options_div_formats_fieldset">';
                            $ret .= '<legend>'.$this->process_text ( self::$local_new_map_formats ).'</legend>';
                            $ret .= '<div class="bmlt_map_container_div_search_options_formats_checkbox_div">';
                                $ret .= '<input title="'.$this->process_text ( self::$local_new_map_all_formats_title ).'" type="checkbox" id="formats_'.$in_uid.'_0" checked="checked" onchange="c_ms_'.$in_uid.'.recalculateMapExt(this)" />';
                                $ret .= '<label title="'.$this->process_text ( self::$local_new_map_all_formats_title ).'" for="formats_'.$in_uid.'_0">'.$this->process_text ( self::$local_new_map_all_formats ).'</label>';
                            $ret .= '</div>';
                            $options = $this->getBMLTOptions_by_id ( $in_options_id );
                            $this->my_driver->set_m_root_uri ( $options['root_server'] );
                            $error = $this->my_driver->get_m_error_message();
                            
                            if ( $error )
                                {
                                }
                            else
                                {
                                $formats = $this->my_driver->get_server_formats();
        
                                if ( !$this->my_driver->get_m_error_message() )
                                    {
                                    $index = 1;
                                    foreach ( $formats as $id => $format )
                                        {
                                        $ret .= '<div class="bmlt_map_container_div_search_options_formats_checkbox_div"><input type="checkbox" value="'.intval ( $id ).'" id="formats_'.$in_uid.'_'.$index.'" onchange="c_ms_'.$in_uid.'.recalculateMapExt(this)" title="'.$this->process_text ( '('.$format['name_string'] .') '.$format['description_string'] ).'" />';
                                        $ret .= '<label title="'.$this->process_text ( '('.$format['name_string'] .') '.$format['description_string'] ).'" for="formats_'.$in_uid.'_'.$index.'">'.$this->process_text ( $format['key_string'] ).'</label></div>';
                                        $index++;
                                        }
                                    }
                                }
                        $ret .= '</fieldset>';
                    $ret .= '</form>';
                $ret .= '</div>';
            $ret .= '</div>';
        $ret .= '</div>';
        return $ret;
        }

    /************************************************************************************//**
    *   \brief  This returns the global JavaScript stuff for the new map search that only   *
    *           only needs to be loaded once.                                               *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_map_search_global_javascript_stuff()
        {
        // Include the Google Maps API V3 files.
        $ret = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
        $ret .= '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry"></script>';       
        // Declare the various globals and display strings. This is how we pass strings to the JavaScript, as opposed to the clunky way we do it in the root server.
        $ret .= '<script type="text/javascript">';
        $ret .= 'var c_g_cannot_determine_location = \''.$this->process_text ( self::$local_cannot_determine_location ).'\';';
        $ret .= 'var c_g_no_meetings_found = \''.$this->process_text ( self::$local_mobile_fail_no_meetings ).'\';';
        $ret .= 'var c_g_server_error = \''.$this->process_text ( self::$local_server_fail ).'\';';
        $ret .= 'var c_g_address_lookup_fail = \''.$this->process_text ( self::$local_cant_find_address ).'\';';
        $ret .= 'var c_g_center_marker_curent_radius_1 = \''.$this->process_text ( self::$local_new_map_js_center_marker_current_radius_1 ).'\';';
        $ret .= 'var c_g_center_marker_curent_radius_2_km = \''.$this->process_text ( self::$local_new_map_js_center_marker_current_radius_2_km ).'\';';
        $ret .= 'var c_g_center_marker_curent_radius_2_mi = \''.$this->process_text ( self::$local_new_map_js_center_marker_current_radius_2_mi ).'\';';
        $ret .= 'var c_g_map_link_text = \''.$this->process_text ( self::$local_map_link ).'\';';
        $ret .= 'var c_g_weekdays = [';
        $ret .= "'".$this->process_text ( join ( "','", self::$local_weekdays ) )."'";
        $ret .= '];';
        $ret .= 'var c_g_weekdays_short = [';
        $ret .= "'".$this->process_text ( join ( "','", self::$local_weekdays_short ) )."'";
        $ret .= '];';
        $ret .= 'var c_g_diameter_choices = ['.join ( ",", self::$local_new_map_js_diameter_choices ).'];';
        $ret .= 'var c_g_formats = \''.$this->process_text ( self::$local_formats ).'\';';
        $ret .= 'var c_g_Noon = \''.$this->process_text ( self::$local_noon ).'\';';
        $ret .= 'var c_g_Midnight = \''.$this->process_text ( self::$local_midnight ).'\';';
        $ret .= 'var c_g_debug_mode = '.( defined ( 'DEBUG_MODE' ) ? 'true' : 'false' ).';';
        $ret .= 'var c_g_distance_prompt = \''.$this->process_text ( self::$local_mobile_distance ).'\';';
        $ret .= 'var c_g_distance_prompt_suffix = \''.$this->process_text ( self::$local_new_map_center_marker_distance_suffix ).'\';';
        $ret .= 'var c_g_distance_center_marker_desc = \''.$this->process_text ( self::$local_new_map_center_marker_description ).'\';';
        $ret .= 'var c_BMLTPlugin_files_uri = \''.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?\';';
        $ret .= "var c_g_BMLTPlugin_images = '".htmlspecialchars ( $this->get_plugin_path()."/google_map_images" )."';";
        $ret .= "var c_g_BMLTPlugin_default_location_text = '".$this->process_text ( self::$local_new_map_text_entry_default_text )."';";
        $ret .= '</script>';
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'javascript.js" type="text/javascript"></script>';
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'map_search.js" type="text/javascript"></script>';

        return $ret;
        }

    /************************************************************************************//**
    *   \brief  This returns the global JavaScript stuff for the new map search that only   *
    *           only needs to be loaded once.                                               *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_nouveau_map_search_global_javascript_stuff()
        {
        // Include the Google Maps API V3 files.
        $ret = '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>';
        $ret .= '<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false&libraries=geometry"></script>';       
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'javascript.js" type="text/javascript"></script>';
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'nouveau_map_search.js" type="text/javascript"></script>';

        return $ret;
        }

    /************************************************************************************//**
    *   \brief  This returns the JavaScript stuff that needs to be loaded into each of the  *
    *           new map search instances.                                                   *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_map_search_local_javascript_stuff(  $in_options_id, ///< The ID for the options to use for this implementation.
                                                            $in_uid         ///< The unique ID for this instance
                                                            )
        {
        $options = $this->getBMLTOptions_by_id ( $in_options_id );

        // Declare the various globals and display strings. This is how we pass strings to the JavaScript, as opposed to the clunky way we do it in the root server.
        $ret = '<script type="text/javascript">';
        $ret .= 'var c_ms_'.$in_uid.' = null;';
        $ret .= 'var c_g_distance_units_are_km_'.$in_uid.' = '.((strtolower ($options['distance_units']) == 'km' ) ? 'true' : 'false').';';
        $ret .= 'var c_g_distance_units_'.$in_uid.' = \''.((strtolower ($options['distance_units']) == 'km' ) ? $this->process_text ( self::$local_mobile_kilometers ) : $this->process_text ( self::$local_mobile_miles ) ).'\';';
        $ret .= 'var c_g_BMLTPlugin_throbber_img_src_'.$in_uid." = '".htmlspecialchars ( $this->get_plugin_path().'themes/'.$options['theme'].'/images/Throbber.gif' )."';";
        $ret .= 'var c_g_BMLTRoot_URI_JSON_SearchResults_'.$in_uid." = '".htmlspecialchars ( $this->get_ajax_base_uri() )."?redirect_ajax_json=".urlencode ( 'switcher=GetSearchResults' )."&bmlt_settings_id=$in_options_id';\n";
        $ret .= '</script>';

        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief This is a function that filters the content, and replaces a portion with the *
    *   "changes" dump.                                                                     *
    *                                                                                       *
    *   \returns a string, containing the content.                                          *
    ****************************************************************************************/
    function display_changes (  $in_content      ///< This is the content to be filtered.
                                )
        {
        $options_id = $this->cms_get_page_settings_id( $in_content );
        
        $options = $this->getBMLTOptions_by_id ( $options_id );
        $root_server_root = $options['root_server'];

        $in_content = str_replace ( '&#038;', '&', $in_content );   // This stupid kludge is because WordPress does an untoward substitution. Won't do anything unless WordPress has been naughty.
        while ( $params = self::get_shortcode ( $in_content, 'bmlt_changes' ) )
            {
            $param_array = explode ( '##-##', $params );    // You can specify a settings ID, by separating it from the URI parameters with a ##-##.
            
            $params = null;
            
            if ( is_array ( $param_array ) && (count ( $param_array ) > 1) )
                {
                $options = $this->getBMLTOptions_by_id ( $param_array[0] );
                $params = $param_array[1];
                }
            else
                {
                $params = (count ($param_array) > 0) ? $param_array[0] : null;
                }
            
            if ( $params && $options['root_server'] )
                {
                $params = explode ( '&', $params );
                
                $start_date = null;
                $end_date = null;
                $meeting_id = null;
                $service_body_id = null;
                $single_uri = null;
                
                foreach ( $params as $one_param )
                    {
                    list ( $key, $value ) = explode ( '=', $one_param, 2 );
                    
                    if ( $key && $value )
                        {
                        switch ( $key )
                            {
                            case 'start_date':
                                $start_date = strtotime ( $value );
                            break;
                            
                            case 'end_date':
                                $end_date = strtotime ( $value );
                            break;
                            
                            case 'meeting_id':
                                $meeting_id = intval ( $value );
                            break;
                            
                            case 'service_body_id':
                                $service_body_id = intval ( $value );
                            break;
                            
                            case 'single_uri':
                                $single_uri = $value;
                            break;
                            }
                        }
                    }
                $this->my_driver->set_m_root_uri ( $options['root_server'] );
                $error = $this->my_driver->get_m_error_message();
                
                if ( $error )
                    {
                    if ( ob_get_level () )         ob_end_clean(); // Just in case we are in an OB
                    echo "<!-- BMLTPlugin ERROR (display_changes)! Can't set the Satellite Driver root! ".htmlspecialchars ( $error )." -->";
                    }
                else
                    {
	                set_time_limit ( 120 ); // Change requests can take a loooong time...
                    $changes = $this->my_driver->get_meeting_changes ( $start_date, $end_date, $meeting_id, $service_body_id );

                    $error = $this->my_driver->get_m_error_message();
                    
                    if ( $error )
                        {
                        if ( ob_get_level () )             ob_end_clean(); // Just in case we are in an OB
                        echo "<!-- BMLTPlugin ERROR (display_changes)! Error during get_meeting_changes Call! ".htmlspecialchars ( $error )." -->";
                        }
                    else
                        {
                        $the_new_content = '<div class="bmlt_change_record_div">';
                        foreach ( $changes as $change )
                            {
                            $the_new_content .= self::setup_one_change ( $change, $single_uri );
                            }
                        
                        $the_new_content .= '</div>';
                        
                        $in_content = self::replace_shortcode ( $in_content, 'bmlt_changes', $the_new_content );
                        }
                    }
                }
            }
        return $in_content;
        }

    /************************************************************************************//**
    *   \brief Returns the XHTML for one single change record.                              *
    *                                                                                       *
    *   \returns A string. The DOCTYPE to be displayed.                                     *
    ****************************************************************************************/
    static function setup_one_change (  $in_change_array,       ///< One change record
                                        $in_single_uri = null   ///< If there was a specific single meeting URI, we pass it in here.
                                        )
        {
        $ret = '<dl class="bmlt_change_record_dl" id="bmlt_change_dl_'.htmlspecialchars ( $in_change_array['change_type'] ).'_'.intval ( $in_change_array['date_int'] ).'_'.intval ( $in_change_array['meeting_id'] ).'">';
            $ret .= '<dt class="bmlt_change_record_dt bmlt_change_record_dt_date">'.self::process_text ( self::$local_change_label_date ).'</dt>';
                $ret .= '<dd class="bmlt_change_record_dd bmlt_change_record_dd_date">'.date ( self::$local_change_date_format, intval ( $in_change_array['date_int'] ) ).'</dd>';
            
            if ( isset ( $in_change_array['meeting_name'] ) && $in_change_array['meeting_name'] )
                {
                $ret .= '<dt class="bmlt_change_record_dt bmlt_change_record_dt_name">'.self::process_text ( self::$local_change_label_meeting_name ).'</dt>';
                    $ret .= '<dd class="bmlt_change_record_dd bmlt_change_record_dd_name">';
                    
                    if ( isset ( $in_change_array['meeting_id'] ) && $in_change_array['meeting_id'] && isset ( $in_single_uri ) && $in_single_uri )
                        {
                        $ret .= '<a href="'.htmlspecialchars ( $in_single_uri ).$in_change_array['meeting_id'].'" rel="nofollow">';
                            $ret .= self::process_text ( html_entity_decode ( $in_change_array['meeting_name'] ) );
                        $ret .= '</a>';
                        }
                    else
                        {
                        $ret .= self::process_text ( html_entity_decode ( $in_change_array['meeting_name'] ) );
                        }
                    
                    $ret .= '</dd>';
                }
            if ( isset ( $in_change_array['service_body_name'] ) && $in_change_array['service_body_name'] )
                {
                $ret .= '<dt class="bmlt_change_record_dt bmlt_change_record_dt_service_body_name">'.self::process_text ( self::$local_change_label_service_body_name ).'</dt>';
                    $ret .= '<dd class="bmlt_change_record_dd bmlt_change_record_dd_service_body_name">'.self::process_text ( html_entity_decode ( $in_change_array['service_body_name'] ) ).'</dd>';
                }
            if ( isset ( $in_change_array['user_name'] ) && $in_change_array['user_name'] )
                {
                $ret .= '<dt class="bmlt_change_record_dt bmlt_change_record_dt_service_body_admin_name">'.self::process_text ( self::$local_change_label_admin_name ).'</dt>';
                    $ret .= '<dd class="bmlt_change_record_dd bmlt_change_record_dd_service_body_admin_name">'.self::process_text ( html_entity_decode ( $in_change_array['user_name'] ) ).'</dd>';
                }
            if ( isset ( $in_change_array['details'] ) && $in_change_array['details'] )
                {
                $ret .= '<dt class="bmlt_change_record_dt bmlt_change_record_dt_description">'.self::process_text ( self::$local_change_label_description ).'</dt>';
                    $ret .= '<dd class="bmlt_change_record_dd bmlt_change_record_dd_description">'.self::process_text ( html_entity_decode ( $in_change_array['details'] ) ).'</dd>';
                }
        $ret .= '</dl>';
        
        return $ret;
        }

    /************************************************************************************//**
    *                              FAST MOBILE LOOKUP ROUTINES                              *
    *                                                                                       *
    *   Our mobile support is based on the fast mobile client. It has been adapted to fit   *
    *   into a WordPress environment.                                                       *
    ****************************************************************************************/

    /************************************************************************************//**
    *   \brief Checks the UA of the caller, to see if it should return XHTML Strict or WML. *
    *                                                                                       *
    *   NOTE: This is very, very basic. It is not meant to be a studly check, like WURFL.   *
    *                                                                                       *
    *   \returns A string. The DOCTYPE to be displayed.                                     *
    ****************************************************************************************/
    static function BMLTPlugin_select_doctype(  $in_http_vars   ///< The query variables
                                            )
        {
        $ret = '';
        
        function isDeviceWML1()
        {
            return BMLTPlugin::mobile_sniff_ua($in_http_vars) == 'wml';
        }
    
        function isDeviceWML2()
        {
            return BMLTPlugin::mobile_sniff_ua($in_http_vars) == 'xhtml_mp';
        }
            
        function isMobileDevice()
        {
            $language = BMLTPlugin::mobile_sniff_ua($in_http_vars);
            return ($language != 'xhtml') && ($language != 'smartphone');
        }
        
        // If we aren't deliberately forcing an emulation, we figure it out for ourselves.
        if ( !isset ( $in_http_vars['WML'] ) )
            {
            if ( isDeviceWML1() )
                {
                $in_http_vars['WML'] = 1;
                }
            elseif ( isDeviceWML2() )
                {
                $in_http_vars['WML'] = 2;
                }
            elseif ( isMobileDevice() )
                {
                $in_http_vars['WML'] = 1;
                }
            }
        
        // We may specify a mobile XHTML (WML 2) manually.
        if ( isset ( $in_http_vars['WML'] ) )
            {
            if ( $in_http_vars['WML'] == 2 )    // Use the XHTML MP header
                {
                $ret = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
                }
            else    // Default is WAP
                {
                $ret = '<'; // This is because some servers are dumb enough to interpret the embedded prolog as PHP delimiters.
                $ret .= '?xml version="1.0"?';
                $ret .= '><!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">';
                }
            }
        else
            {
            // We return a fully-qualified XHTML 1.0 Strict page.
            $ret = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            }
        
        if ( !isset ( $in_http_vars['WML'] ) || ($in_http_vars['WML'] != 1) )
            {
            $ret .= '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"';
            if ( !isset ( $in_http_vars['WML'] ) )
                {
                $ret .= ' lang="en"';
                }
            $ret .= '>';
            }
        else
            {
            $ret .= '<wml>';
            }
        
        $ret .= '<head>';
    
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Output the necessary Javascript. This is only called for a "pure javascript" *
    *   do_search invocation (smartphone interactive map).                                  *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_fast_mobile_lookup_javascript_stuff( $in_sensor = true  ///< A Boolean. If false, then we will invoke the API with the sensor set false. Default is true.
                                                            )
        {
        $options = $this->getBMLTOptions_by_id ( $this->my_http_vars['bmlt_settings_id'] );
            
        $ret = '';
        $sensor = $in_sensor ? 'true' : 'false';

        // Include the Google Maps API V3 files.
        $ret .= '<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor='.$sensor.'"></script>';
        
        // Declare the various globals and display strings. This is how we pass strings to the JavaScript, as opposed to the clunky way we do it in the root server.
        $ret .= '<script type="text/javascript">';
        $ret .= 'var c_g_cannot_determine_location = \''.$this->process_text ( self::$local_cannot_determine_location ).'\';';
        $ret .= 'var c_g_no_meetings_found = \''.$this->process_text ( self::$local_mobile_fail_no_meetings ).'\';';
        $ret .= 'var c_g_server_error = \''.$this->process_text ( self::$local_server_fail ).'\';';
        $ret .= 'var c_g_address_lookup_fail = \''.$this->process_text ( self::$local_cant_find_address ).'\';';
        $ret .= 'var c_g_map_link_text = \''.$this->process_text ( self::$local_map_link ).'\';';
        $ret .= 'var c_g_weekdays = [';
        $ret .= "'".$this->process_text ( join ( "','", self::$local_weekdays ) )."'";
        $ret .= '];';
        $ret .= 'var c_g_formats = \''.$this->process_text ( self::$local_formats ).'\';';
        $ret .= 'var c_g_Noon = \''.$this->process_text ( self::$local_noon ).'\';';
        $ret .= 'var c_g_Midnight = \''.$this->process_text ( self::$local_midnight ).'\';';
        $ret .= 'var c_g_debug_mode = '.( defined ( 'DEBUG_MODE' ) ? 'true' : 'false' ).';';
        $h = null;
        $m = null;
        list ( $h, $m ) = explode ( ':', date ( "G:i", time() + ($options['time_offset'] * 60 * 60) - ($options['grace_time'] * 60) ) );
        $ret .= 'var c_g_hour = '.intval ( $h ).';';
        $ret .= 'var c_g_min = '.intval ( $m ).';';
        $ret .= 'var c_g_distance_prompt = \''.$this->process_text ( self::$local_mobile_distance ).'\';';
        $ret .= 'var c_g_distance_units_are_km = '.((strtolower ($options['distance_units']) == 'km' ) ? 'true' : 'false').';';
        $ret .= 'var c_g_distance_units = \''.((strtolower ($options['distance_units']) == 'km' ) ? $this->process_text ( self::$local_mobile_kilometers ) : $this->process_text ( self::$local_mobile_miles ) ).'\';';
        $ret .= 'var c_BMLTPlugin_files_uri = \''.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?\';';
        $ret .= 'var c_bmlt_settings_id='.$this->my_http_vars['bmlt_settings_id'].';';        
        $url = $this->get_plugin_path();

        $img_url = "$url/google_map_images";

        $img_url = htmlspecialchars ( $img_url );
        
        $ret .= "var c_g_BMLTPlugin_images = '$img_url';";
        $ret .= '</script>';
       
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'javascript.js" type="text/javascript"></script>';
        $ret .= '<script src="'.htmlspecialchars ( $this->get_plugin_path() ).(!defined ( '_DEBUG_MODE_' ) ? 'js_stripper.php?filename=' : '').'fast_mobile_lookup.js" type="text/javascript"></script>';

        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Output whatever header stuff is necessary for the available UA               *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_fast_mobile_lookup_header_stuff()
        {
        $ret = '';
        $url = $this->get_plugin_path();
            
        $ret .= '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';    // WML 1 only cares about the charset and cache.
        $ret .= '<meta http-equiv="Cache-Control" content="max-age=300"  />';               // Cache for 5 minutes.
        $ret .= '<meta http-equiv="Cache-Control" content="no-transform"  />';              // No Transforms.

        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )   // If full XHTML
            {
            // Various meta tags we need.
            $ret .= '<meta http-equiv="Content-Script-Type" content="text/javascript" />';      // Set the types for inline styles and scripts.
            $ret .= '<meta http-equiv="Content-Style-Type" content="text/css" />';
            $ret .= '<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />'; // Make sure iPhone screens stay humble.
            
            $url = $this->get_plugin_path();
            
            $options = $this->getBMLTOptions_by_id ( $this->my_http_vars['bmlt_settings_id'] );
            
            $url = htmlspecialchars ( $url.'themes/'.$options['theme'].'/' );
            
            if ( defined ( '_DEBUG_MODE_' ) ) // In debug mode, we use unoptimized versions of these files for easier tracking.
                {
                $ret .= '<link rel="stylesheet" media="all" href="'.$url.'fast_mobile_lookup.css" type="text/css" />';
                }
            else
                {
                $ret .= '<link rel="stylesheet" media="all" href="'.htmlspecialchars($url).'style_stripper.php?filename=fast_mobile_lookup.css" type="text/css" />';
                }
            
            // If we have a shortcut icon, set it here.
            if ( defined ('_SHORTCUT_LOC_' ) )
                {
                $ret .= '<link rel="SHORTCUT ICON" href="'.$this->process_text ( _SHORTCUT_LOC_ ).'" />';
                }
            
            // Set the appropriate page title.
            if ( isset ( $this->my_http_vars['do_search'] ) )
                {
                $ret .= '<title>'.$this->process_text ( $local_mobile_results_page_title ).'</title>';
                }
            else
                {
                $ret .= '<title>'.$this->process_text ( $local_mobile_results_form_title ).'</title>';
                }
            }
        
        $ret .= '</head>';

        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Returns the XHTML/WML for the Map Search form. These are the three "fast     *
    *   lookup" links displayed at the top (Note display:none" in the style).               *
    *   This is to be revealed by JavaScript.                                               *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_draw_map_search_form ()
        {
        $ret = '<div class="search_intro" id="hidden_until_js" style="display:none">';
            $ret .= '<h1 class="banner_h1">'.$this->process_text ( self::$local_GPS_banner ).'</h1>';
            $ret .= '<h2 class="banner_h2">'.$this->process_text ( self::$local_GPS_banner_subtext ).'</h2>';
            $ret .= '<div class="link_one_line"><a rel="nofollow" accesskey="1" href="'.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?BMLTPlugin_mobile&amp;do_search&amp;bmlt_settings_id='.$this->my_http_vars['bmlt_settings_id'].((isset ( $this->my_http_vars['base_url'] ) && $this->my_http_vars['base_url']) ? '&amp;base_url='.urlencode($this->my_http_vars['base_url']) : '').'">'.$this->process_text ( self::$local_search_all ).'</a></div>';
            $ret .= '<div class="link_one_line"><a rel="nofollow" accesskey="2" href="'.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?BMLTPlugin_mobile&amp;do_search&amp;qualifier=today&amp;bmlt_settings_id='.$this->my_http_vars['bmlt_settings_id'].((isset ( $this->my_http_vars['base_url'] ) && $this->my_http_vars['base_url']) ? '&amp;base_url='.urlencode($this->my_http_vars['base_url']) : '').'">'.$this->process_text ( self::$local_search_today ).'</a></div>';
            $ret .= '<div class="link_one_line"><a rel="nofollow" accesskey="3" href="'.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?BMLTPlugin_mobile&amp;do_search&amp;qualifier=tomorrow&amp;bmlt_settings_id='.$this->my_http_vars['bmlt_settings_id'].((isset ( $this->my_http_vars['base_url'] ) && $this->my_http_vars['base_url']) ? '&amp;base_url='.urlencode($this->my_http_vars['base_url']) : '').'">'.$this->process_text ( self::$local_search_tomorrow ).'</a></div>';
            $ret .= '<hr class="meeting_divider_hr" />';
        $ret .= '</div>';
        
        return $ret;
        }
    
    /************************************************************************************//**
    *   \brief Returns the XHTML/WML for the Address Entry form                             *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_draw_address_search_form ()
        {
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret = '<form class="address_input_form" method="get" action="'.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'"';
            if ( !isset ( $this->my_http_vars['WML'] ) )
                {
                // This fills the form with a "seed" text (standard accessibility practice). We do it this way, so we don't clutter the form if no JavaScript is available.
                $ret .= ' onsubmit="if((document.getElementById(\'address_input\').value==\'';
                $ret .= $this->process_text ( self::$local_enter_an_address );
                $ret .= '\')||!document.getElementById(\'address_input\').value){alert(\''.$this->process_text ( self::$local_enter_address_alert ).'\');document.getElementById(\'address_input\').focus();return false}else{if(document.getElementById(\'hidden_until_js\').style.display==\'block\'){document.getElementById(\'do_search\').value=\'1\'}}"';
                }
            $ret .= '>';
            $ret .= '<div class="search_address">';
            // The default, is we return a list. This is changed by JavaScript.
            $ret .= '<input type="hidden" name="BMLTPlugin_mobile" />';
            
            if ( isset ( $this->my_http_vars['base_url'] ) && $this->my_http_vars['base_url'] )
                {
                $ret .= '<input type="hidden" name="base_url" value="'.htmlspecialchars($this->my_http_vars['base_url']).'" />';
                }
                
            $ret .= '<input type="hidden" name="bmlt_settings_id" value="'.$this->my_http_vars['bmlt_settings_id'].'" />';
            $ret .= '<input type="hidden" name="do_search" id="do_search" value="the hard way" />';
            $ret .= '<h1 class="banner_h2">'.$this->process_text ( self::$local_search_address_single ).'</h1>';
            if ( !isset ( $this->my_http_vars['WML'] ) )  // This is here to prevent WAI warnings.
                {
                $ret .= '<label for="address_input" style="display:none">'.$this->process_text ( self::$local_enter_address_alert ).'</label>';
                }
            if ( isset ( $this->my_http_vars['WML'] ) )
                {
                $ret .= '<input type="hidden" name="WML" value="2" />';
                }
            }
        else
            {
            $ret = '<p>';   // WML rides the short bus.
            }
        
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret .= '<div class="address_top" id="address_input_line_wrapper">';
            $ret .= '<div class="link_one_line input_line_div" id="address_input_line_div">';
            if ( !isset ( $this->my_http_vars['WML'] ) )
                {
                $ret .= '<div class="link_one_line" id="hidden_until_js2" style="display:none">';
                $ret .= '<input type="checkbox" id="force_list_checkbox"';
                $ret .= ' onchange="if(this.checked){document.getElementById ( \'hidden_until_js\' ).style.display = \'none\';document.getElementById(\'address_input\').focus();}else{document.getElementById ( \'hidden_until_js\' ).style.display = \'block\'}" /><label for="force_list_checkbox"';
                $ret .= '> '.$this->process_text ( self::$local_list_check ).'</label>';
                $ret .= '</div>';
                }
            $ret .= '</div>';
            }
            
        $ret .= '<input type="text" name="address"';
        
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret .= ' id="address_input" class="address_input" size="64" value=""';
            if ( !isset ( $this->my_http_vars['WML'] ) )
                {
                $ret .= ' onfocus="if(!this.value||(this.value==\''.$this->process_text ( self::$local_enter_an_address ).'\'))this.value=\'\'"';
                $ret .= ' onkeydown="if(!this.value||(this.value==\''.$this->process_text ( self::$local_enter_an_address ).'\'))this.value=\'\'"';
                $ret .= ' onblur="if(!this.value)this.value=\''.$this->process_text ( self::$local_enter_an_address ).'\'"';
                }
            }
        else
            {
            $ret .= ' size="32" format="*m"';
            }
        
        $ret .= ' />';
        
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret .= '</div>';
            }
        else
            {
            $ret .= '</p>';
            }
        
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret .= '<div class="link_form_elements">';
            $ret .= '<div class="link_one_line">';
            $ret .= '<input checked="checked" id="search_all_days" type="radio" name="qualifier" value="" />';
            $ret .= '<label for="search_all_days"> '.$this->process_text ( self::$local_search_all_address ).'</label>';
            $ret .= '</div>';
            $ret .= '<div class="link_one_line">';
            $ret .= '<input id="search_today" type="radio" name="qualifier" value="today" />';
            $ret .= '<label for="search_today"> '.$this->process_text ( self::$local_search_today ).'</label>';
            $ret .= '</div>';
            $ret .= '<div class="link_one_line">';
            $ret .= '<input id="search_tomorrow" type="radio" name="qualifier" value="tomorrow" />';
            $ret .= '<label for="search_tomorrow"> '.$this->process_text ( self::$local_search_tomorrow ).'</label>';
            $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '<div class="link_one_line_submit">';
            if ( !isset ( $this->my_http_vars['WML'] ) )  // This silly thing is to prevent WAI warnings.
                {
                $ret .= '<label for="submit_button" style="display:none">'.$this->process_text ( _SEARCH_SUBMIT_ ).'</label>';
                }
            $ret .= '<input id="submit_button" type="submit" value="'.$this->process_text ( self::$local_search_submit_button ).'"';
            if ( !isset ( $this->my_http_vars['WML'] ) )
                {
                $ret .= ' onclick="if((document.getElementById(\'address_input\').value==\'';
                $ret .= $this->process_text ( self::$local_enter_an_address );
                $ret .= '\')||!document.getElementById(\'address_input\').value){alert(\''.$this->process_text ( self::$local_enter_address_alert ).'\');document.getElementById(\'address_input\').focus();return false}else{if(document.getElementById(\'hidden_until_js\').style.display==\'block\'){document.getElementById(\'do_search\').value=\'1\'}}"';
                }
            $ret .= ' />';
            $ret .= '</div>';
            $ret .= '</div>';
            $ret .= '</form>';
            }
        else
            {
            $ret .= '<p>';
            $ret .= '<select name="qualifier" value="">';
            $ret .= '<option value="">'.$this->process_text ( self::$local_search_all_address ).'</option>';
            $ret .= '<option value="today">'.$this->process_text ( self::$local_search_today ).'</option>';
            $ret .= '<option value="tomorrow">'.$this->process_text ( self::$local_search_tomorrow ).'</option>';
            $ret .= '</select>';
            $ret .= '<anchor>';
            $ret .= '<go href="'.htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'" method="get">';
            $ret .= '<postfield name="address" value="$(address)"/>';
            $ret .= '<postfield name="qualifier" value="$(qualifier)"/>';
            $ret .= '<postfield name="do_search" value="the hard way" />';
            $ret .= '<postfield name="WML" value="1" />';
            $ret .= '<postfield name="BMLTPlugin_mobile" value="1" />';
            if ( isset ( $this->my_http_vars['base_url'] ) && $this->my_http_vars['base_url'] )
                {
                $ret .= '<postfield type="hidden" name="base_url" value="'.htmlspecialchars($this->my_http_vars['base_url']).'" />';
                }
            $ret .= '<postfield name="bmlt_settings_id" value="'.$this->my_http_vars['bmlt_settings_id'].'" />';
            $ret .= '</go>';
            $ret .= $this->process_text ( $local_search_submit_button );
            $ret .= '</anchor>';
            $ret .= '</p>';
            }
        
        return $ret;
        }

    /************************************************************************************//**
    *   \brief Renders one WML card                                                         *
    *                                                                                       *
    *   \returns A string. The WML 1.1 to be displayed.                                     *
    ****************************************************************************************/

    function BMLTPlugin_render_card (   $ret,                   ///< The current XHTML tally (so we can count it).
                                        $index,                 ///< The page index of the meeting.
                                        $count,                 ///< The total number of meetings.
                                        $meeting                ///< The meeting data.
                                    )
                            
        {
        $ret .= '<card id="card_'.$index.'" title="'.htmlspecialchars($meeting['meeting_name']).'">';
        

        if ( $count > 1 )
            {
            $next_card = null;
            $prev_card = null;
            $myself = null;
            $vars = array();
            
            unset ( $_REQUEST['access_card'] );
            
            foreach ( $_REQUEST as $name => $val )
                {
                $text = urlencode ( $name ).'='.urlencode ( $val );
                array_push ( $vars, $text );
                }
            
            $myself = htmlspecialchars ( $this->get_ajax_mobile_base_uri() ).'?'.join ( '&amp;', $vars ).'&amp;access_card=';
        
            if ( $index < $count )
                {
                $next_card = $myself.strval($index + 1);
                }
            
            if ( $index > 1 )
                {
                $prev_card = $myself.strval($index - 1);
                }

            $ret .= '<p><table columns="3"><tr>';
            $ret .= '<td>';
            if ( $prev_card )
                {
                $ret .= '<small><anchor>'.$this->process_text (self::$local_prev_card).'<go href="'.$prev_card.'"/></anchor></small>';
                }
            $ret .= '</td><td>&nbsp;</td><td>';
            if ( $next_card )
                {
                $ret .= '<small><anchor>'.$this->process_text (self::$local_next_card).'<go href="'.$next_card.'"/></anchor></small>';
                }
            
            $ret .= '</td></tr></table></p>';
            }
    
        $ret .= '<p><big><strong>'.htmlspecialchars($meeting['meeting_name']).'</strong></big></p>';
        $ret .= '<p>'.$this->process_text ( self::$local_weekdays[$meeting['weekday_tinyint']] ).' '.htmlspecialchars ( date ( 'g:i A', strtotime ( $meeting['start_time'] ) ) ).'</p>';
        if ( $meeting['location_text'] )
            {
            $ret .= '<p><b>'.htmlspecialchars ( $meeting['location_text'] ).'</b></p>';
            }
        
        $ret .= '<p>';
        if ( $meeting['location_street'] )
            {
            $ret .= htmlspecialchars ( $meeting['location_street'] );
            }
        
        if ( $meeting['location_neighborhood'] )
            {
            $ret .= ' ('.htmlspecialchars ( $meeting['location_neighborhood'] ).')';
            }
        $ret .= '</p>';
        
        if ( $meeting['location_municipality'] )
            {
            $ret .= '<p>'.htmlspecialchars ( $meeting['location_municipality'] );
        
            if ( $meeting['location_province'] )
                {
                $ret .= ', '.htmlspecialchars ( $meeting['location_province'] );
                }
            
            if ( $meeting['location_postal_code_1'] )
                {
                $ret .= ' '.htmlspecialchars ( $meeting['location_postal_code_1'] );
                }
            $ret .= '</p>';
            }
        
        $distance = null;
        
        if ( $meeting['distance_in_km'] )
            {
            $distance = round ( ((strtolower ($options['distance_units']) == 'km') ? $meeting['distance_in_km'] : $meeting['distance_in_miles']), 1 );
            
            $distance = strval ($distance).' '.((strtolower ($options['distance_units']) == 'km' ) ? $this->process_text ( self::$local_mobile_kilometers ) : $this->process_text ( self::$local_mobile_miles ) );

            $ret .= '<p><b>'.$this->process_text ( self::$local_mobile_distance ).':</b> '.htmlspecialchars ( $distance ).'</p>';
            }
                                                        
        if ( $meeting['location_info'] )
            {
            $ret .= '<p>'.htmlspecialchars ( $meeting['location_info'] ).'</p>';
            }
                    
        if ( $meeting['comments'] )
            {
            $ret .= '<p>'.htmlspecialchars ( $meeting['comments'] ).'</p>';
            }
        
        $ret .= '<p><b>'.$this->process_text ( self::$local_formats ).':</b> '.htmlspecialchars ( $meeting['formats'] ).'</p>';
        
        $ret .= '</card>';
        
        return $ret;
        }

    /************************************************************************************//**
    *   \brief Runs the lookup.                                                             *
    *                                                                                       *
    *   \returns A string. The XHTML to be displayed.                                       *
    ****************************************************************************************/
    function BMLTPlugin_fast_mobile_lookup()
        {
        /************************************************************************************//**
        *   \brief Sorting Callback                                                             *
        *                                                                                       *
        *   This will sort meetings by weekday, then by distance, so the first meeting of any   *
        *   given weekday is the closest one, etc.                                              *
        *                                                                                       *
        *   \returns -1 if a < b, 1, otherwise.                                                 *
        ****************************************************************************************/
        function mycmp (    $in_a_meeting,  ///< These are meeting data arrays. The elements we'll be checking will be 'weekday_tinyint' and 'distance_in_XX'.
                            $in_b_meeting
                        )
            {
            $ret = 0;
            
            if ( $in_a_meeting['weekday_tinyint'] != $in_b_meeting['weekday_tinyint'] )
                {
                $ret = ($in_a_meeting['weekday_tinyint'] < $in_b_meeting['weekday_tinyint']) ? -1 : 1;
                }
            else
                {
                $dist_a = intval ( round (strtolower(($options['distance_units']) == 'mi') ? $in_a_meeting['distance_in_miles'] : $in_a_meeting['distance_in_km'], 1) * 10 );
                $dist_b = intval ( round ((strtolower($options['distance_units']) == 'mi') ? $in_b_meeting['distance_in_miles'] : $in_b_meeting['distance_in_km'], 1) * 10 );
    
                if ( $dist_a != $dist_b )
                    {
                    $ret = ($dist_a < $dist_b) ? -1 : 1;
                    }
                else
                    {
                    $time_a = preg_replace ( '|:|', '', $in_a_meeting['start_time']);
                    $time_b = preg_replace ( '|:|', '', $in_b_meeting['start_time']);
                    $ret = ($time_a < $time_b) ? -1 : 1;
                    }
                }
            
            return $ret;
            }
        $ret = self::BMLTPlugin_select_doctype($this->my_http_vars);
        $ret .= $this->BMLTPlugin_fast_mobile_lookup_header_stuff();   // Add styles and/or JS, depending on the UA.
        $options = $this->getBMLTOptions_by_id ( $this->my_http_vars['bmlt_settings_id'] );
        
        // If we are running XHTML, then JavaScript works. Let's see if we can figure out where we are...
        // If the client can handle JavaScript, then the whole thing can be done with JS, and there's no need for the driver.
        // Also, if JS does not work, the form will ask us to do it "the hard way" (i.e. on the server).
        if ( $this->my_http_vars['address'] && isset ( $this->my_http_vars['do_search'] ) && (($this->my_http_vars['do_search'] == 'the hard way') || (isset ( $this->my_http_vars['WML'] ) && ($this->my_http_vars['WML'] == 1))) )
            {
            if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )   // Regular XHTML requires a body element.
                {
                $ret .= '<body>';
                }
            
            $this->my_driver->set_m_root_uri ( $options['root_server'] );
            $error = $this->my_driver->get_m_error_message();
            
            if ( $error )
                {
                if ( ob_get_level () )     ob_end_clean(); // Just in case we are in an OB
                die ( '<h1>ERROR (BMLTPlugin_fast_mobile_lookup: '.htmlspecialchars ( $error ).')</h1>' );
                }
            
            $qualifier = strtolower ( trim ( $this->my_http_vars['qualifier'] ) );
            
            // Do the search.
            
            if ( $this->my_http_vars['address'] )
                {
                $this->my_driver->set_current_transaction_parameter ( 'SearchString', $this->my_http_vars['address'] );
                $error_message = $this->my_driver->get_m_error_message();
                if ( $error_message )
                    {
                    $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                    }
                else
                    {
                    $this->my_driver->set_current_transaction_parameter ( 'StringSearchIsAnAddress', true );
                    $error_message = $this->my_driver->get_m_error_message();
                    if ( $error_message )
                        {
                        $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                        }
                    else
                        {
                        if ( $qualifier )
                            {
                            $weekdays = '';
                            $h = 0;
                            $m = 0;
                            $time = time() + ($options['time_offset'] * 60 * 60);
                            $today = intval(date ( "w", $time )) + 1;
                            // We set the current time, minus the grace time. This allows us to be running late, yet still have the meeting listed.
                            list ( $h, $m ) = explode ( ':', date ( "G:i", time() - ($options['grace_period'] * 60) ) );
                            if ( $qualifier == 'today' )
                                {
                                $weekdays = strval ($today);
                                }
                            else
                                {
                                $weekdays = strval ( ($today < 7) ? $today + 1 : 1 );
                                }
                            $this->my_driver->set_current_transaction_parameter ( 'weekdays', array($weekdays) );
                            $error_message = $this->my_driver->get_m_error_message();
                            if ( $error_message )
                                {
                                $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                                }
                            else
                                {
                                if ( $h || $m )
                                    {
                                    $this->my_driver->set_current_transaction_parameter ( 'StartsAfterH', $h );
                                    $error_message = $this->my_driver->get_m_error_message();
                                    if ( $error_message )
                                        {
                                        $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                                        }
                                    else
                                        {
                                        $this->my_driver->set_current_transaction_parameter ( 'StartsAfterM', $m );
                                        $error_message = $this->my_driver->get_m_error_message();
                                        if ( $error_message )
                                            {
                                            $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                                            }
                                        }
                                    }
                                }
                            }
                        
                        if ( $error_message )
                            {
                            $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                            }
                        else
                            {
                            $this->my_driver->set_current_transaction_parameter ( 'SearchStringRadius', -10 );
                            $error_message = $this->my_driver->get_m_error_message();
                            if ( $error_message )
                                {
                                $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                                }
                            else    // The search is set up. Throw the switch, Igor! ...yeth...mawther....
                                {
                                $search_result = $this->my_driver->meeting_search();

                                $error_message = $this->my_driver->get_m_error_message();
                                if ( $error_message )
                                    {
                                    $ret .= $this->process_text ( self::$local_server_fail ).' "'.htmlspecialchars ( $error_message ).'"';
                                    }
                                elseif ( isset ( $search_result ) && is_array ( $search_result ) && isset ( $search_result['meetings'] ) )
                                    {
                                    // Yes! We have valid search data!
                                    if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )   // Regular XHTML
                                        {
                                        $ret .= '<div class="multi_meeting_div">';
                                        }
                                    
                                    $index = 1;
                                    $count = count ( $search_result['meetings'] );
                                    usort ( $search_result['meetings'], 'mycmp' );
                                    if ( isset ( $_REQUEST['access_card'] ) && intval ( $_REQUEST['access_card'] ) )
                                        {
                                        $index = intval ( $_REQUEST['access_card'] );
                                        }
                                        
                                    if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )   // Regular XHTML
                                        {
                                        $index = 1;
                                        foreach ( $search_result['meetings'] as $meeting )
                                            {
                                            $ret .= '<div class="single_meeting_div">';
                                            $ret .= '<h1 class="meeting_name_h2">'.htmlspecialchars($meeting['meeting_name']).'</h1>';
                                            $ret .= '<p class="time_day_p">'.$this->process_text ( self::$local_weekdays[$meeting['weekday_tinyint']] ).' ';
                                            $time = explode ( ':', $meeting['start_time'] );
                                            $am_pm = ' AM';
                                            $distance = null;
                                            
                                            if ( $meeting['distance_in_km'] )
                                                {
                                                $distance = round ( ((strtolower ($options['distance_units']) == 'km') ? $meeting['distance_in_km'] : $meeting['distance_in_miles']), 1 );
                                                
                                                $distance = strval ($distance).' '.((strtolower ($options['distance_units']) == 'km' ) ? $this->process_text ( self::$local_mobile_kilometers ) : $this->process_text ( self::$local_mobile_miles ) );
                                                }

                                            $time[0] = intval ( $time[0] );
                                            $time[1] = intval ( $time[1] );
                                            
                                            if ( ($time[0] == 23) && ($time[1] > 50) )
                                                {
                                                $ret .= $this->process_text ( self::$local_midnight );
                                                }
                                            elseif ( ($time[0] == 12) && ($time[1] == 0) )
                                                {
                                                $ret .= $this->process_text ( self::$local_noon );
                                                }
                                            else
                                                {
                                                if ( ($time[0] > 12) || (($time[0] == 12) && ($time[1] > 0)) )
                                                    {
                                                    $am_pm = ' PM';
                                                    }
                                                
                                                if ( $time[0] > 12 )
                                                    {
                                                    $time[0] -= 12;
                                                    }
                                            
                                                if ( $time[1] < 10 )
                                                    {
                                                    $time[1] = "0$time[1]";
                                                    }
                                                
                                                $ret .= htmlspecialchars ( $time[0].':'.$time[1].$am_pm );
                                                }
                                            
                                            $ret .= '</p>';
                                            if ( $meeting['location_text'] )
                                                {
                                                $ret .= '<p class="locations_text_p">'.htmlspecialchars ( $meeting['location_text'] ).'</p>';
                                                }
                                            
                                            $ret .= '<p class="street_p">';
                                            if ( $meeting['location_street'] )
                                                {
                                                $ret .= htmlspecialchars ( $meeting['location_street'] );
                                                }
                                            
                                            if ( $meeting['location_neighborhood'] )
                                                {
                                                $ret .= '<span class="neighborhood_span"> ('.htmlspecialchars ( $meeting['location_neighborhood'] ).')</span>';
                                                }
                                            $ret .= '</p>';
                                            
                                            if ( $meeting['location_municipality'] )
                                                {
                                                $ret .= '<p class="town_p">'.htmlspecialchars ( $meeting['location_municipality'] );
                                            
                                                if ( $meeting['location_province'] )
                                                    {
                                                    $ret .= '<span class="state_span">, '.htmlspecialchars ( $meeting['location_province'] ).'</span>';
                                                    }
                                                
                                                if ( $meeting['location_postal_code_1'] )
                                                    {
                                                    $ret .= '<span class="zip_span"> '.htmlspecialchars ( $meeting['location_postal_code_1'] ).'</span>';
                                                    }
                                                $ret .= '</p>';
                                                if ( !isset ( $this->my_http_vars['WML'] ) )
                                                    {
                                                    $ret .= '<p id="maplink_'.intval($meeting['id_bigint']).'" style="display:none">';
                                                    $url = '';

                                                    $comma = false;
                                                    if ( $meeting['meeting_name'] )
                                                        {
                                                        $url .= urlencode($meeting['meeting_name']);
                                                        $comma = true;
                                                        }
                                                        
                                                    if ( $meeting['location_text'] )
                                                        {
                                                        $url .= ($comma ? ',+' : '').urlencode($meeting['location_text']);
                                                        $comma = true;
                                                        }
                                                    
                                                    if ( $meeting['location_street'] )
                                                        {
                                                        $url .= ($comma ? ',+' : '').urlencode($meeting['location_street']);
                                                        $comma = true;
                                                        }
                                                    
                                                    if ( $meeting['location_municipality'] )
                                                        {
                                                        $url .= ($comma ? ',+' : '').urlencode($meeting['location_municipality']);
                                                        $comma = true;
                                                        }
                                                        
                                                    if ( $meeting['location_province'] )
                                                        {
                                                        $url .= ($comma ? ',+' : '').urlencode($meeting['location_province']);
                                                        }
                                                    
                                                    $url = 'http://maps.google.com/maps?q='.urlencode($meeting['latitude']).','.urlencode($meeting['longitude']) . '+(%22'.str_replace ( "%28", '-', str_replace ( "%29", '-', $url )).'%22)';
                                                    $url .= '&ll='.urlencode($meeting['latitude']).','.urlencode($meeting['longitude']);
                                                    $ret .= '<a rel="external nofollow" accesskey="'.$index.'" href="'.htmlspecialchars ( $url ).'" title="'.htmlspecialchars($meeting['meeting_name']).'">'.$this->process_text ( self::$local_map_link ).'</a>';
                                                    $ret .= '<script type="text/javascript">document.getElementById(\'maplink_'.intval($meeting['id_bigint']).'\').style.display=\'block\';var c_BMLTPlugin_settings_id = '.htmlspecialchars ( $this->my_http_vars['bmlt_settings_id'] ).';</script>';

                                                    $ret .= '</p>';
                                                    }
                                                }
                                                        
                                            if ( $meeting['location_info'] )
                                                {
                                                $ret .= '<p class="location_info_p">'.htmlspecialchars ( $meeting['location_info'] ).'</p>';
                                                }
                                                        
                                            if ( $meeting['comments'] )
                                                {
                                                $ret .= '<p class="comments_p">'.htmlspecialchars ( $meeting['comments'] ).'</p>';
                                                }
                                            
                                            if ( $distance )
                                                {
                                                $ret .= '<p class="distance_p"><strong>'.$this->process_text ( self::$local_mobile_distance ).':</strong> '.htmlspecialchars ( $distance ).'</p>';
                                                }
                                                
                                            $ret .= '<p class="formats_p"><strong>'.$this->process_text ( self::$local_formats ).':</strong> '.htmlspecialchars ( $meeting['formats'] ).'</p>';
                                            $ret .= '</div>';
                                            if ( $index++ < $count )
                                                {
                                                if ( !isset ( $this->my_http_vars['WML'] ) )
                                                    {
                                                    $ret .= '<hr class="meeting_divider_hr" />';
                                                    }
                                                else
                                                    {
                                                    $ret .= '<hr />';
                                                    }
                                                }
                                            }
                                        }
                                    else    // WML 1 (yuch) We do this, because we need to limit the size of the pages to fit simple phones.
                                        {
                                        $meetings = $search_result['meetings'];
                                        $indexed_array = array_values($meetings);
                                        $ret = $this->BMLTPlugin_render_card ( $ret, $index, $count, $indexed_array[$index - 1], false );
                                        }
                                    
                                    if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )   // Regular XHTML
                                        {
                                        $ret .= '</div>';
                                        }
                                    }
                                else
                                    {
                                    $ret .= '<h1 class="failed_search_h1';
                                    if ( isset ( $this->my_http_vars['WML'] ) && $this->my_http_vars['WML'] )   // We use a normally-positioned element in WML.
                                        {
                                        $ret .= '_wml';
                                        }
                                    $ret .= '">'.$this->process_text (self::$local_mobile_fail_no_meetings).'</h1>';
                                    }
                                }
                            }
                        }
                    }
                }
            else
                {
                $ret .= '<h1 class="failed_search_h1">'.$this->process_text (self::$local_enter_address_alert).'</h1>';
                }
            }
        elseif ( isset ( $this->my_http_vars['do_search'] ) && !((($this->my_http_vars['do_search'] == 'the hard way') || (isset ( $this->my_http_vars['WML'] ) && ($this->my_http_vars['WML'] == 1)))) )
            {
            $ret .= '<body id="search_results_body"';
            if ( !isset ( $this->my_http_vars['WML'] ) )
                {
                $ret .= ' onload="WhereAmI (\''.htmlspecialchars ( strtolower ( trim ( $this->my_http_vars['qualifier'] ) ) ).'\',\''.htmlspecialchars ( trim ( $this->my_http_vars['address'] ) ).'\')"';
                }
            $ret .= '>';

            $ret .= $this->BMLTPlugin_fast_mobile_lookup_javascript_stuff();

            $ret .= '<div id="location_finder" class="results_map_div">';
            
            $url = $this->get_plugin_path();
            
            $throbber_loc .= htmlspecialchars ( $url.'themes/'.$options['theme'].'/images/Throbber.gif' );
            
            $ret .= '<div class="throbber_div"><img id="throbber" src="'.htmlspecialchars ( $throbber_loc ).'" alt="AJAX Throbber" /></div>';
            $ret .= '</div>';
            }
        else
            {
            if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
                {
                $ret .= '<body id="search_form_body"';
                if ( !isset ( $this->my_http_vars['WML'] ) )
                    {
                    $ret .= ' onload="if( (typeof ( navigator ) == \'object\' &amp;&amp; typeof ( navigator.geolocation ) == \'object\') || (window.blackberry &amp;&amp; blackberry.location.GPSSupported) || (typeof ( google ) == \'object\' &amp;&amp; typeof ( google.gears ) == \'object\') ){document.getElementById ( \'hidden_until_js\' ).style.display = \'block\';document.getElementById ( \'hidden_until_js2\' ).style.display = \'block\';};document.getElementById(\'address_input\').value=\''.$this->process_text ( self::$local_enter_an_address ).'\'"';
                    }
                $ret .= '>';
                $ret .= '<div class="search_div"';
                if ( !isset ( $this->my_http_vars['WML'] ) )
                    {
                    $ret .= ' cellpadding="0" cellspacing="0" border="0"';
                    }
                $ret .= '>';
                $ret .= '<div class="GPS_lookup_row_div"><div>';
                if ( !isset ( $this->my_http_vars['WML'] ) )
                    {
                    $ret .= $this->BMLTPlugin_draw_map_search_form ();
                    }
                $ret .= '</div></div>';
                $ret .= '<div><div>';
                $ret .= $this->BMLTPlugin_draw_address_search_form();
                $ret .= '</div></div></div>';
                }
            else
                {
                $ret .= '<card title="'.$this->process_text (_FORM_TITLE_).'">';
                $ret .= $this->BMLTPlugin_draw_address_search_form();
                $ret .= '</card>';
                }
            }
        
        if ( !isset ( $this->my_http_vars['WML'] ) || ($this->my_http_vars['WML'] != 1) )
            {
            $ret .= '</body>';  // Wrap up the page.
            $ret .= '</html>';
            if ( isset ( $this->my_http_vars['WML'] ) && ($this->my_http_vars['WML'] == 2) )
                {
                $ret = "<"."?xml version='1.0' encoding='UTF-8' ?".">".$ret;
                header ( 'Content-type: application/xhtml+xml' );
                }
            }
        else
            {
            $ret .= '</wml>';
            header ( 'Content-type: text/vnd.wap.wml' );
            }
        
        return $ret;
        }
    
    /************************************************************************************//**
    *                                    THE CONSTRUCTOR                                    *
    *                                                                                       *
    *   \brief Constructor. Enforces the SINGLETON, and sets up the callbacks.              *
    *                                                                                       *
    *   You will need to make sure that you call this with a parent::__construct() call.    *
    ****************************************************************************************/
    function __construct ()
        {
        if ( !isset ( self::$g_s_there_can_only_be_one ) || (self::$g_s_there_can_only_be_one === null) )
            {
            self::$g_s_there_can_only_be_one = $this;
            
            $this->my_http_vars = array_merge_recursive ( $_GET, $_POST );
                
            if ( !(isset ( $this->my_http_vars['search_form'] ) && $this->my_http_vars['search_form'] )
                && !(isset ( $this->my_http_vars['do_search'] ) && $this->my_http_vars['do_search'] ) 
                && !(isset ( $this->my_http_vars['single_meeting_id'] ) && $this->my_http_vars['single_meeting_id'] ) 
                )
                {
                $this->my_http_vars['search_form'] = true;
                }
            
            $this->my_http_vars['script_name'] = preg_replace ( '|(.*?)\?.*|', "$1", $_SERVER['REQUEST_URI'] );
            $this->my_http_vars['satellite'] = $this->my_http_vars['script_name'];
            $this->my_http_vars['supports_ajax'] = 'yes';
            $this->my_http_vars['no_ajax_check'] = 'yes';

            // We need to start off by setting up our driver.
            $this->my_driver = new bmlt_satellite_controller;
            
            if ( $this->my_driver instanceof bmlt_satellite_controller )
                {
                $this->set_callbacks(); // Set up the various callbacks and whatnot.
                }
            else
                {
                echo "<!-- BMLTPlugin ERROR (__construct)! Can't Instantiate the Satellite Driver! Please reinstall the plugin! -->";
                }
            }
        else
            {
            echo "<!-- BMLTPlugin Warning: __construct() called multiple times! -->";
            }
        }
    
    /************************************************************************************//**
    *                                THE CMS-SPECIFIC FUNCTIONS                             *
    *                                                                                       *
    * These need to be overloaded by the subclasses.                                        *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *   \brief Return an HTTP path to the AJAX callback target for the mobile handler.      *
    *                                                                                       *
    *   \returns a string, containing the path. Defaults to the base URI.                   *
    ****************************************************************************************/
    protected function get_ajax_mobile_base_uri()
        {
        return $this->get_ajax_base_uri();
        }
    
    /************************************************************************************//**
    *   \brief Return an HTTP path to the AJAX callback target.                             *
    *                                                                                       *
    *   \returns a string, containing the path.                                             *
    ****************************************************************************************/
    protected function get_admin_ajax_base_uri()
        {
        return htmlspecialchars ( $this->get_ajax_base_uri() );
        }
    
    /************************************************************************************//**
    *   \brief Return an HTTP path to the basic admin form submit (action) URI              *
    *                                                                                       *
    *   \returns a string, containing the path.                                             *
    ****************************************************************************************/
    protected function get_admin_form_uri()
        {
        return null;
        }
    
    /************************************************************************************//**
    *   \brief Return an HTTP path to the AJAX callback target.                             *
    *                                                                                       *
    *   \returns a string, containing the path.                                             *
    ****************************************************************************************/
    protected function get_ajax_base_uri()
        {
        return $_SERVER['PHP_SELF'];
        }
    
    /************************************************************************************//**
    *   \brief Return an HTTP path to the plugin directory.                                 *
    *                                                                                       *
    *   \returns a string, containing the path.                                             *
    ****************************************************************************************/
    protected function get_plugin_path()
        {
        return null;
        }
    
    /************************************************************************************//**
    *   \brief This uses the WordPress text processor (__) to process the given string.     *
    *                                                                                       *
    *   This allows easier translation of displayed strings. All strings displayed by the   *
    *   plugin should go through this function.                                             *
    *                                                                                       *
    *   \returns a string, processed by WP.                                                 *
    ****************************************************************************************/
    protected function process_text (  $in_string  ///< The string to be processed.
                                    )
        {
        return htmlspecialchars ( $in_string );
        }
        
    /************************************************************************************//**
    *   \brief Sets up the admin and handler callbacks.                                     *
    ****************************************************************************************/
    protected function set_callbacks ( )
        {
        }

    /************************************************************************************//**
    *   \brief This gets the admin options from the database (allows CMS abstraction).      *
    *                                                                                       *
    *   \returns an associative array, with the option settings.                            *
    ****************************************************************************************/
    protected function cms_get_option ( $in_option_key    ///< The name of the option
                                        )
        {
        return null;
        }
    
    /************************************************************************************//**
    *   \brief This gets the admin options from the database (allows CMS abstraction).      *
    ****************************************************************************************/
    protected function cms_set_option ( $in_option_key,   ///< The name of the option
                                        $in_option_value  ///< the values to be set (associative array)
                                        )
        {
        }
    
    /************************************************************************************//**
    *   \brief Deletes a stored option (allows CMS abstraction).                            *
    ****************************************************************************************/
    protected function cms_delete_option ( $in_option_key   ///< The name of the option
                                        )
        {
        }

    /************************************************************************************//**
    *   \brief This gets the page meta for the given page. (allows CMS abstraction).        *
    *                                                                                       *
    *   \returns a mixed type, with the meta data                                           *
    ****************************************************************************************/
    protected function cms_get_post_meta (  $in_page_id,    ///< The ID of the page/post
                                            $in_settings_id ///< The ID of the meta tag to fetch
                                            )
        {
        return null;
        }

    /************************************************************************************//**
    *   \brief This function fetches the settings ID for a page (if there is one).          *
    *                                                                                       *
    *   If $in_check_mobile is set to true, then ONLY a check for mobile support will be    *
    *   made, and no other shortcodes will be checked.                                      *
    *                                                                                       *
    *   \returns a mixed type, with the settings ID.                                        *
    ****************************************************************************************/
    protected function cms_get_page_settings_id ($in_content,               ///< Required (for the base version) content to check.
                                                 $in_check_mobile = false   ///< True if this includes a check for mobile. Default is false.
                                                )
        {
        $my_option_id = null;
        
        if ( $in_content )  // The default version requires content.
            {
            // We only return a mobile ID if we have the shortcode, we're asked for it, we're not already handling mobile, and we have a mobile UA.
            if ( $in_check_mobile && !isset ( $this->my_http_vars['BMLTPlugin_mobile'] ) && (self::mobile_sniff_ua ($this->my_http_vars) != 'xhtml') && ($params = self::get_shortcode ( $in_content, 'bmlt_mobile')) ) 
                {
                if ( $params === true ) // If no mobile settings number was provided, we use the default.
                    {
                    $options = $this->getBMLTOptions ( 1 );
                    $my_option_id = strval ( $options['id'] );
                    }
                else
                    {
                    $my_option_id = $params;
                    }
                }
            elseif( !$in_check_mobile ) // A mobile check ignores the rest.
                {
                if ( ($params = self::get_shortcode ( $in_content, 'bmlt_simple')) || ($params = self::get_shortcode ( $in_content, 'bmlt_changes')) ) 
                    {
                    $param_array = explode ( '##-##', $params );
                    
                    if ( is_array ( $param_array ) && (count ( $param_array ) > 1) )
                        {
                        $my_option_id = $param_array[0];
                        }
                    }
        
                if ($params = self::get_shortcode ( $in_content, 'bmlt') ) 
                    {
                    $my_option_id = ( $params !== true ) ? $params : $my_option_id;
                    }
        
                if ($params = self::get_shortcode ( $in_content, 'bmlt_map') ) 
                    {
                    $my_option_id = ( $params !== true ) ? $params : $my_option_id;
                    }
                }
            }
        
        return $my_option_id;
        }

    /************************************************************************************//**
    *                                  THE CMS CALLBACKS                                    *
    ****************************************************************************************/
        
    /************************************************************************************//**
    *   \brief Presents the admin page.                                                     *
    ****************************************************************************************/
    function admin_page ( )
        {
        }
       
    /************************************************************************************//**
    *   \brief Presents the admin menu options.                                             *
    *                                                                                       *
    * NOTE: This function requires WP. Most of the rest can probably be more easily         *
    * converted for other CMSes.                                                            *
    ****************************************************************************************/
    function option_menu ( )
        {
        }
        
    /************************************************************************************//**
    *   \brief Echoes any necessary head content.                                           *
    ****************************************************************************************/
    function standard_head ( )
        {
        }
        
    /************************************************************************************//**
    *   \brief Echoes any necessary head content for the admin.                             *
    ****************************************************************************************/
    function admin_head ( )
        {
        }
};
?>