<?php
/****************************************************************************************//**
*   \file   lang_en.php                                                                     *
*                                                                                           *
*   \brief  This file contains English localizations.                                       *
*   \version 1.2                                                                            *
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

class BMLT_Localized_BaseClass
    {
    /************************************************************************************//**
    *                           STATIC DATA MEMBERS (LOCALIZABLE)                           *
    ****************************************************************************************/
    
    /// These are all for the admin pages.
    static  $local_options_title = 'Basic Meeting List Toolbox Options';    ///< This is the title that is displayed over the options.
    static  $local_menu_string = 'BMLT Options';                            ///< The name of the menu item.
    static  $local_options_prefix = 'Select Setting ';                      ///< The string displayed before each number in the options popup.
    static  $local_options_add_new = 'Add A new Setting';                   ///< The string displayed in the "Add New Option" button.
    static  $local_options_save = 'Save Changes';                           ///< The string displayed in the "Save Changes" button.
    static  $local_options_delete_option = 'Delete This Setting';           ///< The string displayed in the "Delete Option" button.
    static  $local_options_delete_failure = 'The setting deletion failed.'; ///< The string displayed upon unsuccessful deletion of an option page.
    static  $local_options_create_failure = 'The setting creation failed.'; ///< The string displayed upon unsuccessful creation of an option page.
    static  $local_options_delete_option_confirm = 'Are you sure that you want to delete this setting?';    ///< The string displayed in the "Are you sure?" confirm.
    static  $local_options_delete_success = 'The setting was deleted successfully.';                        ///< The string displayed upon successful deletion of an option page.
    static  $local_options_create_success = 'The setting was created successfully.';                        ///< The string displayed upon successful creation of an option page.
    static  $local_options_save_success = 'The settings were updated successfully.';                        ///< The string displayed upon successful update of an option page.
    static  $local_options_save_failure = 'The settings were not updated.';                                 ///< The string displayed upon unsuccessful update of an option page.
    static  $local_options_url_bad = 'This root server URL will not work for this plugin.';                 ///< The string displayed if a root server URI fails to point to a valid root server.
    static  $local_options_access_failure = 'You are not allowed to perform this operation.';               ///< This is displayed if a user attempts a no-no.
    static  $local_options_unsaved_message = 'You have unsaved changes. Are you sure you want to leave without saving them?';   ///< This is displayed if a user attempts to leave a page without saving the options.
    static  $local_options_settings_id_prompt = 'The ID for this Setting is ';                              ///< This is so that users can see the ID for the setting.
    
    /// These are all for the admin page option sheets.
    static  $local_options_name_label = 'Setting Name:';                    ///< The Label for the setting name item.
    static  $local_options_rootserver_label = 'Root Server:';               ///< The Label for the root server item.
    static  $local_options_new_search_label = 'New Search URL:';            ///< The Label for the new search item.
    static  $local_options_gkey_label = 'Google Maps API Key:';             ///< The Label for the Google Maps API Key item.
    static  $local_options_no_name_string = 'Enter Setting Name';           ///< The Value to use for a name field for a setting with no name.
    static  $local_options_no_root_server_string = 'Enter a Root Server URL';                               ///< The Value to use for a root with no URL.
    static  $local_options_no_new_search_string = 'Enter a New Search URL'; ///< The Value to use for a new search with no URL.
    static  $local_options_no_gkey_string = 'Enter a New API Key';          ///< The Value to use for a new search with no URL.
    static  $local_options_test_server = 'Test';                            ///< This is the title for the "test server" button.
    static  $local_options_fetch_server_langs = 'Fetch Server Languages';   ///< This is the title for the "Fetch Server Languages" button.
    static  $local_options_fetch_server_langs_tooltip = 'If you press this button, the server will be queried for its available and default languages.';    ///< This is the tooltip for the "Fetch Server Languages" button.
    static  $local_options_test_server_success = 'Version ';                ///< This is a prefix for the version, on success.
    static  $local_options_test_server_failure = 'This Root Server URL is not Valid';                       ///< This is a prefix for the version, on failure.
    static  $local_options_test_server_tooltip = 'This tests the root server, to see if it is OK.';         ///< This is the tooltip text for the "test server" button.
    static  $local_options_map_label = 'Select a Center Point and Zoom Level for Map Displays';             ///< The Label for the map.
    static  $local_options_gkey_caveat = 'These are only necessary for old-style BMLT implementations';     ///< This lets people know that this is not necessary for newer installs.
    static  $local_options_mobile_legend = 'These are for the fast mobile lookup';                          ///< This indicates that the enclosed settings are for the fast mobile lookup.
    static  $local_options_mobile_grace_period_label = 'Grace Period:';     ///< When you do a "later today" search, you get a "Grace Period."
    static  $local_options_mobile_time_offset_label = 'Time Offset:';       ///< This may have an offset (time zone difference) from the main server.
    static  $local_options_initial_view = array (                           ///< The list of choices for presentation in the popup.
                                                '' => 'Root Server Decides', 'map' => 'Map', 'text' => 'Text', 'advanced' => 'Advanced (Server Decides)', 'advanced_map' => 'Advanced Map', 'advanced_text' => 'Advanced Text'
                                                );
    static  $local_options_initial_view_prompt = 'Initial Search Type:';    ///< The label for the initial view popup.
    static  $local_options_theme_prompt = 'Select a Color Theme:';          ///< The label for the theme selection popup.
    static  $local_options_push_down_checkbox_label = '"More Details" Windows "push down" the main list or map, as opposed to popping up over them.';       ///< The label for the "more details" checkbox.
    static  $local_options_more_styles_label = 'Add CSS Styles to the Plugin:';                             ///< The label for the Additional CSS textarea.
    static  $local_single_meeting_tooltip = 'Follow This Link for Details About This Meeting.'; ///< The tooltip shown for a single meeting.
    static  $local_gm_link_tooltip = 'Follow This Link to be Taken to A Google Maps Location for This Meeting.';    ///< The tooltip shown for the Google Maps link.
    static  $local_not_enough_for_old_style = 'In order to display the "classic" BMLT window, you need to have both a root server and a Google Maps API key in the corresponding setting.'; ///< Displayed if there is no GMAP API key.
    static  $local_options_language_prompt = 'Language:';                   ///< This is for the language select.
    static  $local_options_distance_prompt = 'Distance Units:';             ///< This is for the distance units select.
    static  $local_options_distance_disclaimer = 'This will not affect all of the displays.';               ///< This tells the admin that only some stuff will be affected.
    static  $local_options_grace_period_disclaimer = 'Minutes Elapsed Before A Meeting is Considered "Past."';      ///< This explains what the grace period means.
    static  $local_options_time_offset_disclaimer = 'Hours of Difference From the Main Server.';            ///< This explains what the time offset means.
    static  $local_options_miles = 'Miles';                                 ///< The string for miles.
    static  $local_options_kilometers = 'Kilometers';                       ///< The string for kilometers.
    
    /// These are for the actual search displays
    static  $local_select_search = 'Select a Quick Search';                 ///< Used for the "filler" in the quick search popup.
    static  $local_clear_search = 'Clear Search Results';                   ///< Used for the "Clear" item in the quick search popup.
    static  $local_menu_new_search_text = 'New Search';                     ///< For the new search menu in the old-style BMLT search.
    
    /// These are for the change display
    static  $local_change_label_date =  'Change Date:';                     ///< The date when the change was made.
    static  $local_change_label_meeting_name =  'Meeting Name:';            ///< The name of the changed meeting.
    static  $local_change_label_service_body_name =  'Service Body:';       ///< The name of the meeting's Service body.
    static  $local_change_label_admin_name =  'Changed By:';                ///< The name of the Service Body Admin that made the change.
    static  $local_change_label_description =  'Description:';              ///< The description of the change.
    static  $local_change_date_format = 'F j Y, \a\t g:i A';                ///< The format in which the change date/time is displayed.
    
    /// A simple message for most <noscript> elements. We have a different one for the older interactive search (below).
    static  $local_noscript = 'This will not work, because you do not have JavaScript active.';             ///< The string displayed in a <noscript> element.
                                    
    /************************************************************************************//**
    *                      STATIC DATA MEMBERS (SPECIAL LOCALIZABLE)                        *
    ****************************************************************************************/
    
    /// This is the only localizable string that is not processed. This is because it contains HTML. However, it is also a "hidden" string that is only displayed when the browser does not support JS.
    static  $local_no_js_warning = '<noscript class="no_js">This Meeting Search will not work because your browser does not support JavaScript. However, you can use the <a rel="external nofollow" href="###ROOT_SERVER###">main server</a> to do the search.</noscript>'; ///< This is the noscript presented for the old-style meeting search. It directs the user to the root server, which will support non-JS browsers.
                                    
    /************************************************************************************//**
    *                       STATIC DATA MEMBERS (NEW MAP LOCALIZABLE)                       *
    ****************************************************************************************/
                                    
    static  $local_new_map_option_1_label = 'Search Options (Not Applied Unless This Section Is Open):';
    static  $local_new_map_weekdays = 'Meetings Gather on These Weekdays:';
    static  $local_new_map_all_weekdays = 'All';
    static  $local_new_map_all_weekdays_title = 'Find meetings for every day.';
    static  $local_new_map_weekdays_title = 'Find meetings that occur on ';
    static  $local_new_map_formats = 'Meetings Have These Formats:';
    static  $local_new_map_all_formats = 'All';
    static  $local_new_map_all_formats_title = 'Find meetings for every format.';
    static  $local_new_map_js_center_marker_current_radius_1 = 'The circle is about ';
    static  $local_new_map_js_center_marker_current_radius_2_km = ' kilometers wide.';
    static  $local_new_map_js_center_marker_current_radius_2_mi = ' miles wide.';
    static  $local_new_map_js_diameter_choices = array ( 0.25, 0.5, 1.0, 1.5, 2.0, 3.0, 5.0, 10.0, 15.0, 20.0, 25.0, 30.0, 50.0, 100.0 );
    static  $local_new_map_js_new_search = 'New Search';
    static  $local_new_map_option_loc_label = 'Enter A Location:';
    static  $local_new_map_option_loc_popup_label_1 = 'Search for meetings within';
    static  $local_new_map_option_loc_popup_label_2 = 'of the location.';
    static  $local_new_map_option_loc_popup_km = 'Km';
    static  $local_new_map_option_loc_popup_mi = 'Miles';
    static  $local_new_map_option_loc_popup_auto = 'an automatically chosen distance';
    static  $local_new_map_center_marker_distance_suffix = ' from the center marker.';
    static  $local_new_map_center_marker_description = 'This is your chosen location.';
    static  $local_new_map_text_entry_fieldset_label = 'Enter an Address, Postcode or Location';
    static  $local_new_map_text_entry_default_text = 'Enter an Address, Postcode or Location';
    static  $local_new_map_location_submit_button_text = 'Search for Meetings Near This Location';
    
    /************************************************************************************//**
    *                       STATIC DATA MEMBERS (MOBILE LOCALIZABLE)                        *
    ****************************************************************************************/
    
    /// The units for distance.
    static  $local_mobile_kilometers = 'Kilometers';
    static  $local_mobile_miles = 'Miles';
    static  $local_mobile_distance = 'Distance';  ///< Distance (the string)
    
    /// The page titles.
    static  $local_mobile_results_page_title = 'Quick Meeting Search Results';
    static  $local_mobile_results_form_title = 'Find Nearby Meetings Quickly';
    
    /// The fast GPS lookup links.
    static  $local_GPS_banner = 'Select A Fast Meeting Lookup';
    static  $local_GPS_banner_subtext = 'Bookmark these links for even faster searches in the future.';
    static  $local_search_all = 'Search for all meetings near my present location.';
    static  $local_search_today = 'Later Today';
    static  $local_search_tomorrow = 'Tomorrow';
    
    /// The search for an address form.
    static  $local_list_check = 'If you are experiencing difficulty with the interactive map, or wish to have the results returned as a list, check this box and enter an address.';
    static  $local_search_address_single = 'Search for Meetings Near An Address';
    
    /// Used instead of "near my present location."
    static  $local_search_all_address = 'Search for all meetings near this address.';
    static  $local_search_submit_button = 'Search For Meetings';
    
    /// This is what is entered into the text box.
    static  $local_enter_an_address = 'Enter An Address';
    
    /// Error messages.
    static  $local_mobile_fail_no_meetings = 'No Meetings Found!';
    static  $local_server_fail = 'The search failed because the server encountered an error!';
    static  $local_cant_find_address = 'Cannot Determine the Location From the Address Information!';
    static  $local_cannot_determine_location = 'Cannot Determine Your Current Location!';
    static  $local_enter_address_alert = 'Please enter an address!';
    
    /// The text for the "Map to Meeting" links
    static  $local_map_link = 'Map to Meeting';
    
    /// Only used for WML pages
    static  $local_next_card = 'Next Meeting >>';
    static  $local_prev_card = '<< Previous Meeting';
    
    /// Used for the info and list windows.
    static  $local_formats = 'Formats';
    static  $local_noon = 'Noon';
    static  $local_midnight = 'Midnight';
    
    /// This array has the weekdays, spelled out. Since weekdays start at 1 (Sunday), we consider 0 to be an error.
    static	$local_weekdays = array ( 'ERROR', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
    static	$local_weekdays_short = array ( 'ERR', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );
    };
?>