/****************************************************************************************//**
* \file map_search.js																        *
* \brief Javascript functions for the new default implementation.                           *
*                                                                                           *
*   This class implements the entire new default search algorithm (basic/advanced/text/map) *
*   in a manner that exports all the functionality to the client. It uses the JSON API      *
*   to communicate with the root server.                                                    *
*                                                                                           *
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
                            in_initial_view,        ///< This contains the initial view, as specified in the settings.
                            in_initial_lat,         ///< The initial latitude for the map.
                            in_initial_long,        ///< The initial longitude for the map.
                            in_initial_zoom,        ///< The initial zoom level for the map.
                            in_initial_text,        ///< If there is any initial text to be displayed, it should be here.
                            in_checked_location,    ///< If the "Location" checkbox should be checked, this should be TRUE.
                            in_single_meeting_id    ///< If this has an integer number in it, it will display the details for a single meeting.
                            )
{
	/****************************************************************************************
	*									  CLASS DATA MEMBERS								*
	****************************************************************************************/
    
    /// These are the state variables.
    var m_uid = null;                       ///< The unique identifier. This won't be changed after the construction.
    var m_current_view = null;              ///< One of 'map', 'text', 'advanced', 'advanced map', 'advanced text'. It will change ast the object state changes.
    var m_current_long = null;              ///< The current map longitude. It will change as the map state changes.
    var m_current_lat = null;               ///< The current map latitude. It will change as the map state changes.
    var m_current_zoom = null;              ///< The current map zoom. It will change as the map state changes.
    var m_single_meeting_id = null;         ///< This will contain the ID of any single meeting being displayed.
    
    /// These variables hold quick references to the various elements of the screen.
    var m_container_div = null;             ///< This is the main outer container. It also contains the script.
    var m_display_div = null;               ///< This is the div where everything happens.
    var m_header_div = null;                ///< This will contain the header components.
    var m_content_div = null;               ///< This will contain the content (working) components.
    
    var m_basic_advanced_switch_div = null; ///< This will contain the "basic and "advanced" switch links.
    var m_map_text_switch_div = null;       ///< This will contain the 'Map' and 'Text' switch links.
    var m_basic_switch_a = null;            ///< This is the "basic" anchor
    var m_advanced_switch_a = null;         ///< This is the "advanced" anchor
    var m_map_switch_a = null;              ///< This is the "map" anchor
    var m_text_switch_a = null;             ///< This is the "text" anchor
    
    var m_map_div = null;                   ///< This will contain the map.
    var m_main_map = null;                  ///< This is the actual Google Maps instance.
    
    var m_text_div = null;                  ///< This will contain the text div.
    var m_text_input = null;                ///< This is the text search input element.
    var m_location_checkbox = null;         ///< This is the "This is a Location" checkbox.
    
    var m_single_meeting_display_div = null;    ///< This is the div that will be used to display the details of a single meeting.
        
    /****************************************************************************************
    *								  PRIVATE CLASS FUNCTIONS							    *
    ****************************************************************************************/
    
    /****************************************************************************************
    *################################# INITIAL SETUP ROUTINES ##############################*
    ****************************************************************************************/
    
    /************************************************************************************//**
    *	\brief Sets up all the various DOM elements that comprise the search screen.        *
    ****************************************************************************************/
    this.buildDOMTree = function ()
        {
        this.m_display_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_display_div.className = 'bmlt_nouveau_div';
        this.m_display_div.id = this.m_uid;
        
        this.m_header_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_header_div.className = 'bmlt_nouveau_header_div';
        
        this.m_content_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_content_div.className = 'bmlt_nouveau_content_div';
        
        this.buildDOMTree_Basic_Advanced_Switch();
        this.buildDOMTree_Map_Text_Switch();
        
        this.buildDOMTree_Map_Div();
        
        this.m_display_div.appendChild ( this.m_header_div );
        this.m_display_div.appendChild ( this.m_content_div );
        this.m_container_div.appendChild ( this.m_display_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets up the "BASIC/ADVANCED" tab switch div.                            *
    ****************************************************************************************/
    this.buildDOMTree_Basic_Advanced_Switch = function ()
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
        
        this.setBasicAdvancedSwitch();
        
        this.m_header_div.appendChild ( this.m_basic_advanced_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "BASIC/ADVANCED" tab switch div. It actually      *
    *          changes the state of the anchors, so it is more than just a CSS class change.*
    ****************************************************************************************/
    this.setBasicAdvancedSwitch = function()
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
    *	\brief This sets up the "MAP/TEXT" tab switch div.                                  *
    ****************************************************************************************/
    this.buildDOMTree_Map_Text_Switch = function ( in_container_node   ///< This holds the node that will contain the switch.
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
        
        this.setMapTextSwitch();
        
        this.m_header_div.appendChild ( this.m_map_text_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setMapTextSwitch = function()
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
    
    /************************************************************************************//**
    *	\brief This constructs the map div (used by the map search).                        *
    ****************************************************************************************/
    this.buildDOMTree_Map_Div = function ()
        {
        this.m_map_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_map_div.className = 'bmlt_nouveau_map_div';
        this.loadMap();
        this.m_content_div.appendChild ( this.m_map_div );
        };
    
    /************************************************************************************//**
    *	\brief This constructs the text div (used by the text search).                      *
    ****************************************************************************************/
    this.buildDOMTree_Text_Div = function ()
        {
        this.m_text_div = null;
        this.m_text_input = null;
        this.m_location_checkbox = null;
        };
    
    /****************************************************************************************
    *###################################### MAP ROUTINES ###################################*
    ****************************************************************************************/

    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
	this.loadMap = function ( )
	{
        if ( this.m_map_div )
            {
            var myOptions = {
                            'center': new google.maps.LatLng ( this.m_current_lat, this.m_current_long ),
                            'zoom': this.m_current_zoom,
                            'mapTypeId': google.maps.MapTypeId.ROADMAP,
                            'mapTypeControlOptions': { 'style': google.maps.MapTypeControlStyle.DROPDOWN_MENU },
                            'zoomControl': true,
                            'mapTypeControl': true,
                            'disableDoubleClickZoom' : true,
                            'draggableCursor': "pointer",
                            'scaleControl' : true
                            };

            var	pixel_width = this.m_map_div.offsetWidth;
            var	pixel_height = this.m_map_div.offsetHeight;
            
            if ( (pixel_width < 640) || (pixel_height < 640) )
                {
                myOptions.scrollwheel = true;
                myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.SMALL };
                }
            else
                {
                myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };
                };

            this.m_main_map = new google.maps.Map ( this.m_map_div, myOptions );
            
            if ( this.m_main_map )
                {
                this.m_main_map.response_object = null;
                this.m_main_map.center_marker = null;
                this.m_main_map.geo_width = null;
                google.maps.event.addListener ( this.m_main_map, 'click', this.mapClicked );
                    
                // Options for circle overlay object

                var circle_options =   {
                                'center': this.m_main_map.getCenter(),
                                'fillColor': "#999",
                                'radius':1000,
                                'fillOpacity': 0.25,
                                'strokeOpacity': 0.0,
                                'map': null,
                                'clickable': false
                                };

                this.m_main_map._circle_overlay = new google.maps.Circle(circle_options);
                };
            };
	};

    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.mapClicked = function ( in_event ///< The mouse event that caused the click.
                                )
    {
    alert ( 'MAP CLICKED' );
    };
    
    /****************************************************************************************
    *								        CONSTRUCTOR							            *
    ****************************************************************************************/
    
    this.m_uid = in_unique_id;
    this.m_current_view = in_initial_view;
    this.m_current_long = in_initial_long;
    this.m_current_lat = in_initial_lat;
    this.m_current_zoom = in_initial_zoom;
    this.m_single_meeting_id = in_single_meeting_id;

    this.m_container_div = document.getElementById ( this.m_uid + '_container' );   ///< This is the main outer container.
    
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'text':            // These are OK.
        case 'advanced text':
        break;
        
        
        case 'advanced':        // These are the same for this implementation
        case 'advanced map':
            this.m_current_view = 'advanced map';
        break;
        
        default:    // The default is map. That includes a "server select."
            this.m_current_view = 'map';
        break;
        };
    
    this.buildDOMTree();
};

/********************************************************************************************
*								  PUBLIC CLASS FUNCTIONS									*
********************************************************************************************/
    
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.BasicButtonHit = function()
    {
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'advanced map':
            this.m_current_view = 'map';
        break;
        
        case 'advanced text':
            this.m_current_view = 'text';
        break;
        };
        
    this.setBasicAdvancedSwitch();
    };

/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.AdvancedButtonHit = function()
    {
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'map':
            this.m_current_view = 'advanced map';
        break;
        
        case 'text':
            this.m_current_view = 'advanced text';
        break;
        };
        
    this.setBasicAdvancedSwitch();
    };
        
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.MapButtonHit = function( in_adv_basic    ///< If this is "advanced," then we apply to the "advanced" divs.
                                                    )
    {
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'text':
            this.m_current_view = 'map';
        break;
        
        case 'advanced text':
            this.m_current_view = 'advanced map';
        break;
        };
        
    this.setMapTextSwitch();
    };
        
/****************************************************************************************//**
*	\brief 
********************************************************************************************/
    
NouveauMapSearch.prototype.TextButtonHit = function( in_adv_basic   ///< If this is "advanced," then we apply to the "advanced" divs.
                                                    )
    {
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'map':
            this.m_current_view = 'text';
        break;
        
        case 'advanced map':
            this.m_current_view = 'advanced text';
        break;
        };
        
    this.setMapTextSwitch();
    };
