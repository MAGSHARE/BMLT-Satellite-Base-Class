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
*	\brief  This class implements our bmlt_nouveau instance as an entirely DOM-generated    *
*           JavaScript Web app.                                                             *
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
    var m_checked_location = null;         ///< This is set at construction. If true, then the "Location" checkbox will be checked at startup.
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
    var m_advanced_switch_a = null;         ///< This is the "advanced" disclosure switch
    var m_map_switch_a = null;              ///< This is the "map" anchor
    var m_text_switch_a = null;             ///< This is the "text" anchor
    var m_advanced_section_div = null;      ///< This is the advanced display section
    
    var m_advanced_go_a = null;             ///< This will be a "GO" button in the advanced search section.
    
    var m_map_div = null;                   ///< This will contain the map.
    var m_main_map = null;                  ///< This is the actual Google Maps instance.
    var m_search_radius = null;             ///< This is the chosen search radius (if the advanced search is open and the map is open).
    
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
    var m_advanced_weekdays_shown = null;
    
    /// Meeting Formats
    var m_advanced_formats_div = null;
    var m_advanced_formats_header_div = null;
    var m_advanced_formats_disclosure_a = null;
    var m_advanced_formats_content_div = null;
    var m_advanced_formats_shown = null;
    
    /// Service Bodies
    var m_advanced_service_bodies_div = null;
    var m_advanced_service_bodies_header_div = null;
    var m_advanced_service_bodies_disclosure_a = null;
    var m_advanced_service_bodies_content_div = null;
    var m_advanced_service_bodies_shown = null;
    
    /// The GO Button
    var m_advanced_go_button_div = null;
    
    /// The dynamic map search results
    var m_map_search_results_disclosure_div = null;
    var m_map_search_results_disclosure_a = null;
    var m_map_search_results_container_div = null;
    var m_map_search_results_inner_container_div = null;
    var m_map_search_results_map_div = null;
    var m_map_search_results_map = null;
    var m_mapResultsDisplayed = null;
    
    /// The dynamic list search results
    var m_list_search_results_disclosure_div = null;
    var m_list_search_results_disclosure_a = null;
    var m_list_search_results_container_div = null;
    var m_list_search_results_table = null;
    var m_list_search_results_table_head = null;
    var m_list_search_results_table_body = null;
    var m_listResultsDisplayed = null;
    
    var m_single_meeting_display_div = null;    ///< This is the div that will be used to display the details of a single meeting.
    var m_throbber_div = null;                  ///< This will show the throbber.
    
    var m_search_results = null;                ///< If there are any search results, they are kept here (JSON object).
    var m_long_lat_northeast = null;            ///< This will contain the long/lat for the maximum North and West coordinate to show all the meetings in the search.
    var m_long_lat_southwest = null;            ///< This will contain the long/lat for the maximum South and East coordinate to show all the meetings in the search.
    var m_search_results_shown = null;          ///< If this is true, then the results div is displayed.
    
    var m_ajax_request = null;                  ///< This is used to handle AJAX calls.
    
    var m_search_sort_key = null;               ///< This can be 'time', 'town', 'name', or 'distance'.
        
    /****************************************************************************************
    *								  INTERNAL CLASS FUNCTIONS							    *
    ****************************************************************************************/
    /****************************************************************************************
    *#################################### THIRD-PARTY CODE #################################*
    ****************************************************************************************/
    /**
    sprintf() for JavaScript 0.6

    Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:
        * Redistributions of source code must retain the above copyright
          notice, this list of conditions and the following disclaimer.
        * Redistributions in binary form must reproduce the above copyright
          notice, this list of conditions and the following disclaimer in the
          documentation and/or other materials provided with the distribution.
        * Neither the name of sprintf() for JavaScript nor the
          names of its contributors may be used to endorse or promote products
          derived from this software without specific prior written permission.

    THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
    ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
    WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
    DISCLAIMED. IN NO EVENT SHALL Alexandru Marasteanu BE LIABLE FOR ANY
    DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
    (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
    LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
    ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
    (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
    SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


    Changelog:
    2007.04.03 - 0.1:
     - initial release
    2007.09.11 - 0.2:
     - feature: added argument swapping
    2007.09.17 - 0.3:
     - bug fix: no longer throws exception on empty paramenters (Hans Pufal)
    2007.10.21 - 0.4:
     - unit test and patch (David Baird)
    2010.05.09 - 0.5:
     - bug fix: 0 is now preceeded with a + sign
     - bug fix: the sign was not at the right position on padded results (Kamal Abdali)
     - switched from GPL to BSD license
    2010.05.22 - 0.6:
     - reverted to 0.4 and fixed the bug regarding the sign of the number 0
     Note:
     Thanks to Raphael Pigulla <raph (at] n3rd [dot) org> (http://www.n3rd.org/)
     who warned me about a bug in 0.5, I discovered that the last update was
     a regress. I appologize for that.
    **/

    function str_repeat(i, m) {
        for (var o = []; m > 0; o[--m] = i);
        return o.join('');
    }

    function sprintf() {
        var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
        while (f) {
            if (m = /^[^\x25]+/.exec(f)) {
                o.push(m[0]);
            }
            else if (m = /^\x25{2}/.exec(f)) {
                o.push('%');
            }
            else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
                if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) {
                    throw('Too few arguments.');
                }
                if (/[^s]/.test(m[7]) && (typeof(a) != 'number')) {
                    throw('Expecting number but found ' + typeof(a));
                }
                switch (m[7]) {
                    case 'b': a = a.toString(2); break;
                    case 'c': a = String.fromCharCode(a); break;
                    case 'd': a = parseInt(a,10); break;
                    case 'e': a = m[6] ? a.toExponential(m[6]) : a.toExponential(); break;
                    case 'f': a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a); break;
                    case 'o': a = a.toString(8); break;
                    case 's': a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a); break;
                    case 'u': a = Math.abs(a); break;
                    case 'x': a = a.toString(16); break;
                    case 'X': a = a.toString(16).toUpperCase(); break;
                }
                a = (/[def]/.test(m[7]) && m[2] && a >= 0 ? '+'+ a : a);
                c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
                x = m[5] - String(a).length - s.length;
                p = m[5] ? str_repeat(c, x) : '';
                o.push(s + (m[4] ? a + p : p + a));
            }
            else {
                throw('Huh ?!');
            }
            f = f.substring(m[0].length);
        }
        return o.join('');
    }
    
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
        this.buildDOMTree_SearchResults_Section();
        
        this.setDisplayedSearchResults();   // Make sure that the proper div is displayed.
        this.validateGoButtons();
        
        this.buildDOMTree_CreateThrobberDiv();
        this.hideThrobber();
        
        // Finally, set everything into the container.
        this.m_container_div.appendChild ( this.m_display_div );
        };
    
    /****************************************************************************************
    *######################## SET UP SEARCH SPEC AND RESULTS SWITCH ########################*
    ****************************************************************************************/

    /************************************************************************************//**
    *	\brief This sets up the "MAP/TEXT" tab switch div.                                  *
    ****************************************************************************************/
    this.buildDOMTree_ResultsSpec_Switch = function ()
        {
        this.m_search_spec_switch_div = document.createElement ( 'div' );   // Create the switch container.
        
        this.m_search_spec_switch_a = document.createElement ( 'a' );      // Create the basic switch anchor element.
        this.m_search_spec_switch_a.appendChild ( document.createTextNode(g_Nouveau_select_search_spec_text) );
        this.m_search_spec_switch_div.appendChild ( this.m_search_spec_switch_a );
        
        this.m_search_results_switch_a = document.createElement ( 'a' );      // Create the advanced switch anchor element.
        this.m_search_results_switch_a.appendChild ( document.createTextNode(g_Nouveau_select_search_results_text) );
        this.m_search_spec_switch_div.appendChild ( this.m_search_results_switch_a );
        
        this.m_search_spec_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.searchSpecButtonHit()' );
        this.m_search_results_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.searchResultsButtonHit()' );
        
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
        
        this.m_text_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.textButtonHit()' );
        this.m_map_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.mapButtonHit()' );
        
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
            this.m_advanced_map_options_div.className = 'bmlt_nouveau_advanced_map_options_div';
            }
        else
            {
            this.m_map_switch_a.className = 'bmlt_nouveau_switch_a';
            this.m_text_switch_a.className = 'bmlt_nouveau_switch_a_selected';
            this.m_map_div.className = 'bmlt_nouveau_map_div map_div_hidden';
            this.m_text_div.className = 'bmlt_nouveau_text_div';
            this.m_advanced_map_options_div.className = 'bmlt_nouveau_advanced_map_options_div bmlt_nouveau_advanced_map_options_div_hidden';
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
                this.m_main_map.center_marker = null;
                this.m_main_map.geo_width = null;
                
                var id = this.m_uid;
                
                google.maps.event.addListener ( this.m_main_map, 'click', function(in_event) { NouveauMapSearch.prototype.sMapClicked( in_event, id ); } );
                    
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
    *	\brief This creates the map for the search spec.                                    *
    ****************************************************************************************/
	this.loadResultsMap = function ( )
	    {
        if ( this.m_map_search_results_map_div )
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

            var	pixel_width = this.m_map_search_results_map_div.offsetWidth;
            var	pixel_height = this.m_map_search_results_map_div.offsetHeight;
            
            if ( (pixel_width < 640) || (pixel_height < 640) )
                {
                myOptions.scrollwheel = true;
                myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.SMALL };
                }
            else
                {
                myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };
                };

            this.m_map_search_results_map = new google.maps.Map ( this.m_map_search_results_map_div, myOptions );
            
            if ( this.m_map_search_results_map )
                {
                this.m_map_search_results_map.meeting_marker_array = null;
                
                var id = this.m_uid;
                
                google.maps.event.addListener ( this.m_map_search_results_map, 'zoom_changed', function(in_event) { NouveauMapSearch.prototype.sMapZoomChanged( in_event, id ); } );

                this.m_map_search_results_map.fitBounds ( new google.maps.LatLngBounds ( new google.maps.LatLng ( this.m_long_lat_southwest.lat, this.m_long_lat_southwest.lng ), new google.maps.LatLng ( this.m_long_lat_northeast.lat, this.m_long_lat_northeast.lng ) ) );
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
        this.m_text_input.uid = this.m_uid; // Used to establish context in the callbacks.
        this.m_text_input.onfocus = function () {NouveauMapSearch.prototype.sCheckTextInputFocus(this);};
        this.m_text_input.onblur = function () {NouveauMapSearch.prototype.sCheckTextInputBlur(this);};
        this.m_text_input.onkeyup = function () {NouveauMapSearch.prototype.sCheckTextInputKeyUp(this);};
        
        this.m_text_item_div.appendChild ( this.m_text_input );
        this.m_text_inner_div.appendChild ( this.m_text_item_div );
        
        this.m_text_go_button_div = document.createElement ( 'div' );
        this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div';
        
        this.m_text_go_a = document.createElement ( 'a' );
        this.m_text_go_a.appendChild ( document.createTextNode(g_Nouveau_text_go_button_string) );
        
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
        this.m_advanced_switch_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.toggleAdvanced()' );
        this.m_basic_advanced_switch_div.appendChild ( this.m_advanced_switch_a );
        
        this.m_search_spec_div.appendChild ( this.m_basic_advanced_switch_div );
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
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setBasicAdvancedSwitch = function()
        {
        if ( (this.m_current_view == 'advanced map') || (this.m_current_view == 'advanced text') )
            {
            this.m_advanced_switch_a.className = 'bmlt_nouveau_advanced_switch_disclosure_open_a';
            this.m_advanced_section_div.className = 'bmlt_nouveau_advanced_section_div';
            this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div text_go_a_hidden';
            }
        else
            {
            this.m_advanced_switch_a.className = 'bmlt_nouveau_advanced_switch_disclosure_a';
            this.m_advanced_section_div.className = 'bmlt_nouveau_advanced_section_div advanced_div_hidden';
            this.m_text_go_button_div.className = 'bmlt_nouveau_text_go_button_div';
            };
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
        this.m_advanced_weekdays_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.toggleWeekdaysDisclosure()' );
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
            this.m_advanced_weekdays_content_div.className = 'bmlt_nouveau_advanced_weekdays_content_div';
            }
        else
            {
            this.m_advanced_weekdays_disclosure_a.className = 'bmlt_nouveau_advanced_weekdays_disclosure_a';
            this.m_advanced_weekdays_content_div.className = 'bmlt_nouveau_advanced_weekdays_content_div bmlt_nouveau_advanced_weekdays_content_div_hidden';
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
        this.m_advanced_formats_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.toggleFormatsDisclosure()' );
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
            this.m_advanced_formats_content_div.className = 'bmlt_nouveau_advanced_formats_content_div';
            }
        else
            {
            this.m_advanced_formats_disclosure_a.className = 'bmlt_nouveau_advanced_formats_disclosure_a';
            this.m_advanced_formats_content_div.className = 'bmlt_nouveau_advanced_formats_content_div bmlt_nouveau_advanced_formats_content_div_hidden';
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
        this.m_advanced_service_bodies_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.toggleServiceBodiesDisclosure()' );
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
            this.m_advanced_service_bodies_content_div.className = 'bmlt_nouveau_advanced_service_bodies_content_div';
            }
        else
            {
            this.m_advanced_service_bodies_disclosure_a.className = 'bmlt_nouveau_advanced_service_bodies_disclosure_a';
            this.m_advanced_service_bodies_content_div.className = 'bmlt_nouveau_advanced_service_bodies_content_div bmlt_nouveau_advanced_service_bodies_content_div_hidden';
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
        this.m_advanced_go_a.className = 'bmlt_nouveau_advanced_go_button_a';
        this.m_advanced_go_a.appendChild ( document.createTextNode(g_Nouveau_text_go_button_string) );
        this.m_advanced_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.goButtonHit()' );
        
        this.m_advanced_go_button_div.appendChild ( this.m_advanced_go_a );
        this.m_advanced_section_div.appendChild ( this.m_advanced_go_button_div );
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.clearSearchResults = function ()
        {
        this.m_long_lat_northwest = null;
        this.m_long_lat_southeast = null;
        this.m_search_results = null;
        this.m_search_results_shown = false;
        this.m_map_search_results_map = null;
        this.m_mapResultsDisplayed = false;
        this.m_listResultsDisplayed = false;

        if ( this.m_search_results_div )
            {
            this.m_display_div.removeChild ( this.m_search_results_div );
            this.m_search_results_div.innerHTML = "";
            };
        
        this.m_map_search_results_disclosure_div = null;
        this.m_map_search_results_disclosure_a = null;
        this.m_map_search_results_container_div = null;
        this.m_map_search_results_map_div = null;
        this.m_list_search_results_disclosure_div = null;
        this.m_list_search_results_disclosure_a = null;
        this.m_list_search_results_container_div = null;
        this.m_list_search_results_table = null;
        this.m_list_search_results_table_head = null;
        this.m_list_search_results_table_body = null;
        this.m_search_results_div = null;
        };
    
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_Section = function ()
        {
        this.m_search_results_div = document.createElement ( 'div' );
        this.m_search_results_div.className = 'bmlt_nouveau_search_results_div';
        
        if ( this.m_search_results && this.m_search_results.length )
            {
            this.buildDOMTree_SearchResults_Map();
            this.buildDOMTree_SearchResults_List();
            };
        
        this.m_display_div.appendChild ( this.m_search_results_div );
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_Map = function ()
        {
        this.m_map_search_results_disclosure_div = document.createElement ( 'div' );
        this.m_map_search_results_disclosure_div.className = 'bmlt_nouveau_search_results_map_disclosure_div';
        
        this.m_map_search_results_disclosure_a = document.createElement ( 'a' );
        this.m_map_search_results_disclosure_a.appendChild ( document.createTextNode(g_Nouveau_display_map_results_text) );
        this.m_map_search_results_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.displayMapResultsDiscolsureHit()' );
        
        this.m_map_search_results_disclosure_div.appendChild ( this.m_map_search_results_disclosure_a );
        
        this.m_search_results_div.appendChild ( this.m_map_search_results_disclosure_div );

        this.m_map_search_results_container_div = document.createElement ( 'div' );
        this.m_map_search_results_container_div.className = 'bmlt_nouveau_search_results_map_container_div';
        
        this.m_map_search_results_inner_container_div = document.createElement ( 'div' );
        this.m_map_search_results_inner_container_div.className = 'bmlt_nouveau_search_results_map_inner_container_div';
        
        this.m_map_search_results_map_div = document.createElement ( 'div' );
        this.m_map_search_results_map_div.className = 'bmlt_nouveau_search_results_map_div';
        
        this.m_map_search_results_inner_container_div.appendChild ( this.m_map_search_results_map_div );
        this.m_map_search_results_container_div.appendChild ( this.m_map_search_results_inner_container_div );
        
        this.setMapResultsDisclosure();
        
        this.m_search_results_div.appendChild ( this.m_map_search_results_container_div );
        };
    
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setMapResultsDisclosure = function()
        {
        if ( this.m_mapResultsDisplayed )
            {
            this.m_map_search_results_disclosure_a.className = 'bmlt_nouveau_search_results_map_disclosure_a bmlt_nouveau_search_results_map_disclosure_open_a';
            this.m_map_search_results_container_div.className = 'bmlt_nouveau_search_results_map_container_div';
            }
        else
            {
            this.m_map_search_results_disclosure_a.className = 'bmlt_nouveau_search_results_map_disclosure_a';
            this.m_map_search_results_container_div.className = 'bmlt_nouveau_search_results_map_container_div bmlt_nouveau_search_results_map_container_div_hidden';
            };
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_List = function ()
        {
        this.m_list_search_results_disclosure_div = document.createElement ( 'div' );
        this.m_list_search_results_disclosure_div.className = 'bmlt_nouveau_search_results_list_disclosure_div';
        
        this.m_list_search_results_disclosure_a = document.createElement ( 'a' );
        this.m_list_search_results_disclosure_a.appendChild ( document.createTextNode(g_Nouveau_display_list_results_text) );
        this.m_list_search_results_disclosure_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.displayListResultsDiscolsureHit()' );
        
        this.m_list_search_results_disclosure_div.appendChild ( this.m_list_search_results_disclosure_a );
        
        this.m_search_results_div.appendChild ( this.m_list_search_results_disclosure_div );

        this.m_list_search_results_container_div = document.createElement ( 'div' );
        this.m_list_search_results_container_div.className = 'bmlt_nouveau_search_results_list_container_div';
        this.buildDOMTree_SearchResults_List_Table();
        
        this.setListResultsDisclosure();
        
        this.m_search_results_div.appendChild ( this.m_list_search_results_container_div );
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_List_Table = function ()
        {
        this.m_list_search_results_table = document.createElement ( 'table' );
        this.m_list_search_results_table.className = 'bmlt_nouveau_search_results_list_table';
        this.m_list_search_results_table.setAttribute ( 'cellpadding', 0 );
        this.m_list_search_results_table.setAttribute ( 'cellspacing', 0 );
        this.m_list_search_results_table.setAttribute ( 'border', 0 );
        
        this.m_list_search_results_table_head = document.createElement ( 'thead' );
        this.m_list_search_results_table_head.className = 'bmlt_nouveau_search_results_list_thead';
        this.buildDOMTree_SearchResults_List_Table_Header();
        
        this.m_list_search_results_table.appendChild ( this.m_list_search_results_table_head );
        
        this.m_list_search_results_table_body = document.createElement ( 'tbody' );
        this.m_list_search_results_table_body.className = 'bmlt_nouveau_search_results_list_tbody';
        this.buildDOMTree_SearchResults_List_Table_Contents();
        
        this.m_list_search_results_table.appendChild ( this.m_list_search_results_table_body );
        
        this.m_list_search_results_container_div.appendChild ( this.m_list_search_results_table );
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_List_Table_Header = function ()
        {
        // The header has one row.
        var tr_element = document.createElement ( 'tr' );
        tr_element.className = 'bmlt_nouveau_search_results_list_header_tr';
        
        for ( var i = 0; i < g_Nouveau_array_header_text.length; i++ )
            {
            var td_element = document.createElement ( 'td' );
            td_element.className = 'bmlt_nouveau_search_results_list_header_td';
        
            switch ( i )
                {
                case    0:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_nation';
                break;
            
                case    1:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_state';
                break;
            
                case    2:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_county';
                break;
            
                case    3:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_town';
                break;
            
                case    4:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_name';
                break;
            
                case    5:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_weekday';
                break;
            
                case    6:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_start';
                break;
            
                case    7:
                    td_element.className += ' bmlt_nouveau_search_results_list_header_td_location';
                break;

                default:
                    td_element.className += ' bmlt_nouveau_hidden';
                break;
                };
        
            td_element.appendChild ( document.createTextNode(g_Nouveau_array_header_text[i]) );
            tr_element.appendChild ( td_element );
            };
        
        this.m_list_search_results_table_head.appendChild ( tr_element );
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_List_Table_Contents = function ()
        {
        // The header has one row.
        for ( var i = 0; i < this.m_search_results.length; i++ )
            {
            var tr_element = document.createElement ( 'tr' );
            tr_element.className = 'bmlt_nouveau_search_results_list_body_tr ' + 'bmlt_nouveau_search_results_list_body_tr_' + (((i % 2) == 0) ? 'even' : 'odd');
        
            for ( var c = 0; c < g_Nouveau_array_header_text.length; c++ )
                {
                this.buildDOMTree_SearchResults_List_Table_Contents_Node_TD(this.m_search_results[i], c, tr_element);
                };
            this.m_list_search_results_table_body.appendChild ( tr_element );
            };
        };
        
    /************************************************************************************//**
    *	\brief 
    ****************************************************************************************/
    this.buildDOMTree_SearchResults_List_Table_Contents_Node_TD = function (    in_meeting_object,  ///< The meeting data line object.
                                                                                index,              ///< Which column it will be using.
                                                                                tr_element          ///< The tr element that will receive this line.
                                                                            )
        {
        var td_element = document.createElement ( 'td' );
        td_element.className = 'bmlt_nouveau_search_results_list_body_td';
        
        switch ( index )
            {
            case    0:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_nation';
                td_element.appendChild ( this.buildDOMTree_ConstructNationName ( in_meeting_object ) );
            break;
            
            case    1:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_state';
                td_element.appendChild ( this.buildDOMTree_ConstructStateName ( in_meeting_object ) );
            break;
            
            case    2:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_county';
                td_element.appendChild ( this.buildDOMTree_ConstructCountyName ( in_meeting_object ) );
            break;
            
            case    3:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_town';
                td_element.appendChild ( this.buildDOMTree_ConstructTownName ( in_meeting_object ) );
            break;
            
            case    4:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_name';
                td_element.appendChild ( this.buildDOMTree_ConstructMeetingName ( in_meeting_object ) );
            break;
            
            case    5:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_weekday';
                td_element.appendChild ( this.buildDOMTree_ConstructWeekday ( in_meeting_object ) );
            break;
            
            case    6:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_start';
                td_element.appendChild ( this.buildDOMTree_ConstructStartTime( in_meeting_object ) );
            break;
            
            case    7:
                td_element.className += ' bmlt_nouveau_search_results_list_body_td_location';
                td_element.appendChild ( this.buildDOMTree_ConstructLocation( in_meeting_object ) );
            break;
            
            default:
                td_element.className += ' bmlt_nouveau_hidden';
            break;
            };
        
        tr_element.appendChild ( td_element );
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructNationName = function ( in_meeting_object  ///< The meeting data line object.
                                                     )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_nation_name_div';

        if ( in_meeting_object['location_nation'] )
            {
            container_element.appendChild ( document.createTextNode ( in_meeting_object['location_nation'] ) );
            };
        
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructStateName = function ( in_meeting_object  ///< The meeting data line object.
                                                    )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_state_name_div';
        
        if ( in_meeting_object['location_province'] )
            {
            container_element.appendChild ( document.createTextNode ( in_meeting_object['location_province'] ) );
            };
        
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructCountyName = function ( in_meeting_object  ///< The meeting data line object.
                                                     )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_county_name_div';
        
        if ( in_meeting_object['location_city_subsection'] )
            {
            container_element.className += ' bmlt_nouveau_search_results_list_town_name_county_has_borough_div';
            };

        container_element.appendChild ( document.createTextNode ( in_meeting_object['location_sub_province'] ) );
        
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructTownName = function ( in_meeting_object  ///< The meeting data line object.
                                                   )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_town_name_div';
        
        if ( in_meeting_object['location_sub_province'] )
            {
            container_element.className += ' bmlt_nouveau_search_results_list_town_name_has_county_div';
            };
        
        var span_element = null;

        if ( in_meeting_object['location_municipality'] )
            {
            span_element = document.createElement ( 'span' );
            span_element.className = 'bmlt_nouveau_search_results_list_town_name_town_span';
            
            if ( in_meeting_object['location_city_subsection'] )
                {
                span_element.className += ' bmlt_nouveau_search_results_list_town_name_town_has_borough_span';
                };
                
            span_element.appendChild ( document.createTextNode ( in_meeting_object['location_municipality'] ) );
            container_element.appendChild ( span_element );
            };
        
        if ( in_meeting_object['location_city_subsection'] )
            {
            span_element = document.createElement ( 'span' );
            span_element.className = 'bmlt_nouveau_search_results_list_town_name_borough_span';
            
            if ( in_meeting_object['location_municipality'] )
                {
                span_element.className += ' bmlt_nouveau_search_results_list_town_name_borough_has_town_span';
                };

            span_element.appendChild ( document.createTextNode ( in_meeting_object['location_city_subsection'] ) );
            container_element.appendChild ( span_element );
            }
        
        if ( in_meeting_object['location_neighborhood'] )
            {
            span_element = document.createElement ( 'span' );
            span_element.className = 'bmlt_nouveau_search_results_list_town_name_neighborhood_span';
            span_element.appendChild ( document.createTextNode ( in_meeting_object['location_neighborhood'] ) );
            container_element.appendChild ( span_element );
            }

        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructMeetingName = function ( in_meeting_object  ///< The meeting data line object.
                                                        )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_meeting_name_div';
        
        if ( in_meeting_object['meeting_name'] )
            {
            container_element.appendChild ( document.createTextNode ( in_meeting_object['meeting_name'] ) );
            }
                    
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructWeekday = function ( in_meeting_object  ///< The meeting data line object.
                                                    )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_weekday_div';
        container_element.appendChild ( document.createTextNode(g_Nouveau_weekday_long_array[in_meeting_object['weekday_tinyint'] - 1] ) );
                    
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructStartTime = function ( in_meeting_object  ///< The meeting data line object.
                                                    )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_start_time_div';
        
        var time = (in_meeting_object['start_time'].toString()).split(':');

        time[0] = parseInt ( time[0], 10 );
        time[1] = parseInt ( time[1], 10 );
        var st = null;
        
        if ( (time[0] == 12) && (time[1] == 0) )
            {
            st = g_Nouveau_noon;
            }
        else if ( (time[0] == 23) && (time[1] > 45) )
            {
            st = g_Nouveau_noon;
            }
        else
            {
            var hours = (time[0] > 12) ? time[0] - 12 : time[0];
            var minutes = time[1];
            var a = ((time[0] > 12) || ((time[0] == 12) && (time[1] > 0))) ? g_Nouveau_pm : g_Nouveau_am;
            st = sprintf ( g_Nouveau_time_sprintf_format, hours, time[1], a );
            };
        
        container_element.appendChild ( document.createTextNode( st ) );
                    
        return container_element;
        };
        
    /************************************************************************************//**
    *	\brief 
    *   \returns
    ****************************************************************************************/
    this.buildDOMTree_ConstructLocation = function ( in_meeting_object  ///< The meeting data line object.
                                                    )
        {
        var container_element = document.createElement ( 'div' );
        container_element.className = 'bmlt_nouveau_search_results_list_location_div';
        
        var loc_text = '';
        
        if ( in_meeting_object['location_text'] && in_meeting_object['location_street'] && in_meeting_object['location_info'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_loc_street_info, in_meeting_object['location_text'], in_meeting_object['location_street'], in_meeting_object['location_info'] );
            }
        else if ( in_meeting_object['location_text'] && in_meeting_object['location_street'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_loc_street, in_meeting_object['location_text'], in_meeting_object['location_street'] );
            }
        else if ( in_meeting_object['location_street'] && in_meeting_object['location_info'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_street_info, in_meeting_object['location_street'], in_meeting_object['location_info'] );
            }
        else if ( in_meeting_object['location_text'] && in_meeting_object['location_info'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_loc_info, in_meeting_object['location_text'], in_meeting_object['location_info'] );
            }
        else if ( in_meeting_object['location_street'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_street, in_meeting_object['location_street'] );
            }
        else if ( in_meeting_object['location_text'] )
            {
            loc_text = sprintf ( g_Nouveau_location_sprintf_format_loc, in_meeting_object['location_text'] );
            }
        else
            {
            loc_text = g_Nouveau_location_sprintf_format_wtf;
            };

        container_element.appendChild ( document.createTextNode( loc_text ) );
        return container_element;
        };
                
    /************************************************************************************//**
    *	\brief This sets the state of the "MAP/TEXT" tab switch div. It actually changes    *
    *          the state of the anchors, so it is more than just a CSS class change.        *
    ****************************************************************************************/
    this.setListResultsDisclosure = function()
        {
        if ( this.m_listResultsDisplayed )
            {
            this.m_list_search_results_disclosure_a.className = 'bmlt_nouveau_search_results_list_disclosure_a bmlt_nouveau_search_results_list_disclosure_open_a';
            this.m_list_search_results_container_div.className = 'bmlt_nouveau_search_results_list_container_div';
            }
        else
            {
            this.m_list_search_results_disclosure_a.className = 'bmlt_nouveau_search_results_list_disclosure_a';
            this.m_list_search_results_container_div.className = 'bmlt_nouveau_search_results_list_container_div bmlt_nouveau_search_results_list_container_div_hidden';
            };
        };
        
    /************************************************************************************//**
    *	\brief This establishes the (usually invisible) throbber display.                   *
    ****************************************************************************************/
    this.buildDOMTree_CreateThrobberDiv = function ()
        {
        this.m_throbber_div = document.createElement ( 'div' );
        this.m_throbber_div.className = 'bmlt_nouveau_throbber_div bmlt_nouveau_throbber_div_hidden';
        
        var inner_div = document.createElement ( 'div' );
        inner_div.className = 'bmlt_nouveau_throbber_mask_div';

        this.m_throbber_div.appendChild ( inner_div );
        
        var inner_div = document.createElement ( 'div' );
        inner_div.className = 'bmlt_nouveau_throbber_inner_container_div';

        var inner_img = document.createElement ( 'img' );
        inner_img.className = 'bmlt_nouveau_throbber_img';
        inner_img.src = g_Nouveau_throbber_image_src;
        inner_img.setAttribute ( 'alt', 'Busy Throbber' );
        
        inner_div.appendChild ( inner_img );
        
        this.m_throbber_div.appendChild ( inner_div );
        
        this.m_display_div.appendChild ( this.m_throbber_div );
        };
    
    /****************************************************************************************
    *#################################### MAP HANDLERS #####################################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This starts a search immediately, for a basic map click.                     *
    ****************************************************************************************/
    this.basicdMapClicked = function ()
        {
        this.beginSearch();
        };

    /************************************************************************************//**
    *	\brief This moves the marker, in response to a map click.                           *
    ****************************************************************************************/
    this.advancedMapClicked = function ()
        {
        this.displayMarkerInAdvancedMap();
        };

    /************************************************************************************//**
    *	\brief This displays the marker and overlay for the advanced map.                   *
    ****************************************************************************************/
    this.displayMarkerInAdvancedMap = function ()
        {
        };

    /****************************************************************************************
    *################################### PERFORM SEARCH ####################################*
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief This function constructs a URI to the root server that reflects the search   *
    ****************************************************************************************/
    this.beginSearch = function ()
        {
        this.m_search_results = null;
        this.setDisplayedSearchResults();
        this.clearSearchResults();
        this.callRootServer ( this.createSearchURI() );
        };
        
    /************************************************************************************//**
    *	\brief This shows our "busy throbber."                                              *
    ****************************************************************************************/
    this.displayThrobber = function ()
        {
        this.m_throbber_div.className = 'bmlt_nouveau_throbber_div';
        };

    /************************************************************************************//**
    *	\brief This hides our "busy throbber."                                              *
    ****************************************************************************************/
    this.hideThrobber = function ()
        {
        this.m_throbber_div.className = 'bmlt_nouveau_throbber_div bmlt_nouveau_throbber_div_hidden';
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
        
        uri_elements[index] = new Array;
        uri_elements[index][0] = 'long_val';
        uri_elements[index++][1] = this.m_current_long;
        
        uri_elements[index] = new Array;
        uri_elements[index][0] = 'lat_val';
        uri_elements[index++][1] = this.m_current_lat;
            
        // First, if we have a map up, we use the specified width. (not done if the search is specified using text).
        // This restricts the search area.
        if ( (this.m_current_view == 'map') || (this.m_current_view == 'advanced map') )
            {
            uri_elements[index] = new Array;
            uri_elements[index][0] = 'geo_width';

            // In the case of the advanced map, we will also have a radius value. Otherwise, we use the default auto.
            uri_elements[index++][1] = (this.m_current_view == 'advanced map') ? this.m_search_radius : g_Nouveau_default_geo_width;
            }
        else    // Otherwise, we use whatever is in the text box.
            {
            var search_text = this.m_text_input.value;
            
            if ( search_text )
                {
                uri_elements[index] = new Array;
                uri_elements[index][0] = 'SearchString';
                uri_elements[index++][1] = escape(search_text);
                
                // Make sure that all the text is used.
                uri_elements[index] = new Array;
                uri_elements[index][0] = 'SearchStringAll';
                uri_elements[index++][1] = 1;
                };
                
            if ( this.m_location_checkbox.checked )
                {
                uri_elements[index] = new Array;
                uri_elements[index][0] = 'StringSearchIsAnAddress';
                uri_elements[index++][1] = 1;
                };
            };
        
        // Concatenate all the various parameters we gathered.
        for ( var i = 0; i < index; i++ )
            {
            ret += '&' + uri_elements[i][0] + '=' + uri_elements[i][1];
            };
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
        this.displayThrobber();
	    if ( this.m_ajax_request )   // This prevents the requests from piling up. We are single-threaded.
	        {
	        this.m_ajax_request.abort();
	        this.m_ajax_request = null;
	        };
	    
        this.m_ajax_request = BMLTPlugin_AjaxRequest ( in_uri, NouveauMapSearch.prototype.sAJAXRouter, 'get', this.m_uid );
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
        this.analyzeSearchResults();
        this.m_search_results_shown = true;
        this.buildDOMTree_SearchResults_Section();
        this.m_mapResultsDisplayed = true;
        this.m_listResultsDisplayed = true;
        this.setDisplayedSearchResults();
        this.loadResultsMap();
        this.redrawMapMarkers();
        };
    
    /************************************************************************************//**
    *	\brief This sorts through all of the search results, and builds an array of fields  *
    *          for their display.                                                           *
    *          The principal reason for this function is to create a "box" that contains    *
    *          all of the meetings. This will be used to select an initial projection on    *
    *          the map display.                                                             *
    *          TODO: This needs some work to make it effective for the antimeridian.        *
    ****************************************************************************************/
    this.analyzeSearchResults = function ()
        {
        // These will be the result of this function.
        this.m_long_lat_northeast = { 'lng':this.m_current_long, 'lat':this.m_current_lat };  // This will contain the North, East corner of the map to encompass all the results.
        this.m_long_lat_southwest = { 'lng':this.m_current_long, 'lat':this.m_current_lat };  // Same for South, West.

        // We loop through the whole response.
		for ( var c = 0; c < this.m_search_results.length; c++ )
		    {
		    this.m_search_results[c].uid = this.m_uid;    // This will be used to anchor context in future callbacks. This is a convenient place to set it.
		    var theMeeting = this.m_search_results[c];
		    var mLNG = theMeeting['longitude'];
		    var mLAT = theMeeting['latitude'];
		    
		    if ( mLNG < this.m_long_lat_northeast.lng )
		        {
		        this.m_long_lat_northeast.lng = mLNG;
		        };
		    
		    if ( mLAT > this.m_long_lat_northeast.lat )
		        {
		        this.m_long_lat_northeast.lat = mLAT;
		        };
		    
		    if ( mLNG > this.m_long_lat_southwest.lng )
		        {
		        this.m_long_lat_southwest.lng = mLNG;
		        };
		    
		    if ( mLAT < this.m_long_lat_southwest.lat )
		        {
		        this.m_long_lat_southwest.lat = mLAT;
		        };
		    
		    if ( !theMeeting['distance_in_km'] )    // This should never be necessary, but just in case...
		        {
		        var distance_in_km = Math.abs ( google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng ( this.m_current_lat, this.m_current_long ), new google.maps.LatLng ( theMeeting['latitude'], theMeeting['longitude'] )) / 1000.0 );
		        this.m_search_results[c].distance_in_km = distance_in_km;
		        this.m_search_results[c].distance_in_miles = distance_in_km / 1.60934;
		        };
		    };
		
		this.sortSearchResults();
        };
    
    /************************************************************************************//**
    *	\brief This either hides or shows the search results.                               *
    ****************************************************************************************/
    this.sortSearchResults = function()
        {
        if ( this.m_search_results && this.m_search_results.length )    // Make sure we have something to sort.
            {
            this.m_search_results.sort ( NouveauMapSearch.prototype.sSortCallback );
            };
        };
        
    /************************************************************************************//**
    *	\brief This either hides or shows the search results.                               *
    ****************************************************************************************/
    this.setDisplayedSearchResults = function()
        {
        if ( !this.m_search_results )
            {
            this.m_search_results_shown = false;    // Can't show what doesn't exist.
            if ( this.m_search_results_div )
                {
                this.m_search_spec_switch_div.className = 'bmlt_nouveau_search_spec_switch_div bmlt_nouveau_search_spec_switch_div_hidden';
                this.m_search_results_div.className = 'bmlt_nouveau_search_results_div bmlt_nouveau_results_hidden';
                this.m_search_spec_div.className = 'bmlt_nouveau_search_spec_div';
                };
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

            this.setMapResultsDisclosure();
            this.setListResultsDisclosure();
            };
        };

    /***************************************************************************************
    *############################ INSTANCE CALLBACK FUNCTIONS #############################*
    *                                                                                      *
    * These functions are called for an instance, and have object context.                 *
    ****************************************************************************************/
    /************************************************************************************//**
    *	\brief Responds to the Specify A New Search link being hit.                         *
    ****************************************************************************************/
    this.searchSpecButtonHit = function()
        {
        this.m_search_results_shown = false;
        this.setDisplayedSearchResults();
        };
    
    /************************************************************************************//**
    *	\brief Responds to the Show Search Results link being hit.                          *
    ****************************************************************************************/
    this.searchResultsButtonHit = function()
        {
        this.m_search_results_shown = true;
        this.setDisplayedSearchResults();
        };

    /************************************************************************************//**
    *	\brief Responds to either of the GO buttons being hit.                              *
    ****************************************************************************************/
    this.goButtonHit = function()
        {
        this.beginSearch();
        };

    /************************************************************************************//**
    *	\brief Toggles the state of the Basic/Advanced search spec display.                 *
    ****************************************************************************************/
    this.toggleAdvanced = function()
        {
        switch ( this.m_current_view )   // Vet the class state.
            {
            case 'map':
                this.m_current_view = 'advanced map';
                this.validateGoButtons();
            break;
        
            case 'advanced map':
                this.m_current_view = 'map';
            break;
        
            case 'text':
                this.m_current_view = 'advanced text';
                this.validateGoButtons();
            break;
        
            case 'advanced text':
                this.m_current_view = 'text';
                this.validateGoButtons();
            break;
            };
        
        this.setBasicAdvancedSwitch();
        };
        
    /************************************************************************************//**
    *	\brief Responds to the Search By Map link being hit.                                *
    ****************************************************************************************/
    this.mapButtonHit = function()
        {
        switch ( this.m_current_view )   // Vet the class state.
            {
            case 'text':
                this.m_current_view = 'map';
            break;
        
            case 'advanced text':
                this.m_current_view = 'advanced map';
                this.validateGoButtons();
            break;
            };
        
        this.setMapTextSwitch();
        };
        
    /************************************************************************************//**
    *	\brief Responds to the Search By Text button being hit.                             *
    ****************************************************************************************/
    this.textButtonHit = function()
        {
        switch ( this.m_current_view )   // Vet the class state.
            {
            case 'map':
                this.m_current_view = 'text';
                this.validateGoButtons();
            break;
        
            case 'advanced map':
                this.m_current_view = 'advanced text';
                this.validateGoButtons();
            break;
            };
        
        this.setMapTextSwitch();
        };
        
    /************************************************************************************//**
    *	\brief Toggles the display state of the Advanced Weekdays section.                  *
    ****************************************************************************************/
    this.toggleWeekdaysDisclosure = function()
        {
        this.m_advanced_weekdays_shown = !this.m_advanced_weekdays_shown;
    
        this.setAdvancedWeekdaysDisclosure();
        };
        
    /************************************************************************************//**
    *	\brief Toggles the display state of the Advanced Formats section.                   *
    ****************************************************************************************/
    this.toggleFormatsDisclosure = function()
        {
        this.m_advanced_formats_shown = !this.m_advanced_formats_shown;
    
        this.setAdvancedFormatsDisclosure();
        };
        
    /************************************************************************************//**
    *	\brief Toggles the display state of the Advanced Service Bodies section.            *
    ****************************************************************************************/
    this.toggleServiceBodiesDisclosure = function()
        {
        this.m_advanced_service_bodies_shown = !this.m_advanced_service_bodies_shown;
    
        this.setAdvancedServiceBodiesDisclosure();
        };
        
    /************************************************************************************//**
    *	\brief This is called when the Display Map Results Disclosure link is hit.          *
    ****************************************************************************************/
    this.displayMapResultsDiscolsureHit = function()
        {
        this.m_mapResultsDisplayed = !this.m_mapResultsDisplayed;
        this.setMapResultsDisclosure();
        };
        
    /************************************************************************************//**
    *	\brief This is called when the Display List Results Disclosure link is hit.         *
    ****************************************************************************************/
    this.displayListResultsDiscolsureHit = function()
        {
        this.m_listResultsDisplayed = !this.m_listResultsDisplayed;
        this.setListResultsDisclosure();
        };
        
    /************************************************************************************//**
    *	\brief Sets the state of the two GO buttons, as necessary.                          *
    ****************************************************************************************/
    this.validateGoButtons = function()
        {
        if ( this.m_text_input.value && (this.m_text_input.value != this.m_text_input.defaultValue) )
            {
            this.m_advanced_go_a.className = 'bmlt_nouveau_text_go_button_a';
            this.m_advanced_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.goButtonHit()' );
            this.m_text_go_a.className = 'bmlt_nouveau_text_go_button_a';
            this.m_text_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.goButtonHit()' );
            }
        else
            {
            if ( this.m_current_view == 'advanced text' )
                {
                this.m_advanced_go_a.className = 'bmlt_nouveau_text_go_button_a bmlt_nouveau_button_disabled';
                this.m_advanced_go_a.removeAttribute ( 'href' );
                }
            else
                {
                this.m_advanced_go_a.className = 'bmlt_nouveau_text_go_button_a';
                this.m_advanced_go_a.setAttribute ( 'href', 'javascript:g_instance_' + this.m_uid + '_js_handler.goButtonHit()' );
                };
            
            this.m_text_go_a.className = 'bmlt_nouveau_text_go_button_a bmlt_nouveau_button_disabled';
            this.m_text_go_a.removeAttribute ( 'href' );
            };
        };
    
    /************************************************************************************//**
    *	\brief Redraws the blue/red meeting markers.                                        *
    ****************************************************************************************/
    this.redrawMapMarkers = function()
        {
        // First, recalculate all the map markers.
        this.m_map_search_results_map.meeting_marker_array = NouveauMapSearch.prototype.sMapOverlappingMarkers ( this.m_search_results, this.m_map_search_results_map );
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
    
    this.m_checked_location = false;         ///< This is set at construction. If true, then the "Location" checkbox will be checked at startup.
    this.m_advanced_weekdays_shown = false;
    this.m_advanced_formats_shown = false;
    this.m_advanced_service_bodies_shown = false;
    this.m_mapResultsDisplayed = false;
    this.m_listResultsDisplayed = false;
    this.m_search_results_shown = false;         ///< If this is true, then the results div is displayed.
    this.m_search_radius = g_Nouveau_default_geo_width;
    
    this.m_search_sort_key = 'time';             ///< This can be 'time', 'town', 'name', or 'distance'.

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
*######################## CONTEXT-ESTABLISHING CALLBACK FUNCTIONS ##########################*
*                                                                                           *
* These functions are called statically, but establish context from an ID passed in.        *
********************************************************************************************/
/****************************************************************************************//**
*	\brief Will check a text element upon blur, and will fill it with the default string.   *
********************************************************************************************/
NouveauMapSearch.prototype.sCheckTextInputBlur = function ( in_text_element  ///< The text element being evaluated.
                                                            )
    {
    // This funky line creates an object context from the ID passed in.
    // Each object is represented by a dynamically-created global variable, defined by ID, so we access that.
    // 'context' becomes a placeholder for 'this'.
    eval ('var context = g_instance_' + in_text_element.uid + '_js_handler');

    context.validateGoButtons();
        
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
NouveauMapSearch.prototype.sCheckTextInputKeyUp = function ( in_text_element ///< The text element being evaluated.
                                                            )
    {
    eval ('var context = g_instance_' + in_text_element.uid + '_js_handler');

    context.validateGoButtons();

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
NouveauMapSearch.prototype.sCheckTextInputFocus = function ( in_text_element ///< The text element being evaluated.
                                                            )
    {
    eval ('var context = g_instance_' + in_text_element.uid + '_js_handler');

    context.validateGoButtons();
    
    if ( in_text_element.value && (in_text_element.value == in_text_element.defaultValue) )
        {
        in_text_element.value = '';
        };
    };

/****************************************************************************************//**
*	\brief Responds to a click in the map.                                                  *
********************************************************************************************/
NouveauMapSearch.prototype.sMapClicked = function ( in_event,   ///< The map event
                                                    in_id       ///< The unique ID of the object (establishes context).
                                                    )
    {
    eval ('var context = g_instance_' + in_id + '_js_handler');
	
	// We set the long/lat from the event.
	context.m_current_long = in_event.latLng.lng().toString();
	context.m_current_lat = in_event.latLng.lat().toString();

    if ( context.m_current_view == 'map' ) // If it is a simple map, we go straight to a search.
        {
        context.basicdMapClicked();
        }
    else    // Otherwise, we simply move the marker.
        {
        context.advancedMapClicked();
        };
    };

/****************************************************************************************//**
*	\brief Responds to a click in the map.                                                  *
********************************************************************************************/
NouveauMapSearch.prototype.sMapZoomChanged = function ( in_event,   ///< The map event
                                                        in_id       ///< The unique ID of the object (establishes context).
                                                        )
    {
    eval ('var context = g_instance_' + in_id + '_js_handler');
    
    context.redrawMapMarkers();
    };
	
/****************************************************************************************//**
*	\brief This is the AJAX callback from a search request.                                 *
********************************************************************************************/
NouveauMapSearch.prototype.sAJAXRouter = function ( in_response_object, ///< The HTTPRequest response object.
                                                    in_id               ///< The unique ID of the object (establishes context).
                                                    )
    {
    eval ('var context = g_instance_' + in_id + '_js_handler');
    
    if ( context )
        {
        context.m_ajax_request = null;
        context.m_search_results = null;
        context.hideThrobber();
        
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
*	\brief Used to sort the search results. Context is established by fetching the          *
*          'context' data member of either of the passed in objects.                        *
*   \returns -1 if a<b, 0 if a==b, and 1 if a>b                                             *
********************************************************************************************/
NouveauMapSearch.prototype.sSortCallback = function( in_obj_a,
                                                     in_obj_b
                                                    )
    {
    eval ('var context = g_instance_' + in_obj_a.uid.toString() + '_js_handler;' );
    
    var ret = 0;
    
    switch ( context.m_search_sort_key )
        {
        case 'distance':
            if ( in_obj_a.distance_in_km < in_obj_b.distance_in_km )
                {
                ret = -1;
                }
            else if ( in_obj_a.distance_in_km > in_obj_b.distance_in_km )
                {
                ret = 1;
                };
        
        // We try the town, next (Very doubtful this will ever happen).
        
        case 'town':
            if ( ret == 0 )
                {
                var a_nation = in_obj_a.location_province.replace(/\s/g, "").toLowerCase();
                var a_state = in_obj_a.location_province.replace(/\s/g, "").toLowerCase();
                var a_county = in_obj_a.location_sub_province.replace(/\s/g, "").toLowerCase();
                var a_town = in_obj_a.location_municipality.replace(/\s/g, "").toLowerCase();
                var a_borough = in_obj_a.location_city_subsection.replace(/\s/g, "").toLowerCase();
            
                var b_nation = in_obj_b.location_province.replace(/\s/g, "").toLowerCase();
                var b_state = in_obj_b.location_province.replace(/\s/g, "").toLowerCase();
                var b_county = in_obj_b.location_sub_province.replace(/\s/g, "").toLowerCase();
                var b_town = in_obj_b.location_municipality.replace(/\s/g, "").toLowerCase();
                var b_borough = in_obj_b.location_city_subsection.replace(/\s/g, "").toLowerCase();
            
                // We bubble down through the various levels of location.
                // One of the participants being missing prevents comparison.
                if ( a_nation && b_nation )
                    {
                    if ( a_nation < b_nation )
                        {
                        ret = -1;
                        }
                    else if ( a_nation > b_nation )
                        {
                        ret = 1;
                        };
                    };
                
                if ( ret == 0 && a_state && b_state )
                    {
                    if ( a_state < b_state )
                        {
                        ret = -1;
                        }
                    else if ( a_state > b_state )
                        {
                        ret = 1;
                        };
                    };
                
                if ( ret == 0 && a_county && b_county )
                    {
                    if ( a_county < b_county )
                        {
                        ret = -1;
                        }
                    else if ( a_county > b_county )
                        {
                        ret = 1;
                        };
                    };
                
                if ( ret == 0 && a_town && b_town )
                    {
                    if ( a_town < b_town )
                        {
                        ret = -1;
                        }
                    else if ( a_town > b_town )
                        {
                        ret = 1;
                        };
                    };
                
                if ( ret == 0 && a_borough && b_borough )
                    {
                    if ( a_borough < b_borough )
                        {
                        ret = -1;
                        }
                    else if ( a_borough > b_borough )
                        {
                        ret = 1;
                        };
                    };
                };
            
        // We sort by time for the same town.
        
        default:    // 'time' is default
            if ( ret == 0 )
                {
                if ( in_obj_a.weekday_tinyint < in_obj_b.weekday_tinyint )
                    {
                    ret = -1;
                    }
                else if ( in_obj_a.weekday_tinyint > in_obj_b.weekday_tinyint )
                    {
                    ret = 1;
                    }
                else
                    {
                    var time_a = parseInt ( (in_obj_a.start_time.toString().replace(/[\s:]/g, "")), 10);
                    var time_b = parseInt ( (in_obj_b.start_time.toString().replace(/[\s:]/g, "")), 10);
                
                    if ( time_a < time_b )
                        {
                        ret = -1;
                        }
                    else if ( time_a > time_b )
                        {
                        ret = 1;
                        };
                    };
                };
        
        // And finally, by meeting name.
                
        case 'name':
            if ( ret == 0 )
                {
                var a_name = in_obj_a.meeting_name.replace(/\s/g, "").toLowerCase();
                var b_name = in_obj_b.meeting_name.replace(/\s/g, "").toLowerCase();
            
                if ( a_name < b_name )
                    {
                    ret = -1;
                    }
                else if ( a_name > b_name )
                    {
                    ret = 1;
                    };
                };
        break;
        };
        
    return ret;
    };
	
/****************************************************************************************//**
*	\brief	This returns an array, mapping out markers that overlap.					    *
*																						    *
*	\returns An array of arrays. Each array element is an array with n >= 1 elements, each  *
*	of which is a meeting object. Each of the array elements corresponds to a single        *
*	marker, and all the objects in that element's array will be covered by that one marker. *
*	The returned sub-arrays will be sorted in order of ascending weekday.	                *
********************************************************************************************/
	
NouveauMapSearch.prototype.sMapOverlappingMarkers = function (  in_meeting_array,	///< Used to draw the markers when done.
	                                                            in_map_object       ///< The map instance to use.
									                        )
    {
    var tolerance = 8;	/* This is how many pixels we allow. */
    var tmp = new Array;
    
    for ( var c = 0; c < in_meeting_array.length; c++ )
        {
        tmp[c] = new Object;
        tmp[c].matched = false;
        tmp[c].matches = null;
        tmp[c].object = in_meeting_array[c];
        tmp[c].coords = NouveauMapSearch.prototype.sFromLatLngToPixel ( new google.maps.LatLng ( tmp[c].object.latitude, tmp[c].object.longitude ), in_map_object );
        };
    
    for ( var c = 0; c < in_meeting_array.length; c++ )
        {
        if ( false == tmp[c].matched )
            {
            tmp[c].matched = true;
            tmp[c].matches = new Array;
            tmp[c].matches[0] = tmp[c].object;

            for ( var c2 = 0; c2 < in_meeting_array.length; c2++ )
                {
                if ( false == tmp[c2].matched )
                    {
                    var outer_coords = tmp[c].coords;
                    var inner_coords = tmp[c2].coords;
                    
                    var xmin = outer_coords.x - tolerance;
                    var xmax = outer_coords.x + tolerance;
                    var ymin = outer_coords.y - tolerance;
                    var ymax = outer_coords.y + tolerance;
                    
                    /* We have an overlap. */
                    if ( (inner_coords.x >= xmin) && (inner_coords.x <= xmax) && (inner_coords.y >= ymin) && (inner_coords.y <= ymax) )
                        {
                        tmp[c].matches[tmp[c].matches.length] = tmp[c2].object;
                        tmp[c2].matched = true;
                        };
                    };
                };
            };
        };

    var ret = new Array;
    
    for ( var c = 0; c < tmp.length; c++ )
        {
        if ( tmp[c].matches )
            {
            tmp[c].matches.sort ( function(a,b){return a.weekday_tinyint-b.weekday_tinyint});
            ret[ret.length] = tmp[c].matches;
            };
        };
    
    return ret;
    };
    
/****************************************************************************************//**
*	\brief This takes a latitude/longitude location, and returns an x/y pixel location for  *
*	it.																				        *
*																						    *
*	\returns a Google Maps API V3 Point, with the pixel coordinates (top, left origin).	    *
********************************************************************************************/
    
NouveauMapSearch.prototype.sFromLatLngToPixel = function (  in_Latng,
                                                            in_map_object
                                                        )
    {
    var	ret = null;
    
    // We measure the container div element.
    var	div = in_map_object.getDiv();
    
    if ( div )
        {
        var	pixel_width = div.offsetWidth;
        var	pixel_height = div.offsetHeight;
        var	lat_lng_bounds = in_map_object.getBounds();
        var north_west_corner = new google.maps.LatLng ( lat_lng_bounds.getNorthEast().lat(), lat_lng_bounds.getSouthWest().lng() );
        var lng_width = lat_lng_bounds.getNorthEast().lng()-lat_lng_bounds.getSouthWest().lng();
        var	lat_height = lat_lng_bounds.getNorthEast().lat()-lat_lng_bounds.getSouthWest().lat();
        
        // We do this, so we have the largest values possible, to get the most accuracy.
        var	pixels_per_degree = (( pixel_width > pixel_height ) ? (pixel_width / lng_width) : (pixel_height / lat_height));
        
        // Figure out the offsets, in long/lat degrees.
        var	offset_vert = north_west_corner.lat() - in_Latng.lat();
        var	offset_horiz = in_Latng.lng() - north_west_corner.lng();
        
        ret = new google.maps.Point ( Math.round(offset_horiz * pixels_per_degree),  Math.round(offset_vert * pixels_per_degree) );
        };

    return ret;
    };
