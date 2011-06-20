<?php
/****************************************************************************************//**
* \file unit_test.php																		*
* \brief A unit test harness for the BMLTPlugin class.						                *
* \version 1.0.8																			*
    
    This file is part of the BMLT Common Satellite Base Class Project. The project GitHub
    page is available here: https://github.com/MAGSHARE/BMLT-Common-CMS-Plugin-Class
    
    This file is part of the Basic Meeting List Toolbox (BMLT).
    
    Find out more at: http://magshare.org/bmlt
    
    BMLT is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    BMLT is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    You should have received a copy of the GNU General Public License
    along with this code.  If not, see <http://www.gnu.org/licenses/>.
********************************************************************************************/

/********************************************************************************************
*										UNIT TESTING HARNESS								*
*																							*
* This code is used for testing the class by allowing a direct call of the file. It will be	*
* disabled in actual implementation, so calls to the file will return nothing.				*
********************************************************************************************/

require_once ( 'bmlt-unit-test-satellite-plugin.php' );

/// This is an ID for a specific meeting (with some changes) for the meeting changes test.
define ( 'U_TEST_MEETING_ID', 734 );

/****************************************************************************************//**
*	\brief Runs the unit tests.																*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test()
{
	$header = u_test_header();  // Gives the program a chance to "die out".
	
	if ( $header )
	    {
        // We return a fully-qualified XHTML 1.0 Strict page.
        $ret = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"><head><meta http-equiv="content-type" content="text/html; charset=utf-8" />';
        $ret .= $header;
        $ret .= '<style type="text/css">';
        $ret .= '*{margin:0;padding:0}';
        $ret .= 'body{font-family:Courier;font-size:small}';
        $ret .= '.test_container_div{padding-left:20px}';
        $ret .= '.return_button,.utest_input_form_container_div,.centered_div{text-align:center;margin:8px}';
        $ret .= '.return_button{font-size:large}';
        $ret .= '.utest_input_div{width:50%;text-align:left;margin-left:auto;margin-right:auto}';
        $ret .= '.utest_input_textarea{padding:4px;color:#339;border:1px solid #339}';
        $ret .= '.mobile_list_div { text-align:center }';
        $ret .= '.mobile_list_div_line { width: 250px;margin-top:4px;margin-bottom:4px;text-align:left;margin-left:auto;margin-right:auto }';
        $ret .= '.mobile_list_div_line label { margin-left: 8px }';
        $ret .= '</style>';
        $ret .= '<script type="text/javascript">';
        $ret .= "function utest_onsubmit(){var elem=document.getElementById('wml_d');if(document.getElementById('mobile_simulation_smartphone').checked){elem.value='1';elem.name='simulate_smartphone'}else{if(document.getElementById('mobile_simulation_wml_1').checked){elem.value='1';elem.name='WML'}else{if(document.getElementById('mobile_simulation_wml_2').checked){elem.value='2';elem.name='WML'}}};return true}";
        $ret .= "function utest_preset_text(in_value){document.getElementById('utest_string').innerHTML=in_value;if(/.*?bmlt_mobile/.exec(in_value)){document.getElementById('mobile_simulation_smartphone').checked=true}}";
        $ret .= '</script>';
        $ret .= '</head><body>';    // Open the page
        $ret .= '<div class="return_button"><a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'">Return to Start</a></div>';
        $ret .= '<div class="return_button"><a href="'.htmlspecialchars($_SERVER['PHP_SELF']).'?utest_string=clear_session">Clear Session</a></div>';
        $ret .= u_test_body();
        $ret .= '</body></html>';	// Wrap up the page.
	    }
	
	return $ret;
}

/****************************************************************************************//**
*	\brief Decides which operation to perform, based on the utest_string parameter.         *
*                                                                                           *
*   \returns a string. Either 'admin', 'render' or null.                                    *
********************************************************************************************/
function u_test_operation()
{
    $ret = null;
    
    global $BMLTPluginOp;
    
    $oper_text = u_test_get_string();
    
    if ( strtolower ( $oper_text ) == 'admin' )
        {
        $ret = 'admin';
        }
    elseif ( strtolower ( $oper_text ) == 'clear_session' )
        {
        $ret = 'clear_session';
        }
    elseif ( $BMLTPluginOp->get_shortcode('bmlt', $oper_text ) )
        {
        $ret = 'render-old';
        }
    elseif ( isset ( $oper_text ) || count ( $_GET ) || count ( $_POST ) )
        {
        $ret = 'render-new';
        }
    
    return $ret;
}

/****************************************************************************************//**
*	\brief Returns the string provided in the text box.                                     *
*                                                                                           *
*   \returns a string.                                                                      *
********************************************************************************************/
function u_test_get_string()
{
    $ret = isset ( $_GET['utest_string'] ) ? trim ( $_GET['utest_string'] ) : (isset ( $_POST['utest_string'] ) ? trim ( $_POST['utest_string'] ) : null);
    
    return $ret;
}

/****************************************************************************************//**
*	\brief Return unit test body content.													*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test_header()
{
    $ret = '';
    global $BMLTPluginOp;
    
    switch ( u_test_operation() )
        {
        case 'admin':
            $ret .= '<title>Unit Test: Administration</title>';
            $ret .= $BMLTPluginOp->admin_head ( );
        break;
        
        case 'render-old':
        case 'render-new':
            $parse_string = u_test_get_string();
            $ret .= '<title>Unit Test: Display</title>';
            $ret .= $BMLTPluginOp->standard_head ( $parse_string );
        break;
        
        case 'clear_session':
            session_start();
            session_unset();
            session_destroy();
        
        default:
            $ret .= '<title>BMLTPlugin Class Unit Test</title>';
        break;
        }
    
    return $ret;
}

/****************************************************************************************//**
*	\brief Return unit test header content.													*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test_body()
{
    $ret = '';

    switch ( u_test_operation() )
        {
        case 'admin':
            $ret = u_test_admin();
        break;
        
        case 'render-old':
        case 'render-new':
            $ret = u_test_render();
        break;
        
        default:
            $ret = u_test_form();
        break;
        }
    
    return $ret;
}

/****************************************************************************************//**
*	\brief Returns the XHTML for the default unit test form.								*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test_form()
{
    global $BMLTPluginOp;
        
	$ret = '<div class="return_button"><a href="http://'.$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME'].'?utest_string=admin">Admin Page</a></div>';
    $ret .= '<div class="utest_input_form_container_div">';
        $ret .= '<form onsubmit="utest_onsubmit()" class="utest_input_form" method="get" action="'.htmlspecialchars ( $_SERVER['PHP_SELF'] ).'">';
            $ret .= '<div class="utest_input_div">';
                $ret .= '<div class="centered_div">';
                $ret .= '<h1>Enter the Test String</h1>';
                    $ret .= '<div class="preset_list_div">';
                        $ret .= '<label for="preset">Preset Text:<select id="preset" onchange="utest_preset_text(this.value)">';
                        $ret .= '<option value="" disabled="disabled">Select A Preset String</option>';
                        $ret .= '<option value="[[bmlt]]">Standard BMLT (Brackets)</option>';
                        $ret .= '<option value="<!--bmlt-->">Standard BMLT (Comment)</option>';
                        $ret .= '<option value="[[bmlt_mobile]]">BMLT Mobile (Brackets)</option>';
                        $ret .= '<option value="<!--bmlt_mobile-->">BMLT Mobile (Comment)</option>';
                        $ret .= '<option value="[[bmlt_simple(switcher=GetSearchResults&block_mode=1&meeting_key=location_city_subsection&meeting_key_value=Brooklyn&weekdays[]=7)]]">BMLT Simple (Brackets -Brooklyn, Saturday)</option>';
                        $ret .= '<option value="<!--bmlt_simple(switcher=GetSearchResults&block_mode=1&meeting_key=location_city_subsection&meeting_key_value=Brooklyn&weekdays[]=1)-->">BMLT Simple (Comment -Brooklyn, Sunday)</option>';
                        $ret .= '<option value="[[bmlt_simple(switcher=GetFormats)]]">BMLT Simple (Brackets -Formats)</option>';
                        $ret .= '<option value="<!--bmlt_simple(switcher=GetFormats)-->">BMLT Simple (Comment -Formats)</option>';
                        $ret .= '</select>';
                    $ret .= '</div>';
                $ret .= '</div>';
                $ret .= '<textarea style="width:100%" rows="10" class="utest_input_textarea" id="utest_string" name="utest_string">';
                $ret .= '</textarea>';
                $ret .= '<input type="hidden" id="wml_d" value="" />';
                $ret .= '<div class="mobile_list_div">';
                    $ret .= '<div class="mobile_list_div_line"><h2>Simulate Mobile</h2></div>';
                    $ret .= '<div class="mobile_list_div_line"><input checked="checked" name="mobile_simulation" id="mobile_simulation_none" type="radio" value="" /><label for="mobile_simulation_none">No Mobile Simulation</label></div>';
                    $ret .= '<div class="mobile_list_div_line"><input name="mobile_simulation" id="mobile_simulation_smartphone" type="radio" value="smartphone" /><label for="mobile_simulation_smartphone">Simulate Smartphone</label></div>';
                    $ret .= '<div class="mobile_list_div_line"><input name="mobile_simulation" id="mobile_simulation_wml_1" type="radio" value="WML1" /><label for="mobile_simulation_wml_1">Simulate WML 1</label></div>';
                    $ret .= '<div class="mobile_list_div_line"><input name="mobile_simulation" id="mobile_simulation_wml_2" type="radio" value="WML2" /><label for="mobile_simulation_wml_2">Simulate WML 2</label></div>';
                $ret .= '</div>';
                $ret .= '<div class="centered_div"><input type="submit" value="Submit" /><script type="text/javascript">document.getElementById(\'utest_string\').select()</script></div>';
            $ret .= '</div>';
        $ret .= '</form>';
    $ret .= '</div>';
    
    return $ret;
}

/****************************************************************************************//**
*	\brief Returns the XHTML for the default unit test admin page.							*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test_admin()
{
    global $BMLTPluginOp;
        
    return $BMLTPluginOp->return_admin_page();
}

/****************************************************************************************//**
*	\brief Returns the XHTML for the default unit test rendered BMLT instance.				*
*																							*
*	\returns A string. The XHTML to be displayed.											*
********************************************************************************************/
function u_test_render()
{
    global $BMLTPluginOp;
    
    $ret = '<div class="utest_render_container_div">';
    
    $str = u_test_get_string();
    
//die ( '<pre>'.htmlspecialchars ( print_r ( $str, true )).'</pre>' );
    
    $ret .= $BMLTPluginOp->content_filter ( u_test_get_string() );
    
    $ret .= '</div>';
    
    return $ret;
}

/********************************************************************************************
*										UNIT TESTING MAIN									*
/*******************************************************************************************/

// This calls the unit test.
echo u_test();
?>