/****************************************************************************************//**
* \file map_search.js																        *
* \brief Javascript functions for the new map search implementation.                        *
* \version 1.0                                                                              *
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
*	\brief  This class governs the display of one APIV3 map search instance. It plays games *
*           with dynamic DOM construction and complex IDs, because it is designed to allow  *
*           multiple instances on a page.                                                   *
********************************************************************************************/

function MapSearch (
                    in_id,
                    in_div,
                    in_coords
                    )
{
	/****************************************************************************************
	*										CLASS VARIABLES									*
	****************************************************************************************/
	
	var	g_main_map = null;				///< This will hold the Google Map object.
	var	g_allMarkers = [];				///< Holds all the markers.
	var g_main_id = in_id;

	/// These describe the regular NA meeting icon
	var g_icon_image_single = new google.maps.MarkerImage ( c_g_BMLTPlugin_images+"/NAMarker.png", new google.maps.Size(23, 32), new google.maps.Point(0,0), new google.maps.Point(12, 32) );
	var g_icon_image_multi = new google.maps.MarkerImage ( c_g_BMLTPlugin_images+"/NAMarkerG.png", new google.maps.Size(23, 32), new google.maps.Point(0,0), new google.maps.Point(12, 32) );
	var g_icon_shadow = new google.maps.MarkerImage( c_g_BMLTPlugin_images+"/NAMarkerS.png", new google.maps.Size(43, 32), new google.maps.Point(0,0), new google.maps.Point(12, 32) );
	var g_icon_shape = { coord: [16,0,18,1,19,2,20,3,21,4,21,5,22,6,22,7,22,8,22,9,22,10,22,11,22,12,22,13,22,14,22,15,22,16,21,17,21,18,22,19,20,20,19,21,20,22,18,23,17,24,18,25,17,26,15,27,14,28,15,29,12,30,12,31,10,31,10,30,9,29,8,28,8,27,7,26,6,25,5,24,5,23,4,22,3,21,3,20,2,19,1,18,1,17,1,16,0,15,0,14,0,13,0,12,0,11,0,10,0,9,0,8,0,7,1,6,1,5,2,4,2,3,3,2,5,1,6,0,16,0], type: 'poly' };
	
	/// These describe the "You are here" icon.
	var g_center_icon_image = new google.maps.MarkerImage ( c_g_BMLTPlugin_images+"/NACenterMarker.png", new google.maps.Size(21, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36) );
	var g_center_icon_shadow = new google.maps.MarkerImage( c_g_BMLTPlugin_images+"/NACenterMarkerS.png", new google.maps.Size(43, 36), new google.maps.Point(0,0), new google.maps.Point(11, 36) );
	var g_center_icon_shape = { coord: [16,0,18,1,19,2,19,3,20,4,20,5,20,6,20,7,20,8,20,9,20,10,20,11,19,12,17,13,16,14,16,15,15,16,15,17,14,18,14,19,13,20,13,21,13,22,13,23,12,24,12,25,12,26,12,27,11,28,11,29,11,30,11,31,11,32,11,33,11,34,11,35,10,35,10,34,9,33,9,32,9,31,9,30,9,29,9,28,8,27,8,26,8,25,8,24,8,23,7,22,7,21,7,20,6,19,6,18,5,17,5,16,4,15,4,14,3,13,1,12,0,11,0,10,0,9,0,8,0,7,0,6,0,5,0,4,1,3,1,2,3,1,4,0,16,0], type: 'poly' };

	/****************************************************************************************
	*									GOOGLE MAPS STUFF									*
	****************************************************************************************/
	
	/************************************************************************************//**
	*	\brief Load the map and set it up.													*
	****************************************************************************************/
	
	function load_map ( in_div, in_location_coords )
	{
        var g_main_div = document.createElement("div");
        
        if ( g_main_div )
            {
            g_main_div.className = 'bmlt_search_map_div';
            g_main_div.id = g_main_id+'_bmlt_search_map_div';
            g_main_div.myThrobber = null;
            
            in_div.appendChild ( g_main_div );
            
            if ( g_main_div && in_location_coords )
                {
                var myOptions = {
                                    'center': new google.maps.LatLng ( in_location_coords.latitude, in_location_coords.longitude ),
                                    'zoom': in_location_coords.zoom,
                                    'mapTypeId': google.maps.MapTypeId.ROADMAP,
                                    'mapTypeControlOptions': { 'style': google.maps.MapTypeControlStyle.DROPDOWN_MENU },
                                    'zoomControl': true,
                                    'mapTypeControl': true,
                                    'disableDoubleClickZoom' : true
                                };
    
                var	pixel_width = in_div.offsetWidth;
                var	pixel_height = in_div.offsetHeight;
                
                if ( (pixel_width < 640) || (pixel_height < 640) )
                    {
                    myOptions.scrollwheel = true;
                    myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.SMALL };
                    }
                else
                    {
                    myOptions.zoomControlOptions = { 'style': google.maps.ZoomControlStyle.LARGE };
                    };
                    
                g_main_map = new google.maps.Map ( g_main_div, myOptions );
                };
            
            if ( g_main_map )
                {
                g_main_map.response_object = null;
                g_main_map.uid = g_main_div.id+'-MAP';
                google.maps.event.addListener ( g_main_map, 'click', map_clicked );
                create_throbber ( g_main_div );
                };
            };
	};
	
	/************************************************************************************//**
	*	\brief 
	****************************************************************************************/
	
	function create_throbber ( in_div    ///< The container div for the throbber.
	                        )
	{
	    if ( !g_main_map.myThrobber )
	        {
            g_main_map.myThrobber = document.createElement("div");
            if ( g_main_map.myThrobber )
                {
                g_main_map.myThrobber.id = in_div.id+'_throbber_div';
                g_main_map.myThrobber.className = 'bmlt_map_throbber_div';
                g_main_map.myThrobber.style.display = 'none';
                in_div.appendChild ( g_main_map.myThrobber );
                var img = document.createElement("img");
                
                if ( img )
                    {
                    eval ( 'var srcval = c_g_BMLTPlugin_throbber_img_src_'+g_main_id+';' );
                    img.src = srcval;
                    img.className = 'bmlt_map_throbber_img';
                    img.id = in_div.id+'_throbber_img';
                    img.alt = 'AJAX Throbber';
                    g_main_map.myThrobber.appendChild ( img );
                    }
                else
                    {
                    in_div.myThrobber = null;
                    };
                };
            };
        };
        
	/************************************************************************************//**
	*	\brief 
	****************************************************************************************/
	
	function show_throbber()
	{
	    if ( g_main_map.myThrobber )
	        {
	        g_main_map.myThrobber.style.display = 'block';
	        };
    };
        
	/************************************************************************************//**
	*	\brief 
	****************************************************************************************/
	
	function hide_throbber()
	{
	    if ( g_main_map.myThrobber )
	        {
	        g_main_map.myThrobber.style.display = 'none';
	        };
    };
    
	/************************************************************************************//**
	*	\brief Respond to initial map click.                                                *
	****************************************************************************************/
	
	function map_clicked ( in_event ///< The mouse event that caused the click.
	                        )
	{
	    show_throbber();
	    clearAllMarkers();
	    g_main_map.response_object = null;
	    
	    if ( g_main_map.zoom_handler )
	        {
	        google.maps.event.removeListener ( g_main_map.zoom_handler );
	        };
	    
	    g_main_map.zoom_handler = null;
	    
	    var args = 'geo_width=-10'+'&long_val='+in_event.latLng.lng().toString()+'&lat_val='+in_event.latLng.lat().toString();
	    
	    g_main_map.g_location_coords = in_event.latLng;
	    
	    call_root_server ( args );
	};
	
	/************************************************************************************//**
	*	\brief  Does an AJAX call for a JSON response, based on the given criteria and      *
	*           callback function.                                                          *
	*           The callback will be a function in the following format:                    *
	*               function ajax_callback ( in_json_obj )                                  *
	*           where "in_json_obj" is the response, converted to a JSON object.            *
	*           it will be null if the function failed.                                     *
	****************************************************************************************/
	
	function call_root_server ( in_args
	                            )
	{
	    eval ( 'var url = c_g_BMLTRoot_URI_JSON_SearchResults_'+g_main_id+'+\'&\'+in_args;' );
        BMLTPlugin_AjaxRequest ( url, bmlt_ajax_router, 'get' );
	};
	
	/************************************************************************************//**
	*	\brief  
	****************************************************************************************/
	
	function bmlt_ajax_router ( in_response_object,
	                            in_extra
	                            )
	{
		var text_reply = in_response_object.responseText;
		
		if ( text_reply )
			{
	        var json_builder = 'var response_object = '+text_reply+';';
	        
            eval ( json_builder );
            
            if ( response_object )
                {
                if ( !g_main_map.response_object )
                    {
                    g_main_map.response_object = response_object;
                    
                    search_response_callback();
                    }
                else
                    {
                    };
                };
	        };
	};
	
	/************************************************************************************//**
	*	\brief 
	****************************************************************************************/
	
	function search_response_callback()
	{
		if ( !g_allMarkers.length )
			{
	        fit_markers();
            g_main_map.zoom_handler = google.maps.event.addListener ( g_main_map, 'zoom_changed', search_response_callback );
			};
		
		draw_markers();
	    hide_throbber();
	};
	
	/************************************************************************************//**
	*	\brief Determine the zoom level necessary to show all the markers in the viewport.	*
	****************************************************************************************/
	
	function fit_markers()
	{
		var bounds = new google.maps.LatLngBounds();
		
		// We go through all the results, and get the "spread" from them.
		for ( var c = 0; c < g_main_map.response_object.length; c++ )
			{
			var	lat = g_main_map.response_object[c].latitude;
			var	lng = g_main_map.response_object[c].longitude;
			// We will set our minimum and maximum bounds.
			bounds.extend ( new google.maps.LatLng ( lat, lng ) );
			};
	
		bounds.extend ( g_main_map.g_location_coords );
		
		// We now have the full rectangle of our meeting search results. Scale the map to fit them.
		g_main_map.fitBounds ( bounds );
	};
	
	/****************************************************************************************
	*									CREATING MARKERS									*
	****************************************************************************************/
	
	/************************************************************************************//**
	*	\brief Remove all the markers.														*
	****************************************************************************************/
	
	function clearAllMarkers ( )
	{
		if ( g_allMarkers )
			{
			for ( var c = 0; c < g_allMarkers.length; c++ )
				{
				if ( g_allMarkers[c].info_win_ )
				    {
				    g_allMarkers[c].info_win_.close();
				    g_allMarkers[c].info_win_ = null;
				    };
				
				g_allMarkers[c].setMap( null );
				g_allMarkers[c] = null;
				};
			
			g_allMarkers.length = 0;
			};
	};
	
	/************************************************************************************//**
	*	\brief Calculate and draw the markers.												*
	****************************************************************************************/
	
	function draw_markers()
	{
	    clearAllMarkers();
	    
		// This calculates which markers are the red "multi" markers.
		var overlap_map = mapOverlappingMarkers ( g_main_map.response_object );
		
		// Draw the meeting markers.
		for ( var c = 0; c < overlap_map.length; c++ )
			{
			createMapMarker ( overlap_map[c] );
			};
		
		// Finish with the main (You are here) marker.
		createMarker ( g_main_map.g_location_coords, g_center_icon_shadow, g_center_icon_image, g_center_icon_shape );
	};
	
	/************************************************************************************//**
	*	\brief	This returns an array, mapping out markers that overlap.					*
	*																						*
	*	\returns An array of arrays. Each array element is an array with n >= 1 elements,	*
	*	each of which is a meeting object. Each of the array elements corresponds to a		*
	*	single marker, and all the objects in that element's array will be covered by that	*
	*	one marker. The returned sub-arrays will be sorted in order of ascending weekday.	*
	****************************************************************************************/
	
	function mapOverlappingMarkers (in_meeting_array	///< Used to draw the markers when done.
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
			tmp[c].coords = fromLatLngToPixel ( new google.maps.LatLng ( tmp[c].object.latitude, tmp[c].object.longitude ), g_main_map );
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
	
	/************************************************************************************//**
	*	 \brief	This creates a single meeting's marker on the map.							*
	****************************************************************************************/
	
	function createMapMarker (	in_mtg_obj_array	/**< A meeting object array. */
								)
	{
		var main_point = new google.maps.LatLng ( in_mtg_obj_array[0].latitude, in_mtg_obj_array[0].longitude );
		
		var	marker_html = '<div class="meeting_info_window_contents_div';
		
		if ( in_mtg_obj_array.length > 1 )
			{
			marker_html += '_multi">';
			var included_weekdays = [];
			
			for ( var c = 0; c < in_mtg_obj_array.length; c++ )
				{
				var already_there = false;
				for ( var c2 = 0; c2 < included_weekdays.length; c2++ )
					{
					if ( included_weekdays[c2] == in_mtg_obj_array[c].weekday_tinyint )
						{
						already_there = true;
						break;
						};
					};
				
				if ( !already_there )
					{
					included_weekdays[included_weekdays.length] = in_mtg_obj_array[c].weekday_tinyint;
					};
				};
			
			marker_html += '<div class="multi_day_info_div"><fieldset class="marker_fieldset">';
			marker_html += '<legend>';
			
			if ( included_weekdays.length > 1 )
				{
				marker_html += '<select id="sel_'+g_main_map.uid+'_'+in_mtg_obj_array[0].id_bigint.toString()+'" onchange="marker_change_day(\'sel_'+g_main_map.uid+'_'+in_mtg_obj_array[0].id_bigint.toString()+'\',\''+in_mtg_obj_array[0].id_bigint.toString()+'\')">';
				
				for ( var wd = 1; wd < 8; wd++ )
					{
					for ( var c = 0; c < included_weekdays.length; c++ )
						{
						if ( included_weekdays[c] == wd )
							{
							marker_html += '<option value="'+included_weekdays[c]+'">'+c_g_weekdays[included_weekdays[c]]+'</option>';
							}
						}
					};
				marker_html += '</select>';
				}
			else
				{
				marker_html += '<strong>'+c_g_weekdays[included_weekdays[0]]+'</strong>';
				};
			
			marker_html += '</legend>';
			var	first = true;
			for ( var wd = 1; wd < 8; wd++ )
				{
				marker_internal_html = '';
				var	meetings_html = [];
				for ( var c = 0; c < in_mtg_obj_array.length; c++ )
					{
					if ( in_mtg_obj_array[c].weekday_tinyint == wd )
						{
						meetings_html[meetings_html.length] = marker_make_meeting ( in_mtg_obj_array[c] );
						};
					};
				
				if ( meetings_html.length )
					{
					marker_internal_html += '<div class="marker_div_weekday marker_div_weekday_'+wd.toString()+'" style="display:';
					if ( first )
						{
						marker_internal_html += 'block'; 
						first = false;
						}
					else
						{
						marker_internal_html += 'none'; 
						};
						
					marker_internal_html += '" id="sel_'+g_main_map.uid+'_'+in_mtg_obj_array[0].id_bigint.toString()+'_marker_'+in_mtg_obj_array[0].id_bigint.toString()+'_'+wd.toString()+'_id">';
					for ( var c2 = 0; c2 < meetings_html.length; c2++ )
						{
						if ( c2 > 0 )
							{
							marker_internal_html += '<hr class="meeting_divider_hr" />';
							};
						marker_internal_html += meetings_html[c2];
						};
					marker_internal_html += '</div>';
					marker_html += marker_internal_html;
					};
				};
			marker_html += '</fieldset></div>';
			}
		else
			{
			marker_html += '">';
			marker_html += marker_make_meeting ( in_mtg_obj_array[0], c_g_weekdays[in_mtg_obj_array[0].weekday_tinyint] );
			};
		
		marker_html += '</div>';
		var marker = createMarker ( main_point, g_icon_shadow, ((in_mtg_obj_array.length>1) ? g_icon_image_multi : g_icon_image_single), g_icon_shape, marker_html );
	};
	
	/************************************************************************************//**
	*	\brief Return the HTML for a meeting marker info window.							*
	*																						*
	*	\returns the XHTML for the meeting marker.											*
	****************************************************************************************/
	
	function marker_make_meeting ( in_meeting_obj,
									in_weekday )
	{
		var ret = '';
		
		ret = '<div class="marker_div_meeting">';
		ret += '<h4>'+in_meeting_obj.meeting_name.toString()+'</h4>';
		
		var	time = in_meeting_obj.start_time.toString().split(':');

		if ( time[0][0] == '0' )
			{
			time[0] = parseInt(time[0][1]);
			};
		
		var time_str = '';
		
		if ( in_weekday )
			{
			time_str = in_weekday.toString()+' ';
			};
			
		if ( (parseInt ( time[0] ) == 12) && (parseInt ( time[0] ) == 0) )
			{
			time_str += c_g_Noon;
			}
		else
			{
			if ( (parseInt ( time[0] ) == 23) && (parseInt ( time[1] ) >= 50) )
				{
				time_str += c_g_Midnight;
				}
			else
				{
				if ( parseInt ( time[0] ) > 12 )
					{
					time[0] = (parseInt ( time[0] ) - 12);
					time[2] = 'PM';
					}
				else
					{
					if ( parseInt ( time[0] ) == 12 )
						{
						time[2] = 'PM';
						}
					else
						{
						time[2] = 'AM';
						};
					};
				time_str += time[0]+':'+time[1]+' '+time[2];
				};
			};
		
		ret += '<h5>'+time_str+'</h5>';
		
		var location = '';
		
		if ( in_meeting_obj.location_text )
			{
			ret += '<div class="marker_div_location_text">'+in_meeting_obj.location_text.toString()+'</div>';
			};
		
		if ( in_meeting_obj.location_street )
			{
			ret += '<div class="marker_div_location_street">'+in_meeting_obj.location_street.toString()+'</div>';
			};
		
		if ( in_meeting_obj.location_municipality )
			{
			ret += '<div class="marker_div_location_municipality">'+in_meeting_obj.location_municipality.toString();
			if ( in_meeting_obj.location_province )
				{
				ret += '<span class="marker_div_location_province">, '+in_meeting_obj.location_province.toString()+'</span>';
				};
			ret += '</div>';
			};
		
		if ( in_meeting_obj.location_info )
			{
			ret += '<div class="marker_div_location_info">'+in_meeting_obj.location_info.toString()+'</div>';
			};
		
		if ( in_meeting_obj.comments )
			{
			ret += '<div class="marker_div_location_info">'+in_meeting_obj.comments.toString()+'</div>';
			};
		
		ret += '<div class="marker_div_location_maplink"><a href="';
		url = '';
		
		var comma = false;
		if ( in_meeting_obj.meeting_name )
			{
			url += encodeURIComponent(in_meeting_obj.meeting_name.toString());
			comma = true;
			};
			
		if ( in_meeting_obj.location_text )
			{
			url += (comma ? ',+' : '')+encodeURIComponent(in_meeting_obj.location_text.toString());
			comma = true;
			};
		
		if ( in_meeting_obj.location_street )
			{
			url += (comma ? ',+' : '')+encodeURIComponent(in_meeting_obj.location_street.toString());
			comma = true;
			};
		
		if ( in_meeting_obj.location_municipality )
			{
			url += (comma ? ',+' : '')+encodeURIComponent(in_meeting_obj.location_municipality.toString());
			comma = true;
			};
			
		if ( in_meeting_obj.location_province )
			{
			url += (comma ? ',+' : '')+encodeURIComponent(in_meeting_obj.location_province.toString());
			};
		
		url = url.toString().replace(/[\(\)]/gi,'-');
		
		url = '+(%22' + url + '%22)';
		
		url = 'http://maps.google.com/maps?q='+encodeURIComponent(in_meeting_obj.latitude.toString())+','+encodeURIComponent(in_meeting_obj.longitude.toString()) + url + '&amp;ll='+encodeURIComponent(in_meeting_obj.latitude.toString())+','+encodeURIComponent(in_meeting_obj.longitude.toString());

		ret += url + '" rel="external">'+c_g_map_link_text+'</a>';
		ret += '</div>';
		 
		if ( in_meeting_obj.distance_in_km )
			{
            eval ( 'var dist_r_km = c_g_distance_units_are_km_'+g_main_id+';var dist_units = c_g_distance_units_'+g_main_id+';' );
			ret += '<div class="marker_div_distance"><span class="distance_span">'+c_g_distance_prompt+':</span> '+(Math.round((dist_r_km ? in_meeting_obj.distance_in_km : in_meeting_obj.distance_in_miles) * 10)/10).toString()+' '+dist_units;
			ret += '</div>';
			};
		 
		if ( in_meeting_obj.formats )
			{
			ret += '<div class="marker_div_formats"><span class="formats_span">'+c_g_formats+':</span> '+in_meeting_obj.formats;
			ret += '</div>';
			};
	
		ret += '</div>';
		
		return ret;
	}
	
	/************************************************************************************//**
	*	\brief Create a generic marker.														*
	*																						*
	*	\returns a marker object.															*
	****************************************************************************************/
	
	function createMarker (	in_coords,		///< The long/lat for the marker.
							in_shadow_icon,	///< The URI for the icon shadow
							in_main_icon,	///< The URI for the main icon
							in_shape,		///< The shape for the marker
							in_html			///< The info window HTML
							)
	{
		var marker = null;
		
		if ( in_coords )
			{
			var	is_clickable = (in_html ? true : false);

			var marker = new google.maps.Marker ( { 'position':		in_coords,
													'map':			g_main_map,
													'shadow':		in_shadow_icon,
													'icon':			in_main_icon,
													'shape':		in_shape,
													'clickable':	is_clickable,
													'cursor':		'default'
													} );
			if ( marker )
				{
				marker.all_markers_ = g_allMarkers;
				if ( in_html )
					{
					google.maps.event.addListener ( marker, "click", function () {
																				for(var c=0; c < this.all_markers_.length; c++)
																					{
																					if ( this.all_markers_[c] != this )
																						{
																						if ( this.all_markers_[c].info_win_ )
																							{
																							this.all_markers_[c].info_win_.close();
																							this.all_markers_[c].info_win_ = null;
																							};
																						};
																					};
																				this.info_win_ = new google.maps.InfoWindow ({'position': marker.getPosition(), 'map': marker.getMap(), 'content': in_html, 'pixelOffset': new google.maps.Size ( 0, -32 ) });
																				}
												);
					};
				g_allMarkers[g_allMarkers.length] = marker;
				};
			};
		
		return marker;
	};

    /****************************************************************************************//**
    *	\brief Function to Reveal and/hide day <div> elements in the marker info window.	    *
    ********************************************************************************************/
    
    marker_change_day = function (  in_sel_id,
                                    in_id	///< The base ID of the element.
                                    )
    {
        var sel = document.getElementById ( in_sel_id );
        
        if ( sel && sel.value )
            {
            for ( var wd = 1; wd < 8; wd++ )
                {
                var elem = document.getElementById ( in_sel_id+'_marker_'+in_id.toString()+'_'+wd.toString()+'_id' );
                if ( elem )
                    {
                    if ( wd == sel.value )
                        {
                        elem.style.display = 'block';
                        }
                    else
                        {
                        elem.style.display = 'none';
                        };
                    };
                };
            };
    };

    /****************************************************************************************
    *									  UTILITY FUNCTIONS                                 *
    ****************************************************************************************/
    
    /************************************************************************************//**
    *	\brief This takes a latitude/longitude location, and returns an x/y pixel location	*
    *	for it.																				*
    *																						*
    *	\returns a Google Maps API V3 Point, with the pixel coordinates (top, left origin).	*
    ****************************************************************************************/
    
    fromLatLngToPixel = function ( in_Latng )
    {
        var	ret = null;
        
        if ( g_main_map )
            {
            // We measure the container div element.
            var	div = g_main_map.getDiv();
            
            if ( div )
                {
                var	pixel_width = div.offsetWidth;
                var	pixel_height = div.offsetHeight;
                var	lat_lng_bounds = g_main_map.getBounds();
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
            };
    
        return ret;
    };
    
	/****************************************************************************************
	*								MAIN FUNCTIONAL INTERFACE								*
	****************************************************************************************/
	
	if ( in_div && in_coords )
		{
		load_map ( in_div, in_coords );
		};
};
