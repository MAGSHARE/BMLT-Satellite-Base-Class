/****************************************************************************************//**
* \file map_search.js																        *
* \brief Javascript functions for the new map search implementation.                        *
*   \version 2.0                                                                            *
*                                                                                           *
*   This file is part of the BMLT Common Satellite Base Class Project. The project GitHub   *
*   page is available here: https://github.com/MAGSHARE/BMLT-Common-CMS-Plugin-Class        *
*                                                                                           *
*   This file is part of the Basic Meeting List Toolbox (BMLT).                             *
*                                                                                           *
*   Find out more at: http://magshare.org/bmlt                                              *
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

/****************************************************************************************//**
*	\brief  
********************************************************************************************/

function NouveauMapSearch ( in_unique_id,           ///< The UID of the container (will be used to get elements)
                            in_settings_id,         ///< The settings ID (used to propagate the settings ID).
                            in_initial_view,        ///< This contains the initial view, as specified in the settings.
                            in_initial_text,        ///< If there is any initial text to be displayed, it should be here.
                            in_initial_long,        ///< The initial longitude for the map.
                            in_initial_lat,         ///< The initial latitude for the map.
                            in_initial_zoom,        ///< The initial zoom level for the map.
                            in_checked_location     ///< If the "Location" checkbox should be checked, this should be TRUE.
                            )
{
	/****************************************************************************************
	*									  CLASS VARIABLES									*
	****************************************************************************************/
    
    /// These are the state variables.
    var m_uid = null;           ///< The unique identifier. This won't be changed after the construction.
    var m_settings_id = null;   ///< The settings ID. This won't be changed after the construction.
    var m_current_view = null;  ///< One of 'map', 'text', 'advanced', 'advanced map', 'advanced text'. It will change ast the object state changes.
    var m_current_long = null;  ///< The current map longitude. It will change as the map state changes.
    var m_current_lat = null;   ///< The current map latitude. It will change as the map state changes.
    var m_current_zoom = null;  ///< The current map zoom. It will change as the map state changes.
    
    /// These variables hold quick references to the various elements of the screen.
    var m_container_div = null;             ///< This is the main outer container. It also contains the script.
    var m_display_div = null;               ///< This is the div where everything happens.
    
    var m_basic_advanced_switch_div = null; ///< This will contain the "basic and "advanced" switch links.
    var m_map_text_switch_div = null;       ///< This will contain the 'Map' and 'Text' switch links.
    var m_basic_switch_a = null;            ///< This is the "basic" anchor
    var m_advanced_switch_a = null;         ///< This is the "advanced" anchor
    var m_map_switch_a = null;              ///< This is the "map" anchor
    var m_text_switch_a = null;             ///< This is the "text" anchor
    
    /****************************************************************************************
    *								  PRIVATE CLASS FUNCTIONS							    *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.BuildDOMTree = function ()
        {
        this.m_display_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_display_div.className = 'bmlt_nouveau_div';
        this.m_display_div.id = this.m_uid;
        
        this.BuildDOMTree_Basic_Advanced_Switch();
        this.BuildDOMTree_Map_Text_Switch();
        
        this.m_container_div.appendChild ( this.m_display_div );
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.BuildDOMTree_Basic_Advanced_Switch = function ()
        {
        this.m_basic_advanced_switch_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_basic_advanced_switch_div.className = 'bmlt_nouveau_switcher_div';
        
        this.m_basic_switch_a = document.createElement ( 'a' );      // Create the basic switch anchor element.
        var txt = document.createTextNode(g_NouveauMapSearch_basic_name_string);
        this.m_basic_switch_a.appendChild ( txt );
        this.m_basic_advanced_switch_div.appendChild ( this.m_basic_switch_a );
        
        this.m_advanced_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        txt = document.createTextNode(g_NouveauMapSearch_advanced_name_string);
        this.m_advanced_switch_a.appendChild ( txt );
        this.m_basic_advanced_switch_div.appendChild ( this.m_advanced_switch_a );
        
        this.SetBasicAdvancedSwitch();
        
        this.m_display_div.appendChild ( this.m_basic_advanced_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.SetBasicAdvancedSwitch = function()
        {
        if ( (this.m_current_view == 'text') || (this.m_current_view == 'map') )
            {
            this.m_basic_switch_a.className = 'bmlt_nouveau_basic_a_selected';
            this.m_advanced_switch_a.className = 'bmlt_nouveau_advanced_a';
            this.m_advanced_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.AdvancedButtonHit()' );
            this.m_basic_switch_a.removeAttribute ( 'href' );
            }
        else
            {
            this.m_basic_switch_a.className = 'bmlt_nouveau_basic_a';
            this.m_advanced_switch_a.className = 'bmlt_nouveau_advanced_a_selected';
            this.m_basic_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.BasicButtonHit()' );
            this.m_advanced_switch_a.removeAttribute ( 'href' );
            };
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.BuildDOMTree_Map_Text_Switch = function ( in_container_node   ///< This holds the node that will contain the switch.
                                                  )
        {
        this.m_map_text_switch_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_map_text_switch_div.className = 'bmlt_nouveau_switcher_div';
        
        this.m_map_switch_a = document.createElement ( 'a' );      // Create the basic switch anchor element.
        var txt = document.createTextNode(g_NouveauMapSearch_map_name_string);
        this.m_map_switch_a.appendChild ( txt );
        this.m_map_text_switch_div.appendChild ( this.m_map_switch_a );
        
        this.m_text_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        txt = document.createTextNode(g_NouveauMapSearch_text_name_string);
        this.m_text_switch_a.appendChild ( txt );
        this.m_map_text_switch_div.appendChild ( this.m_text_switch_a );
        
        this.SetMapTextSwitch();
        
        this.m_display_div.appendChild ( this.m_map_text_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.SetMapTextSwitch = function()
        {
        if ( (this.m_current_view == 'map') || (this.m_current_view == 'advanced map') )
            {
            this.m_map_switch_a.className = 'bmlt_nouveau_map_a_selected';
            this.m_text_switch_a.className = 'bmlt_nouveau_text_a';
            this.m_text_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.TextButtonHit()' );
            this.m_map_switch_a.removeAttribute ( 'href' );
            }
        else
            {
            this.m_map_switch_a.className = 'bmlt_nouveau_map_a';
            this.m_text_switch_a.className = 'bmlt_nouveau_text_a_selected';
            this.m_map_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.MapButtonHit()' );
            this.m_text_switch_a.removeAttribute ( 'href' );
            };
        };
        
    /****************************************************************************************
    *								        INITIAL RUN							            *
    ****************************************************************************************/
    
    this.m_uid = in_unique_id;               ///< The unique identifier. This won't be changed after the construction.
    this.m_settings_id = in_settings_id;     ///< The settings ID. This won't be changed after the construction.
    this.m_current_view = in_initial_view;   ///< One of 'map', 'text', 'advanced', 'advanced map', 'advanced text'. It will change ast the object state changes.
    this.m_current_long = in_initial_long;   ///< The current map longitude. It will change as the map state changes.
    this.m_current_lat = in_initial_lat;     ///< The current map latitude. It will change as the map state changes.
    this.m_current_zoom = in_initial_zoom;   ///< The current map zoom. It will change as the map state changes.

    this.m_container_div = document.getElementById ( this.m_uid + '_container' );    ///< This is the main outer container. It also contains the script.
    
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'text':            // These are OK.
        case 'advanced text':
        break;
        
        
        case 'advanced':        // These are the same for this implementation
        case 'advanced map':
            this.m_current_view = 'advanced map';
        break;
        
        default:    // The default is map
            this.m_current_view = 'map';
        break;
        };
    
    this.BuildDOMTree();
};

/********************************************************************************************
*								  PUBLIC CLASS FUNCTIONS									*
********************************************************************************************/
    
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.BasicButtonHit = function()
    {
    alert ( 'BASIC' );
    };

/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.AdvancedButtonHit = function()
    {
    alert ( 'ADVANCED' );
    };
        
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.MapButtonHit = function( in_adv_basic    ///< If this is "advanced," then we apply to the "advanced" divs.
                                                    )
    {
    alert ( 'MAP' );
    };
        
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.TextButtonHit = function( in_adv_basic   ///< If this is "advanced," then we apply to the "advanced" divs.
                                                    )
    {
    alert ( 'TEXT' );
    };
