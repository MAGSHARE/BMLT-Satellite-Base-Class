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
                            in_root_server_uri,     ///< The base root server URI,
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
    var m_current_view = null;              ///< One of 'map', 'text', 'advanced', 'advanced map', 'advanced text'. It will change as the object state changes.
    var m_current_long = null;              ///< The current map longitude. It will change as the map state changes.
    var m_current_lat = null;               ///< The current map latitude. It will change as the map state changes.
    var m_current_zoom = null;              ///< The current map zoom. It will change as the map state changes.
    var m_root_server_uri = null;           ///< A string, containing the URI of the root server. It will not change after construction.
    var m_initial_text = null;              ///< This will contain any initial text for the search text box.
    var m_checked_location = false;         ///< This is set at construction. If true, then the "Location" checkbox will be checked at startup.
    var m_single_meeting_id = null;         ///< This will contain the ID of any single meeting being displayed.
    
    /// These variables hold quick references to the various elements of the screen.
    var m_container_div = null;             ///< This is the main outer container. It also contains the script.
    var m_display_div = null;               ///< This is the div where everything happens.
    
    var m_search_spec_switch_div = null;    ///< This holds the switch between the spec and the results.
    var m_search_spec_switch_a = null;      ///< This holds the switch anchor element for the spec..
    var m_search_results_switch_a = null;   ///< This holds the switch anchor element for the results.
    
    var m_search_spec_div = null;           ///< This holds the search specification.
    var m_search_results_div = null;        ///< This holds the search results.
    
    var m_basic_advanced_switch_div = null; ///< This will contain the "basic and "advanced" switch links.
    var m_map_text_switch_div = null;       ///< This will contain the 'Map' and 'Text' switch links.
    var m_advanced_switch_a = null;         ///< This is the "advanced" anchor
    var m_basic_switch_a = null;            ///< This is the "basic" anchor
    var m_map_switch_a = null;              ///< This is the "map" anchor
    var m_text_switch_a = null;             ///< This is the "text" anchor
    var m_advanced_section_div = null;      ///< This is the advanced display section
    
    var m_advanced_go_a = null;             ///< This will be a "GO" button in the advanced search section.
    
    var m_map_div = null;                   ///< This will contain the map.
    var m_main_map = null;                  ///< This is the actual Google Maps instance.
    var m_search_radius = -10;              ///< This is the chosen search radius (if the advanced search is open and the map is open).
    
    var m_text_div = null;                  ///< This will contain the text div.
    var m_text_inner_div = null;            ///< This will be an inner container, allowing more precise positioning.
    var m_text_item_div = null;             ///< This contains the text item.
    var m_text_input = null;                ///< This is the text search input element.
    var m_text_input_label = null;          ///< This is the text input label.
    var m_text_loc_checkbox_div = null;     ///< This contains the location checkbox item.
    var m_location_checkbox = null;         ///< This is the "This is a Location" checkbox.
    var m_location_checkbox_label = null;   ///< This is the "This is a Location" checkbox label.
    var m_text_go_button_div = null;        ///< This contains the go button item.
    var m_text_go_a = null;                 ///< This is the text div "GO" button (Anchor element).
    
    /// These all contain the various Advanced sub-sections
    
    /// The Map Options
    var m_advanced_map_options_div = null;

    /// Weekdays
    var m_advanced_weekdays_div = null;
    var m_advanced_weekdays_header_div = null;
    var m_advanced_weekdays_disclosure_a = null;
    var m_advanced_weekdays_content_div = null;
    var m_advanced_weekdays_shown = false;
    
    /// Meeting Formats
    var m_advanced_formats_div = null;
    var m_advanced_formats_header_div = null;
    var m_advanced_formats_disclosure_a = null;
    var m_advanced_formats_content_div = null;
    var m_advanced_formats_shown = false;
    
    /// Service Bodies
    var m_advanced_service_bodies_div = null;
    var m_advanced_service_bodies_header_div = null;
    var m_advanced_service_bodies_disclosure_a = null;
    var m_advanced_service_bodies_content_div = null;
    var m_advanced_service_bodies_shown = false;
    
    /// The GO Button
    var m_advanced_go_button_div = null;
    
    var m_single_meeting_display_div = null;    ///< This is the div that will be used to display the details of a single meeting.
    
    var m_search_results = null;            ///< If there are any search results, they are kept here (JSON object).
    var m_search_results_shown = false;     ///< If this is true, then the results div is displayed.
    
    var m_ajax_request = null;              ///< This is used to handle AJAX calls.
        
    /****************************************************************************************
    *								  INTERNAL CLASS FUNCTIONS							    *
    ****************************************************************************************/
    
    /****************************************************************************************
    *################################# INITIAL SETUP ROUTINES ##############################*
    ****************************************************************************************/
    
    /************************************************************************************//**
    *	\brief Sets up all the various DOM elements that comprise the search screen.        *
    ****************************************************************************************/
    this.buildDOMTree = function ()
        {
        // First, create and set up the entire screen.
        this.m_display_div = document.createElement ( 'div' );
        this.m_display_div.className = 'bmlt_nouveau_div';
        this.m_display_div.id = this.m_uid;
        
        // Next, create the spec/results switch.
        this.buildDOMTree_ResultsSpec_Switch();
        
        // Next, create the search specification div.
        this.m_search_spec_div = document.createElement ( 'div' );
        this.m_search_spec_div.className = 'bmlt_nouveau_search_spec_div';
        
        this.buildDOMTree_Map_Text_Switch();
        
        this.buildDOMTree_Spec_Map_Div();
        this.buildDOMTree_Text_Div();
        
        this.buildDOMTree_Basic_Advanced_Switch();
        this.buildDOMTree_AdvancedSection();
        
        this.setBasicAdvancedSwitch();
        this.setMapTextSwitch();
        
        this.m_display_div.appendChild ( this.m_search_spec_div );
        
        // Next, create the search results div.
        this.m_search_results_div = document.createElement ( 'div' );
        this.m_search_results_div.className = 'bmlt_nouveau_search_results_div';
        
        this.m_display_div.appendChild ( this.m_search_results_div );

        this.setDisplayedSearchResults();   // Make sure that the proper div is displayed.
        
        // Finally, set everything into the container.
        this.m_container_div.appendChild ( this.m_display_div );
        };
    
    /****************************************************************************************
    *######################## SET UP SEARCH SPEC AND RESULTS SWITCH ########################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This sets up the "MAP/TEXT" tab switch div.                                  *
    ****************************************************************************************/
    this.buildDOMTree_ResultsSpec_Switch = function ( in_container_node   ///< This holds the node that will contain the switch.
                                                  )
        {
        this.m_search_spec_switch_div = document.createElement ( 'div' );   // Create the switch container.
        
        this.m_search_spec_switch_a = document.createElement ( 'a' );      // Create the basic switch anchor element.
        this.m_search_spec_switch_a.appendChild ( document.createTextNode(g_Nouveau_select_search_spec_text) );
        this.m_search_spec_switch_div.appendChild ( this.m_search_spec_switch_a );
        
        this.m_search_results_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        this.m_search_results_switch_a.appendChild ( document.createTextNode(g_Nouveau_select_search_results_text) );
        this.m_search_spec_switch_div.appendChild ( this.m_search_results_switch_a );
        
        this.m_search_spec_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.SearchSpecHit()' );
        this.m_search_results_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.SearchResultsHit()' );
        
        this.m_display_div.appendChild ( this.m_search_spec_switch_div );
        };
        
    /****************************************************************************************
    *################################### SET UP SEARCH SPEC ################################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This sets up the "MAP/TEXT" tab switch div.                                  *
    ****************************************************************************************/
    this.buildDOMTree_Map_Text_Switch = function ( in_container_node   ///< This holds the node that will contain the switch.
                                                  )
        {
        this.m_map_text_switch_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_map_text_switch_div.className = 'bmlt_nouveau_switcher_div bmlt_nouveau_text_map_switcher_div';
        
        this.m_map_switch_a = document.createElement ( 'a' );      // Create the basic switch anchor element.
        this.m_map_switch_a.appendChild ( document.createTextNode(g_NouveauMapSearch_map_name_string) );
        this.m_map_text_switch_div.appendChild ( this.m_map_switch_a );
        
        this.m_text_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        this.m_text_switch_a.appendChild ( document.createTextNode(g_NouveauMapSearch_text_name_string) );
        this.m_map_text_switch_div.appendChild ( this.m_text_switch_a );
        
        this.m_text_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.TextButtonHit()' );
        this.m_map_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.MapButtonHit()' );
        
        this.m_search_spec_div.appendChild ( this.m_map_text_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setMapTextSwitch = function()
        {
        if ( (this.m_current_view == 'map') || (this.m_current_view == 'advanced map') )
            {
            this.m_map_switch_a.className = 'bmlt_nouveau_switch_a_selected';
            this.m_text_switch_a.className = 'bmlt_nouveau_switch_a';
            this.m_text_div.className = 'bmlt_nouveau_text_div text_div_hidden';
            this.m_map_div.className = 'bmlt_nouveau_map_div';
            this.m_advanced_map_options_div.className = 'bmlt_nouveau_advanced_map_options_shown_div';
            }
        else
            {
            this.m_map_switch_a.className = 'bmlt_nouveau_switch_a';
            this.m_text_switch_a.className = 'bmlt_nouveau_switch_a_selected';
            this.m_map_div.className = 'bmlt_nouveau_map_div map_div_hidden';
            this.m_text_div.className = 'bmlt_nouveau_text_div';
            this.m_advanced_map_options_div.className = 'bmlt_nouveau_advanced_map_options_div';
            this.m_text_input.select();
            };
        };
    
    /************************************************************************************//**
    *	\brief This constructs the map div (used by the map search).                        *
    ****************************************************************************************/
    this.buildDOMTree_Spec_Map_Div = function ()
        {
        this.m_map_div = document.createElement ( 'div' );   // Create the map container.
        this.m_map_div.className = 'bmlt_nouveau_map_div';
        this.loadSpecMap();
        this.m_search_spec_div.appendChild ( this.m_map_div );
        };
    
    /************************************************************************************//**
    *	\brief This creates the map for the search spec.                                    *
    ****************************************************************************************/
	this.loadSpecMap = function ( )
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
                var id = this.m_uid;
                
                google.maps.event.addListener ( this.m_main_map, 'click', function(in_event) { NouveauMapSearch.prototype.MapClicked( in_event, id ); } );
                    
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
    *	\brief This constructs the text div (used by the text search).                      *
    ****************************************************************************************/
    this.buildDOMTree_Text_Div = function ()
        {
        this.m_text_div = document.createElement ( 'div' );
        this.m_text_div.className = 'bmlt_nouveau_text_div';
        
        this.m_text_inner_div = document.createElement ( 'div' );
        this.m_text_inner_div.className = 'bmlt_nouveau_text_inner_div';
        
        this.m_text_item_div = document.createElement ( 'div' );
        this.m_text_item_div.className = 'bmlt_nouveau_text_item_div';
        
        this.m_text_input = document.createElement ( 'input' );
        this.m_text_input.type = "text";
        this.m_text_input.defaultValue = g_Nouveau_text_item_default_text;
        this.m_text_input.className = 'bmlt_nouveau_text_input_empty';
        this.m_text_input.value = this.m_text_input.defaultValue;

        // If we have any initial text, we enter that.
        if ( this.m_initial_text )
            {
            this.m_text_input.value = this.m_initial_text;
            this.m_text_input.className = 'bmlt_nouveau_text_input';
            };

        // We just call the global handlers (since callbacks are in their own context, no worries).
        this.m_text_input.onfocus = function () {NouveauMapSearch.prototype.CheckTextInputFocus(this);};
        this.m_text_input.onblur = function () {NouveauMapSearch.prototype.CheckTextInputBlur(this);};
        this.m_text_input.onkeyup = function () {NouveauMapSearch.prototype.CheckTextInputKeyUp(this);};
        
        this.m_text_item_div.appendChild ( this.m_text_input );
        this.m_text_inner_div.appendChild ( this.m_text_item_div );
        
        this.m_text_go_button_div = document.createElement ( 'div' );
        this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div';
        
        this.m_text_go_a = document.createElement ( 'a' );
        this.m_text_go_a.className = 'bmlt_nouveau_text_go_button_a round_button';
        this.m_text_go_a.appendChild ( document.createTextNode(g_Nouveau_text_go_button_string) );
        this.m_text_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.GoForIt()' );
        
        this.m_text_go_button_div.appendChild ( this.m_text_go_a );
        this.m_text_inner_div.appendChild ( this.m_text_go_button_div );
        
        this.m_text_loc_checkbox_div = document.createElement ( 'div' );
        this.m_text_loc_checkbox_div.className = 'bmlt_nouveau_text_checkbox_div';
        
        this.m_location_checkbox = document.createElement ( 'input' );
        this.m_location_checkbox.type = 'checkbox';
        this.m_location_checkbox.id = this.m_uid + '_location_checkbox';
        this.m_location_checkbox.className = 'bmlt_nouveau_text_loc_checkbox';
        this.m_location_checkbox.checked = this.m_checked_location;
                
        this.m_location_checkbox_label = document.createElement ( 'label' );
        this.m_location_checkbox_label.className = 'bmlt_nouveau_text_checkbox_label';
        this.m_location_checkbox_label.setAttribute ( 'for', this.m_uid + '_location_checkbox' );
        
        this.m_location_checkbox_label.appendChild ( document.createTextNode(g_Nouveau_text_location_label_text) );

        this.m_text_loc_checkbox_div.appendChild ( this.m_location_checkbox );
        this.m_text_loc_checkbox_div.appendChild ( this.m_location_checkbox_label );

        this.m_text_inner_div.appendChild ( this.m_text_loc_checkbox_div );
        this.m_text_div.appendChild ( this.m_text_inner_div );
        
        var elem = document.createElement ( 'div' );
        elem.className = 'bmlt_nouveau_breaker_div';
        this.m_text_div.appendChild ( elem );
        
        this.m_location_checkbox = null;
        this.m_search_spec_div.appendChild ( this.m_text_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets up the "MAP/TEXT" tab switch div.                                  *
    ****************************************************************************************/
    this.buildDOMTree_Basic_Advanced_Switch = function ()
        {
        this.m_basic_advanced_switch_div = document.createElement ( 'div' );   // Create the switch container.
        this.m_basic_advanced_switch_div.className = 'bmlt_nouveau_switcher_div bmlt_nouveau_advanced_switcher_div';
        
        this.m_advanced_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        this.m_advanced_switch_a.appendChild ( document.createTextNode(g_NouveauMapSearch_advanced_name_string) );
        this.m_basic_advanced_switch_div.appendChild ( this.m_advanced_switch_a );
        this.m_advanced_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.ToggleAdvanced()' );
        
        this.m_basic_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        this.m_basic_switch_a.appendChild ( document.createTextNode(g_NouveauMapSearch_basic_name_string) );
        this.m_basic_advanced_switch_div.appendChild ( this.m_basic_switch_a );
        this.m_basic_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.ToggleAdvanced()' );
        
        this.m_search_spec_div.appendChild ( this.m_basic_advanced_switch_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setBasicAdvancedSwitch = function()
        {
        if ( (this.m_current_view == 'advanced map') || (this.m_current_view == 'advanced text') )
            {
            this.m_basic_switch_a.className = 'bmlt_nouveau_switch_a';
            this.m_advanced_switch_a.className = 'bmlt_nouveau_advanced_switcher_a_selected';
            this.m_advanced_section_div.className = 'bmlt_nouveau_advanced_section_div';
            this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div text_go_a_hidden';
            }
        else
            {
            this.m_advanced_switch_a.className = 'bmlt_nouveau_switch_a';
            this.m_basic_switch_a.className = 'bmlt_nouveau_switch_a_selected';
            this.m_advanced_section_div.className = 'bmlt_nouveau_advanced_section_div advanced_div_hidden';
            this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div';
            };
        };
    
    /************************************************************************************//**
    *	\brief This sets up the "BASIC/ADVANCED" tab switch div.                            *
    ****************************************************************************************/
    this.buildDOMTree_AdvancedSection = function ()
        {
        this.m_advanced_section_div = document.createElement ( 'div' );
        this.m_advanced_section_div.className = 'bmlt_nouveau_advanced_section_div';
        
        this.buildDOMTree_Advanced_MapOptions();
        this.buildDOMTree_Advanced_Weekdays();
        this.buildDOMTree_Advanced_Formats();
        this.buildDOMTree_Advanced_Service_Bodies();
        this.buildDOMTree_Advanced_GoButton();
        
        this.setAdvancedWeekdaysDisclosure();
        this.setAdvancedFormatsDisclosure();
        this.setAdvancedServiceBodiesDisclosure();
        
        this.m_search_spec_div.appendChild ( this.m_advanced_section_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the Advanced Map Options section.                                      *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_MapOptions = function ()
        {
        this.m_advanced_map_options_div = document.createElement ( 'div' );

        this.m_advanced_section_div.appendChild ( this.m_advanced_map_options_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the Advanced Weekdays section.                                         *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Weekdays = function ()
        {
        this.m_advanced_weekdays_div = document.createElement ( 'div' );
        this.m_advanced_weekdays_div.className = 'bmlt_nouveau_advanced_weekdays_div';
        
        this.buildDOMTree_Advanced_Weekdays_Header();
        this.buildDOMTree_Advanced_Weekdays_Content();
        
        this.m_advanced_section_div.appendChild ( this.m_advanced_weekdays_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the disclosure link for the Advanced Weekdays section.                 *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Weekdays_Header = function ()
        {
        this.m_advanced_weekdays_disclosure_a = null;
        
        this.m_advanced_weekdays_header_div = document.createElement ( 'div' );
        this.m_advanced_weekdays_header_div.className = 'bmlt_nouveau_advanced_weekdays_header_div';
        
        this.m_advanced_weekdays_disclosure_a = document.createElement ( 'a' );
        this.m_advanced_weekdays_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.ToggleWeekdaysDisclosure()' );
        this.m_advanced_weekdays_disclosure_a.appendChild ( document.createTextNode(g_Nouveau_advanced_weekdays_disclosure_text) );
        this.m_advanced_weekdays_header_div.appendChild ( this.m_advanced_weekdays_disclosure_a );
        
        this.m_advanced_weekdays_div.appendChild ( this.m_advanced_weekdays_header_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the content for the Advanced Weekdays section.                         *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Weekdays_Content = function ()
        {
        this.m_advanced_weekdays_content_div = document.createElement ( 'div' );
        
        this.m_advanced_weekdays_div.appendChild ( this.m_advanced_weekdays_content_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setAdvancedWeekdaysDisclosure = function()
        {
        if ( this.m_advanced_weekdays_shown )
            {
            this.m_advanced_weekdays_disclosure_a.className = 'bmlt_nouveau_advanced_weekdays_disclosure_open_a';
            this.m_advanced_weekdays_content_div.className = 'bmlt_nouveau_advanced_weekdays_content_shown_div';
            }
        else
            {
            this.m_advanced_weekdays_disclosure_a.className = 'bmlt_nouveau_advanced_weekdays_disclosure_a';
            this.m_advanced_weekdays_content_div.className = 'bmlt_nouveau_advanced_weekdays_content_div';
            };
        };
    
    /************************************************************************************//**
    *	\brief Build the Formats section.                                                   *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Formats = function ()
        {
        this.m_advanced_formats_div = document.createElement ( 'div' );
        this.m_advanced_formats_div.className = 'bmlt_nouveau_advanced_formats_div';
        
        this.buildDOMTree_Advanced_Formats_Header();
        this.buildDOMTree_Advanced_Formats_Content();
        
        this.m_advanced_section_div.appendChild ( this.m_advanced_formats_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the disclosure link for the Formats section.                           *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Formats_Header = function ()
        {
        this.m_advanced_formats_disclosure_a = null;
        
        this.m_advanced_formats_header_div = document.createElement ( 'div' );
        this.m_advanced_formats_header_div.className = 'bmlt_nouveau_advanced_formats_header_div';
        
        this.m_advanced_formats_disclosure_a = document.createElement ( 'a' );
        this.m_advanced_formats_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.ToggleFormatsDisclosure()' );
        this.m_advanced_formats_disclosure_a.appendChild ( document.createTextNode(g_Nouveau_advanced_formats_disclosure_text) );
        this.m_advanced_formats_header_div.appendChild ( this.m_advanced_formats_disclosure_a );
        
        this.m_advanced_formats_div.appendChild ( this.m_advanced_formats_header_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the contents for the Formats section.                                  *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Formats_Content = function ()
        {
        this.m_advanced_formats_content_div = document.createElement ( 'div' );
        this.m_advanced_formats_content_div.className = 'bmlt_nouveau_advanced_formats_content_div';
        
        this.m_advanced_formats_div.appendChild ( this.m_advanced_formats_content_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setAdvancedFormatsDisclosure = function()
        {
        if ( this.m_advanced_formats_shown )
            {
            this.m_advanced_formats_disclosure_a.className = 'bmlt_nouveau_advanced_formats_disclosure_open_a';
            this.m_advanced_formats_content_div.className = 'bmlt_nouveau_advanced_formats_content_shown_div';
            }
        else
            {
            this.m_advanced_formats_disclosure_a.className = 'bmlt_nouveau_advanced_formats_disclosure_a';
            this.m_advanced_formats_content_div.className = 'bmlt_nouveau_advanced_formats_content_div';
            };
        };
    
    /************************************************************************************//**
    *	\brief Build the Advanced Service Bodies section.                                   *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Service_Bodies = function ()
        {
        this.m_advanced_service_bodies_div = document.createElement ( 'div' );
        this.m_advanced_service_bodies_div.className = 'bmlt_nouveau_advanced_service_bodies_div';
        
        this.buildDOMTree_Advanced_Service_Bodies_Header();
        this.buildDOMTree_Advanced_Service_Bodies_Content();
        
        this.m_advanced_section_div.appendChild ( this.m_advanced_service_bodies_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the disclosure link for the Advanced Service Bodies.                   *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Service_Bodies_Header = function ()
        {
        this.m_advanced_service_bodies_disclosure_a = null;
        
        this.m_advanced_service_bodies_header_div = document.createElement ( 'div' );
        this.m_advanced_service_bodies_header_div.className = 'bmlt_nouveau_advanced_service_bodies_header_div';
        
        this.m_advanced_service_bodies_disclosure_a = document.createElement ( 'a' );
        this.m_advanced_service_bodies_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.ToggleServiceBodiesDisclosure()' );
        this.m_advanced_service_bodies_disclosure_a.appendChild ( document.createTextNode(g_Nouveau_advanced_service_bodies_disclosure_text) );
        this.m_advanced_service_bodies_header_div.appendChild ( this.m_advanced_service_bodies_disclosure_a );
        
        this.m_advanced_service_bodies_div.appendChild ( this.m_advanced_service_bodies_header_div );
        };
    
    /************************************************************************************//**
    *	\brief Build the content for the Advanced Service Bodies section.                   *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_Service_Bodies_Content = function ()
        {
        this.m_advanced_service_bodies_content_div = document.createElement ( 'div' );
        this.m_advanced_service_bodies_content_div.className = 'bmlt_nouveau_advanced_service_bodies_content_div';
        
        this.m_advanced_service_bodies_div.appendChild ( this.m_advanced_service_bodies_content_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setAdvancedServiceBodiesDisclosure = function()
        {
        if ( this.m_advanced_service_bodies_shown )
            {
            this.m_advanced_service_bodies_disclosure_a.className = 'bmlt_nouveau_advanced_service_bodies_disclosure_open_a';
            this.m_advanced_service_bodies_content_div.className = 'bmlt_nouveau_advanced_service_bodies_content_shown_div';
            }
        else
            {
            this.m_advanced_service_bodies_disclosure_a.className = 'bmlt_nouveau_advanced_service_bodies_disclosure_a';
            this.m_advanced_service_bodies_content_div.className = 'bmlt_nouveau_advanced_service_bodies_content_div';
            };
        };
    
    /************************************************************************************//**
    *	\brief Build the GO button for the Advanced section.                                *
    ****************************************************************************************/
    this.buildDOMTree_Advanced_GoButton = function ()
        {
        this.m_advanced_go_button_div = document.createElement ( 'div' );
        this.m_advanced_go_button_div.className = 'bmlt_nouveau_advanced_go_button_div';
        
        this.m_advanced_go_a = document.createElement ( 'a' );
        this.m_advanced_go_a.className = 'bmlt_nouveau_advanced_go_button_a round_button';
        this.m_advanced_go_a.appendChild ( document.createTextNode(g_Nouveau_text_go_button_string) );
        this.m_advanced_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.GoForIt()' );
        
        this.m_advanced_go_button_div.appendChild ( this.m_advanced_go_a );
        this.m_advanced_section_div.appendChild ( this.m_advanced_go_button_div );
        };
        
    /****************************************************************************************
    *#################################### MAP HANDLERS #####################################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This moves the marker, in response to a map click.                           *
    ****************************************************************************************/
    this.advancedMapClicked = function ()
        {
        };

    /****************************************************************************************
    *################################### PERFORM SEARCH ####################################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This function constructs a URI to the root server that reflects the search   *
    ****************************************************************************************/
    this.evaluateSearchEvent = function (   in_map_clicked
                                        )
        {
        if ( in_map_clicked && (this.m_current_view == 'advanced map') )
            {
            do_search = false;
            }
        else
            {
            this.m_search_results = null;
            this.m_search_results_shown = false;
            this.setDisplayedSearchResults();
            this.callRootServer ( this.createSearchURI() );
            };
        };

    /************************************************************************************//**
    *	\brief This function constructs a URI to the root server that reflects the search   *
    *          parameters, as specified by the search specification section.                *
    *   \returns a string, containing the complete URI.                                     *
    ****************************************************************************************/
    this.createSearchURI = function ()
        {
        var ret = this.m_root_server_uri; // We append a question mark, so all the rest can be added without worrying about this.
        
        // These will all be appended to the URI (or not).
        var uri_elements = new Array;
        var index = 0;
        
        // First, if we have a map up, we will use that as the location (not done if the search is specified using text).
        if ( (this.m_current_view == 'map') || (this.m_current_view == 'advanced map') )
            {
            uri_elements[index] = new Array;
            uri_elements[index][0] = 'long_val';
            uri_elements[index++][1] = this.m_current_long;
            
            uri_elements[index] = new Array;
            uri_elements[index][0] = 'lat_val';
            uri_elements[index++][1] = this.m_current_lat;
            
            uri_elements[index] = new Array;
            uri_elements[index][0] = 'geo_width';

            // In the case of the advanced map, we will also have a radius value. Otherwise, we use the default auto.
            uri_elements[index++][1] = (this.m_current_view == 'advanced map') ? this.m_search_radius : g_Nouveau_default_geo_width;
            };
        
        // Concatenate all the various parameters we gathered.
        for ( var i = 0; i < index; i++ )
            {
            ret += '&' + uri_elements[i][0] + '=' + uri_elements[i][1];
            }
        
        // Return the complete URI for a JSON response.
        return ret;
        };
	
	/************************************************************************************//**
	*	\brief  Does an AJAX call for a JSON response, based on the given criteria and      *
	*           callback function.                                                          *
	*           The callback will be a function in the following format:                    *
	*               function ajax_callback ( in_json_obj )                                  *
	*           where "in_json_obj" is the response, converted to a JSON object.            *
	*           it will be null if the function failed.                                     *
	****************************************************************************************/
	
	this.callRootServer = function ( in_uri ///< The URI to call (with all the parameters).
	                                )
	{
	    if ( this.m_ajax_request )   // This prevents the requests from piling up. We are single-threaded.
	        {
	        this.m_ajax_request.abort();
	        this.m_ajax_request = null;
	        };
	    
        this.m_ajax_request = BMLTPlugin_AjaxRequest ( in_uri, NouveauMapSearch.prototype.AJAXRouter, 'get', this.m_uid );
	};
        
    /****************************************************************************************
    *################################# SET UP SEARCH RESULTS ###############################*
    ****************************************************************************************/
    
    /************************************************************************************//**
    *	\brief This either hides or shows the search results.                               *
    ****************************************************************************************/
    this.processSearchResults = function( in_search_results_json_object ///< The search results, as a JSON object.
                                        )
        {
        this.m_search_results = in_search_results_json_object;
        this.m_search_results_shown = true;
        this.setDisplayedSearchResults();
        };
    
    /************************************************************************************//**
    *	\brief This either hides or shows the search results.                               *
    ****************************************************************************************/
    this.setDisplayedSearchResults = function()
        {
        if ( !this.m_search_results )
            {
            this.m_search_results_shown = false;    // Can't show what doesn't exist.
            this.m_search_spec_switch_div.className = 'bmlt_nouveau_search_spec_switch_div bmlt_nouveau_search_spec_switch_div_hidden';
            this.m_search_results_div.className = 'bmlt_nouveau_search_results_div bmlt_nouveau_results_hidden';
            this.m_search_spec_div.className = 'bmlt_nouveau_search_spec_div';
            }
        else
            {
            this.m_search_spec_switch_div.className = 'bmlt_nouveau_search_spec_switch_div';
            
            if ( this.m_search_results_shown )
                {
                this.m_search_spec_switch_a.className = 'bmlt_search_spec_switch_a';
                this.m_search_results_switch_a.className = 'bmlt_search_results_switch_a bmlt_search_results_switch_hidden';
                this.m_search_results_div.className = 'bmlt_nouveau_search_results_div';
                this.m_search_spec_div.className = 'bmlt_nouveau_search_spec_div bmlt_nouveau_spec_hidden';
                }
            else
                {
                this.m_search_spec_switch_a.className = 'bmlt_search_spec_switch_a bmlt_search_spec_switch_hidden';
                this.m_search_results_switch_a.className = 'bmlt_search_spec_switch_a';
                this.m_search_results_div.className = 'bmlt_nouveau_search_results_div bmlt_nouveau_results_hidden';
                this.m_search_spec_div.className = 'bmlt_nouveau_search_spec_div';
                };
            };
        };
    
    /****************************************************************************************
    *##################################### CONSTRUCTOR #####################################*
    ****************************************************************************************/
    
    this.m_uid = in_unique_id;
    this.m_current_view = in_initial_view;
    this.m_current_long = in_initial_long;
    this.m_current_lat = in_initial_lat;
    this.m_current_zoom = in_initial_zoom;
    this.m_root_server_uri = in_root_server_uri;
    this.m_initial_text = in_initial_text;
    this.m_checked_location = in_checked_location;
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

/********************************************************************************************
*################################ STATIC CALLBACK FUNCTIONS ################################*
*                                                                                           *
* These functions are statically, and have no object context (no 'this').                   *
********************************************************************************************/
/****************************************************************************************//**
*	\brief Will check a text element upon blur, and will fill it with the default string.   *
********************************************************************************************/
NouveauMapSearch.prototype.CheckTextInputBlur = function ( in_text_element  ///< The text element being evaluated.
                                                            )
    {
    if ( in_text_element && in_text_element.value && (in_text_element.value != in_text_element.defaultValue) )
        {
        in_text_element.className = 'bmlt_nouveau_text_input';
        }
    else
        {
        in_text_element.className = 'bmlt_nouveau_text_input_empty';
        in_text_element.value = in_text_element.defaultValue;
        };
    };

/****************************************************************************************//**
*	\brief Will test a text element upon keyUp, and may change its appearance.              *
********************************************************************************************/
NouveauMapSearch.prototype.CheckTextInputKeyUp = function ( in_text_element ///< The text element being evaluated.
                                                            )
    {
    if ( in_text_element && in_text_element.value && (in_text_element.value != in_text_element.defaultValue) )
        {
        in_text_element.className = 'bmlt_nouveau_text_input';
        }
    else
        {
        in_text_element.className = 'bmlt_nouveau_text_input_empty';
        in_text_element.value = (in_text_element.hasFocus()) ? '' : in_text_element.defaultValue;
        };
    };

/****************************************************************************************//**
*	\brief Will test a text element upon focus, and remove any default string.              *
********************************************************************************************/
NouveauMapSearch.prototype.CheckTextInputFocus = function ( in_text_element ///< The text element being evaluated.
                                                            )
    {
    if ( in_text_element.value && (in_text_element.value == in_text_element.defaultValue) )
        {
        in_text_element.value = '';
        };
    };

/********************************************************************************************
*######################## CONTEXT-ESTABLISHING CALLBACK FUNCTIONS ##########################*
*                                                                                           *
* These functions are called statically, but establish context from an ID passed in.        *
********************************************************************************************/
/****************************************************************************************//**
*	\brief Responds to a click in the map.                                                  *
********************************************************************************************/
NouveauMapSearch.prototype.MapClicked = function (  in_event,   ///< The map event
                                                    in_id       ///< The unique ID of the object (establishes context).
                                                    )
    {
    // This funky line creates an object context from the ID passed in.
    // Each object is represented by a dynamically-created global variable, defined by ID, so we access that.
    // 'context' becomes a placeholder for 'this'.
    eval ('var context = g_instance_' + in_id + '_js_handler');
	
	// We set the long/lat from the event.
	context.m_current_long = in_event.latLng.lng().toString();
	context.m_current_lat = in_event.latLng.lat().toString();

    if ( context.m_current_view == 'map' ) // If it is a simple map, we go straight to a search.
        {
        context.evaluateSearchEvent();
        }
    else    // Otherwise, we simply move the marker.
        {
        context.advancedMapClicked();
        };
    };
	
/****************************************************************************************//**
*	\brief This is the AJAX callback from a search request.                                 *
********************************************************************************************/
NouveauMapSearch.prototype.AJAXRouter = function ( in_response_object,  ///< The HTTPRequest response object.
                                                    in_id               ///< The unique ID of the object (establishes context).
                                                    )
    {
    eval ('var context = g_instance_' + in_id + '_js_handler');
    
    if ( context )
        {
        context.m_ajax_request = null;
        context.m_search_results = null;
        
        var text_reply = in_response_object.responseText;
    
        if ( text_reply )
            {
            var json_builder = 'var response_object = ' + text_reply + ';';
        
            // This is how you create JSON objects.
            eval ( json_builder );
        
            if ( response_object.length )
                {
                context.processSearchResults ( response_object );
                }
            else
                {
                alert ( g_Nouveau_no_search_results_text );
                };
            }
        else
            {
            alert ( g_Nouveau_no_search_results_text );
            };
        }
    else
        {
        alert ( g_Nouveau_no_search_results_text );
        };
    };

/********************************************************************************************
*############################### INSTANCE CALLBACK FUNCTIONS ###############################*
*                                                                                           *
* These functions are called for an instance, and have object context.                      *
********************************************************************************************/

/****************************************************************************************//**
*	\brief Responds to the Specify A New Search link being hit.                             *
********************************************************************************************/
NouveauMapSearch.prototype.SearchSpecHit = function()
    {
    this.m_search_results_shown = false;
    this.setDisplayedSearchResults();
    };
    
/****************************************************************************************//**
*	\brief Responds to the Show Search Results link being hit.                              *
********************************************************************************************/
NouveauMapSearch.prototype.SearchResultsHit = function()
    {
    this.m_search_results_shown = true;
    this.setDisplayedSearchResults();
    };

/****************************************************************************************//**
*	\brief Responds to either of the GO buttons being hit.                                  *
********************************************************************************************/
NouveauMapSearch.prototype.GoForIt = function()
    {
    this.evaluateSearchEvent();
    };

/****************************************************************************************//**
*	\brief Toggles the state of the Basic/Advanced search spec display.                     *
********************************************************************************************/
NouveauMapSearch.prototype.ToggleAdvanced = function()
    {
    switch ( this.m_current_view )   // Vet the class state.
        {
        case 'map':
            this.m_current_view = 'advanced map';
        break;
        
        case 'advanced map':
            this.m_current_view = 'map';
        break;
        
        case 'text':
            this.m_current_view = 'advanced text';
        break;
        
        case 'advanced text':
            this.m_current_view = 'text';
        break;
        };
        
    this.setBasicAdvancedSwitch();
    };
        
/****************************************************************************************//**
*	\brief Responds to the Search By Map link being hit.                                    *
********************************************************************************************/
NouveauMapSearch.prototype.MapButtonHit = function()
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
*	\brief Responds to the Search By Text button being hit.                                 *
********************************************************************************************/
NouveauMapSearch.prototype.TextButtonHit = function()
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
        
/****************************************************************************************//**
*	\brief Toggles the display state of the Advanced Weekdays section.                      *
********************************************************************************************/
NouveauMapSearch.prototype.ToggleWeekdaysDisclosure = function()
    {
    this.m_advanced_weekdays_shown = !this.m_advanced_weekdays_shown;
    
    this.setAdvancedWeekdaysDisclosure();
    };
        
/****************************************************************************************//**
*	\brief Toggles the display state of the Advanced Formats section.                       *
********************************************************************************************/
NouveauMapSearch.prototype.ToggleFormatsDisclosure = function()
    {
    this.m_advanced_formats_shown = !this.m_advanced_formats_shown;
    
    this.setAdvancedFormatsDisclosure();
    };
        
/****************************************************************************************//**
*	\brief Toggles the display state of the Advanced Service Bodies section.                *
********************************************************************************************/
NouveauMapSearch.prototype.ToggleServiceBodiesDisclosure = function()
    {
    this.m_advanced_service_bodies_shown = !this.m_advanced_service_bodies_shown;
    
    this.setAdvancedServiceBodiesDisclosure();
    };
