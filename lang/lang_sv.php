<?php
// Sverige
/****************************************************************************************//**
* \file lang_sv.php                                                                         *
*                                                                                           *
* \brief This file contains Swedish localizations.                                          *
* \version 3.0 5                                                                            *
*                                                                                           *
* This file is part of the BMLT Common Satellite Base Class Project. The project GitHub     *
* page is available here: https://github.com/MAGSHARE/BMLT-Common-CMS-Plugin-Class          *
*                                                                                           *
* This file is part of the Basic Meeting List Toolbox (BMLT).                               *
*                                                                                           *
* Find out more at: http://bmlt.magshare.org                                                *
*                                                                                           *
* BMLT is free software: you can redistribute it and/or modify                              *
* it under the terms of the GNU General Public License as published by                      *
* the Free Software Foundation, either version 3 of the License, or                         *
* (at your option) any later version.                                                       *
*                                                                                           *
* BMLT is distributed in the hope that it will be useful,                                   *
* but WITHOUT ANY WARRANTY; without even the implied warranty of                            *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the                              *
* GNU General Public License for more details.                                              *
*                                                                                           *
* You should have received a copy of the GNU General Public License                         *
* along with this code. If not, see <http://www.gnu.org/licenses/>.                         *
********************************************************************************************/

class BMLT_Localized_BaseClass
    {
    /************************************************************************************//**
    * STATIC DATA MEMBERS (LOCALIZABLE)                                                     *
    ****************************************************************************************/
    
    /// These are all for the admin pages.
    static $local_options_title = 'Inställningar för BMLT'; ///< This is the title that is displayed over the options.
    static $local_menu_string = 'BMLT Inställningar'; ///< The name of the menu item.
    static $local_options_prefix = 'Välj inställning '; ///< The string displayed before each number in the options popup.
    static $local_options_add_new = 'Ny inställning'; ///< The string displayed in the "Add New Option" button.
    static $local_options_save = 'spara'; ///< The string displayed in the "Save Changes" button.
    static $local_options_delete_option = 'kasta inställning'; ///< The string displayed in the "Delete Option" button.
    static $local_options_delete_failure = 'borttagning av inställning misslyckades.'; ///< The string displayed upon unsuccessful deletion of an option page.
    static $local_options_create_failure = 'Misslyckades med ny inställning.'; ///< The string displayed upon unsuccessful creation of an option page.
    static $local_options_delete_option_confirm = 'Vill du kasta denna inställning?'; ///< The string displayed in the "Are you sure?" confirm.
    static $local_options_delete_success = 'Inställningen kastad!.'; ///< The string displayed upon successful deletion of an option page.
    static $local_options_create_success = 'Inställningen skapad!.'; ///< The string displayed upon successful creation of an option page.
    static $local_options_save_success = 'Inställningen uppdaterad!'; ///< The string displayed upon successful update of an option page.
    static $local_options_save_failure = 'Inställningen uppdaterades ej!.'; ///< The string displayed upon unsuccessful update of an option page.
    static $local_options_url_bad = 'Denna rootserver kommer inte fungera!.'; ///< The string displayed if a root server URI fails to point to a valid root server.
    static $local_options_access_failure = 'Så får du inte göra...'; ///< This is displayed if a user attempts a no-no.
    static $local_options_unsaved_message = 'Ska du inte spara först?'; ///< This is displayed if a user attempts to leave a page without saving the options.
    static $local_options_settings_id_prompt = 'ID är '; ///< This is so that users can see the ID for the setting.
    static $local_options_settings_location_checkbox_label = 'Textsök ordinarie inställning är platssök på'; ///< This is so that users can see the ID for the setting.
    
    /// These are all for the admin page option sheets.
    static $local_options_name_label = 'Inställnings namn:'; ///< The Label for the setting name item.
    static $local_options_rootserver_label = 'Root Server:'; ///< The Label for the root server item.
    static $local_options_new_search_label = 'Ny söknings URL:'; ///< The Label for the new search item.
    static $local_options_gkey_label = 'Google Maps API nyckel:'; ///< The Label for the Google Maps API Key item.
    static $local_options_no_name_string = 'namnge inställningar'; ///< The Value to use for a name field for a setting with no name.
    static $local_options_no_root_server_string = 'Sökväg till rootserver'; ///< The Value to use for a root with no URL.
    static $local_options_no_new_search_string = 'Ny söknings URL'; ///< The Value to use for a new search with no URL.
    static $local_options_no_gkey_string = 'Fyll i ny API nyckel'; ///< The Value to use for a new search with no URL.
    static $local_options_test_server = 'Test'; ///< This is the title for the "test server" button.
    static $local_options_test_server_success = 'Version '; ///< This is a prefix for the version, on success.
    static $local_options_test_server_failure = 'Felaktig sökväg till rootserver'; ///< This is a prefix for the version, on failure.
    static $local_options_test_server_tooltip = 'Testa rootserver'; ///< This is the tooltip text for the "test server" button.
    static $local_options_map_label = 'välj mittpunkt och zoomnivå'; ///< The Label for the map.
    static $local_options_mobile_legend = 'Dessa påverkar olika vyer(som karta mobil och avanserad)'; ///< This indicates that the enclosed settings are for the fast mobile lookup.
    static $local_options_mobile_grace_period_label = 'Extratid på mötets starttid vid sökning:'; ///< When you do a "later today" search, you get a "Grace Period."
    static $local_options_mobile_default_duration_label = 'Default Meeting Duration:';     ///< If the meeting has no duration, use this as a default.
    static $local_options_mobile_time_offset_label = 'Tids Offset:'; ///< This may have an offset (time zone difference) from the main server.
    static $local_options_initial_view = array ( ///< The list of choices for presentation in the popup.
                                                'karta' => 'Karta', 'text' => 'Text', 'avanserad_karta' => 'Avanserad karta', 'avanserad_text' => 'Avanserad Text'
                                                );
    static $local_options_initial_view_prompt = 'Standard söksätt:'; ///< The label for the initial view popup.
    static $local_options_theme_prompt = 'Välj färgtema:'; ///< The label for the theme selection popup.
    static $local_options_more_styles_label = 'Lägg till css för pluginen:'; ///< The label for the Additional CSS textarea.
    static $local_options_distance_prompt = 'Distansenheter:'; ///< This is for the distance units select.
    static $local_options_distance_disclaimer = 'Detta påverkar inte alla vyer'; ///< This tells the admin that only some stuff will be affected.
    static $local_options_grace_period_disclaimer = 'Minuter innan mötet anses som passerat (för snabbsökning).'; ///< This explains what the grace period means.
    static $local_options_time_offset_disclaimer = 'timmars skillnad från mainservern (ändras sällan)'; ///< This explains what the time offset means.
    static $local_options_miles = 'Engelska mil'; ///< The string for miles.
    static $local_options_kilometers = 'Kilometer'; ///< The string for kilometers.
    static $local_options_selectLocation_checkbox_text = 'Visa endast positioneringsfunktioner för mobila enhet'; ///< The label for the location services checkbox.
    
    static $local_no_root_server = 'sökvägen till servern saknas. kontakta webmaster'; ///< Displayed if there was no root server provided.

    /// These are for the actual search displays
    static $local_select_search = 'Välj snabbsök'; ///< Used for the "filler" in the quick search popup.
    static $local_clear_search = 'Rensa sökresultat'; ///< Used for the "Clear" item in the quick search popup.
    static $local_menu_new_search_text = 'Ny sökning'; ///< For the new search menu in the old-style BMLT search.
    static $local_cant_find_meetings_display = 'Inga möten funna!'; ///< When the new map search cannot find any meetings.
    static $local_single_meeting_tooltip = 'Klicka här för mer info om detta möte.'; ///< The tooltip shown for a single meeting.
    static $local_gm_link_tooltip = 'Klicka här för att komma till en karta (googlemap).'; ///< The tooltip shown for the Google Maps link.
    
    /// These are for the change display
    static $local_change_label_date = 'Ändrad datum:'; ///< The date when the change was made.
    static $local_change_label_meeting_name = 'Gruppnamn:'; ///< The name of the changed meeting.
    static $local_change_label_service_body_name = 'Serviceenhet:'; ///< The name of the meeting's Service body.
    static $local_change_label_admin_name = 'Ändrad av:'; ///< The name of the Service Body Admin that made the change.
    static $local_change_label_description = 'Beskrivning:'; ///< The description of the change.
    static $local_change_date_format = 'F j Y, \a\t g:i A'; ///< The format in which the change date/time is displayed.
    
    /// A simple message for most <noscript> elements. We have a different one for the older interactive search (below).
    static $local_noscript = 'Utan javascript aktiverad kommer du få en jobbig tid på nätet. aktivera javascript!.'; ///< The string displayed in a <noscript> element.
    
    /************************************************************************************//**
    * NEW SHORTCODE STATIC DATA MEMBERS (LOCALIZABLE)                                       *
    ****************************************************************************************/
    
    /// These are all for the [[bmlt_nouveau]] shortcode.
    static $local_nouveau_advanced_button = 'Mera valmöjligheter'; ///< The button name for the advanced search in the nouveau search.
    static $local_nouveau_map_button = 'Sök via karta'; ///< The button name for the map search in the nouveau search.
    static $local_nouveau_text_button = 'Sök via text'; ///< The button name for the text search in the nouveau search.
    static $local_nouveau_text_go_button = 'SÖK'; ///< The button name for the "GO" button in the text search in the nouveau search.
    static $local_nouveau_text_item_default_text = 'Söktext'; ///< The text that fills an empty text item.
    static $local_nouveau_text_location_label_text = 'Detta är en plats eller postkod'; ///< The label text for the location checkbox.
    static $local_nouveau_advanced_map_radius_label_1 = 'Hitta möten inom'; ///< The label text for the radius popup.
    static $local_nouveau_advanced_map_radius_label_2 = 'från centermarkören.'; ///< The second part of the label.
    static $local_nouveau_advanced_map_radius_value_auto = 'En automatiskt vald sökradie'; ///< The second part of the label, if Miles
    static $local_nouveau_advanced_map_radius_value_km = 'Km'; ///< The second part of the popup value, if Kilometers
    static $local_nouveau_advanced_map_radius_value_mi = 'Engelska mil'; ///< The second part of the popup value, if Miles
    static $local_nouveau_advanced_weekdays_disclosure_text = 'Valda veckodagar'; ///< The text that is used for the weekdays disclosure link.
    static $local_nouveau_advanced_formats_disclosure_text = 'Valda mötestyper'; ///< The text that is used for the formats disclosure link.
    static $local_nouveau_advanced_service_bodies_disclosure_text = 'Valda serviceenheter'; ///< The text that is used for the service bodies disclosure link.
    static $local_nouveau_select_search_spec_text = 'Ny sökning'; ///< The text that is used for the link that tells you to select the search specification.
    static $local_nouveau_select_search_results_text = 'Se resultat från senaste sökningen'; ///< The text that is used for the link that tells you to select the search results.
    static $local_nouveau_cant_find_meetings_display = 'Inga möten funna'; ///< When the new map search cannot find any meetings.
    static $local_nouveau_cant_lookup_display = 'Kunde inte beräkna din position.'; ///< Displayed if the app is unable to determine the location.
    static $local_nouveau_display_map_results_text = 'Visa svar på karta'; ///< The text for the display map results disclosure link.
    static $local_nouveau_display_list_results_text = 'Visa svar i listform'; ///< The text for the display list results disclosure link.
    static $local_nouveau_table_header_array = array ( 'Nation', 'Stat', 'Land', 'Stad', 'Grupp', 'Dag', 'Börjar', 'Plats', 'Mötestyp', ' ' );
    static $local_nouveau_weekday_long_array = array ( 'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag' );
    static $local_nouveau_weekday_short_array = array ( 'Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör' );
    
    static $local_nouveau_meeting_results_count_sprintf_format = '%s möten funna';
    static $local_nouveau_meeting_results_selection_count_sprintf_format = '%s möten valda, av %s möten funna';
    static $local_nouveau_meeting_results_single_selection_count_sprintf_format = '1 möte valt, av %s funna';
    static $local_nouveau_single_time_sprintf_format = 'Möten varje %s, klockan %s, och pågår i %s.';
    static $local_nouveau_single_duration_sprintf_format_1_hr = '1 timma';
    static $local_nouveau_single_duration_sprintf_format_mins = '%s minuter';
    static $local_nouveau_single_duration_sprintf_format_hrs = '%s timmar';
    static $local_nouveau_single_duration_sprintf_format_hr_mins = '1 timma och %s minuter';
    static $local_nouveau_single_duration_sprintf_format_hrs_mins = '%s timmar och %s minuter';
    
    /// These are all variants of the text that explains the location of a single meeting (Details View).
    static $local_nouveau_location_sprintf_format_loc_street_info = '%s, %s (%s)';
    static $local_nouveau_location_sprintf_format_loc_street = '%s, %s';
    static $local_nouveau_location_sprintf_format_street_info = '%s (%s)';
    static $local_nouveau_location_sprintf_format_loc_info = '%s (%s)';
    static $local_nouveau_location_sprintf_format_street = '%s';
    static $local_nouveau_location_sprintf_format_loc = '%s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_town_province_zip = '%s, %s (%s), %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_town_province_zip = '%s, %s, %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_street_info_town_province_zip = '%s (%s), %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_town_province_zip = '%s (%s), %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_street_town_province_zip = '%s, %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_town_province_zip = '%s, %s, %s %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_town_province = '%s, %s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_town_province = '%s, %s, %s, %s';
    static $local_nouveau_location_sprintf_format_single_street_info_town_province = '%s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_town_province = '%s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_street_town_province = '%s, %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_town_province = '%s, %s %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_town_zip = '%s, %s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_town_zip = '%s, %s, %s %s';
    static $local_nouveau_location_sprintf_format_single_street_info_town_zip = '%s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_town_zip = '%s (%s), %s %s';
    static $local_nouveau_location_sprintf_format_single_street_town_zip = '%s, %s %s';
    static $local_nouveau_location_sprintf_format_single_loc_town_zip = '%s, %s %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_province_zip = '%s, %s (%s), %s, %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_province_zip = '%s, %s, %s, %s';
    static $local_nouveau_location_sprintf_format_single_street_info_province_zip = '%s (%s), %s, %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_province_zip = '%s (%s), %s, %s';
    static $local_nouveau_location_sprintf_format_single_street_province_zip = '%s, %s, %s';
    static $local_nouveau_location_sprintf_format_single_loc_province_zip = '%s, %s, %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_province = '%s, %s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_province = '%s, %s, %s';
    static $local_nouveau_location_sprintf_format_single_street_info_province = '%s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_province = '%s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_street_province = '%s, %s';
    static $local_nouveau_location_sprintf_format_single_loc_province = '%s, %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info_zip = '%s, %s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_loc_street_zip = '%s, %s, %s';
    static $local_nouveau_location_sprintf_format_single_street_info_zip = '%s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_loc_info_zip = '%s (%s), %s';
    static $local_nouveau_location_sprintf_format_single_street_zip = '%s, %s';
    static $local_nouveau_location_sprintf_format_single_loc_zip = '%s, %s';
    
    static $local_nouveau_location_sprintf_format_single_loc_street_info = '%s, %s (%s)';
    static $local_nouveau_location_sprintf_format_single_loc_street = '%s, %s,';
    static $local_nouveau_location_sprintf_format_single_street_info = '%s (%s)';
    static $local_nouveau_location_sprintf_format_single_loc_info = '%s (%s)';
    static $local_nouveau_location_sprintf_format_single_street = '%s';
    static $local_nouveau_location_sprintf_format_single_loc = '%s';
    
    static $local_nouveau_location_sprintf_format_wtf = 'Ingen position angiven';
    
    static $local_nouveau_location_services_set_my_location_advanced_button = 'Sätt markören till min nuvarande position';
    static $local_nouveau_location_services_find_all_meetings_nearby_button = 'Hitta möten nära mig';
    static $local_nouveau_location_services_find_all_meetings_nearby_later_today_button = 'Hitta möten nära mig senare idag';
    static $local_nouveau_location_services_find_all_meetings_nearby_tomorrow_button = 'Hitta möten nära mig i morgon';
    
    static $local_nouveau_location_sprintf_format_duration_title = 'Detta möte är %s timmar och %s minuter långt.';
    static $local_nouveau_location_sprintf_format_duration_hour_only_title = 'Detta möte är 1 timma långt.';
    static $local_nouveau_location_sprintf_format_duration_hour_only_and_minutes_title = 'Detta möte är 1 timme och %s minuter långt.';
    static $local_nouveau_location_sprintf_format_duration_hours_only_title = 'Detta möte är %s timmar långt.';
    static $local_nouveau_lookup_location_failed = "Adressökningen misslyckades.";
    static $local_nouveau_lookup_location_server_error = "Lyckades inte med adresssökningen pga serverfel.";
    static $local_nouveau_time_sprintf_format = '%d:%02d %s';
    static $local_nouveau_am = 'AM';
    static $local_nouveau_pm = 'PM';
    static $local_nouveau_default_duration = '1:30';
    static $local_nouveau_noon = 'Lunch';
    static $local_nouveau_midnight = 'Midnatt';
    static $local_nouveau_advanced_map_radius_value_array = "0.25, 0.5, 1.0, 2.0, 5.0, 10.0, 15.0, 20.0, 50.0, 100.0, 200.0";
    static $local_nouveau_meeting_details_link_title = 'Mer info.';
    static $local_nouveau_meeting_details_map_link_uri_format = 'https://maps.google.com/maps?q=%f,%f';
    static $local_nouveau_meeting_details_map_link_text = 'Karta till möte';

    static $local_nouveau_single_formats_label = 'Mötestyper:';
    static $local_nouveau_single_service_body_label = 'Serviceenhet:';

    static $local_nouveau_prompt_array = array (
                                                'weekday_tinyint' => 'Dag',
                                                'start_time' => 'Startar',
                                                'duration_time' => 'Varaktighet',
                                                'formats' => 'Mötestyp',
                                                'distance_in_miles' => 'Avstånd i Engelska mil',
                                                'distance_in_km' => 'Avstånd i kilometer',
                                                'meeting_name' => 'Grupp',
                                                'location_text' => 'Plats',
                                                'location_street' => 'Adress',
                                                'location_city_subsection' => 'Statsdel',
                                                'location_neighborhood' => 'Kvarter',
                                                'location_municipality' => 'Stad',
                                                'location_sub_province' => 'Land',
                                                'location_province' => 'Stat',
                                                'location_nation' => 'Nation',
                                                'location_postal_code_1' => 'Postkod',
                                                'location_info' => 'Extra Information'
                                                );
                                                
    /************************************************************************************//**
    * STATIC DATA MEMBERS (SPECIAL LOCALIZABLE)                                             *
    ****************************************************************************************/
    
    /// This is the only localizable string that is not processed. This is because it contains HTML. However, it is also a "hidden" string that is only displayed when the browser does not support JS.
    static $local_no_js_warning = '<noscript class="no_js">Denna funktion kräver java. Du kan köra en nerskalad sökning utan java här <a rel="external nofollow" href="###ROOT_SERVER###">huvudserver</a>.</noscript>'; ///< This is the noscript presented for the old-style meeting search. It directs the user to the root server, which will support non-JS browsers.
                                    
    /************************************************************************************//**
    * STATIC DATA MEMBERS (NEW MAP LOCALIZABLE)                                             *
    ****************************************************************************************/
                                    
    static $local_new_map_option_1_label = 'Inställningar sökning (Ej aktiva om fliken ej öppnats):';
    static $local_new_map_weekdays = 'Möten är på dessa dagar:';
    static $local_new_map_all_weekdays = 'Alla';
    static $local_new_map_all_weekdays_title = 'Hitta möten för samtliga dagar.';
    static $local_new_map_weekdays_title = 'Hitta möten som är på ';
    static $local_new_map_formats = 'Möten med dessa mötesformat:';
    static $local_new_map_all_formats = 'Alla';
    static $local_new_map_all_formats_title = 'Sök möten för alla mötestyper.';
    static $local_new_map_js_center_marker_current_radius_1 = 'Cirkeln är ca ';
    static $local_new_map_js_center_marker_current_radius_2_km = ' kilometer bred.';
    static $local_new_map_js_center_marker_current_radius_2_mi = ' Engelska mil bred.';
    static $local_new_map_js_diameter_choices = array ( 0.25, 0.5, 1.0, 1.5, 2.0, 3.0, 5.0, 10.0, 15.0, 20.0, 25.0, 30.0, 50.0, 100.0 );
    static $local_new_map_js_new_search = 'Ny sökning';
    static $local_new_map_option_loc_label = 'Fyll i en position:';
    static $local_new_map_option_loc_popup_label_1 = 'Sök möten';
    static $local_new_map_option_loc_popup_label_2 = 'från positionen.';
    static $local_new_map_option_loc_popup_km = 'Km';
    static $local_new_map_option_loc_popup_mi = 'Engelska mil';
    static $local_new_map_option_loc_popup_auto = 'ett automatiskt valt avstånd.';
    static $local_new_map_center_marker_distance_suffix = ' från centermarkören.';
    static $local_new_map_center_marker_description = 'Detta är din valda position.';
    static $local_new_map_text_entry_fieldset_label = 'Fyll i en adress, postkod eller plats';
    static $local_new_map_text_entry_default_text = 'Fyll i en adress, postkod eller plats';
    static $local_new_map_location_submit_button_text = 'Sök möten nära denna plats';
    
    /************************************************************************************//**
    * STATIC DATA MEMBERS (MOBILE LOCALIZABLE)                                              *
    ****************************************************************************************/
    
    /// The units for distance.
    static $local_mobile_kilometers = 'Kilometer';
    static $local_mobile_miles = 'Engelska mil';
    static $local_mobile_distance = 'Avstånd'; ///< Distance (the string)
    
    /// The page titles.
    static $local_mobile_results_page_title = 'resultat - snabbsökning';
    static $local_mobile_results_form_title = 'Snabbsök närliggande möten';
    
    /// The fast GPS lookup links.
    static $local_GPS_banner = 'Välj snabbsök';
    static $local_GPS_banner_subtext = 'Bokmärk dessa länkar för att snabbt hitta hit igen.';
    static $local_search_all = 'Sök efter alla möten nära mig.';
    static $local_search_today = 'Senare idag';
    static $local_search_tomorrow = 'I morgon';
    
    /// The search for an address form.
    static $local_list_check = 'Om du upplever problem med den interaktiva kartan, Eller önskar få svaren i listform. Klicka i denna box och ange en adress.';
    static $local_search_address_single = 'Sök möten nära en adress';
    
    /// Used instead of "near my present location."
    static $local_search_all_address = 'Sök möten nära denna adress.';
    static $local_search_submit_button = 'Sök möten.';
    
    /// This is what is entered into the text box.
    static $local_enter_an_address = 'Fyll i adress!';
    
    /// Error messages.
    static $local_mobile_fail_no_meetings = 'Inga möten funna!';
    static $local_server_fail = 'Serverfel! Kontakta webmaster';
    static $local_cant_find_address = 'Lyckades inte beräkna position från adressinformation.';
    static $local_cannot_determine_location = 'Lyckades inte beräkna din position!';
    static $local_enter_address_alert = 'Skriv en adress!';
    
    /// The text for the "Map to Meeting" links
    static $local_map_link = 'Karta till möte';
    
    /// Only used for WML pages
    static $local_next_card = 'Nästa möte >>';
    static $local_prev_card = '<< Föregående möte';
    
    /// Used for the info and list windows.
    static $local_formats = 'Format';
    static $local_noon = 'Lunch';
    static $local_midnight = 'Midnatt';
    
    /// This array has the weekdays, spelled out. Since weekdays start at 1 (Sunday), we consider 0 to be an error.
    static	$local_weekdays = array ( 'ERROR', 'Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag' );
    static	$local_weekdays_short = array ( 'ERR', 'Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör' );
    };
?>