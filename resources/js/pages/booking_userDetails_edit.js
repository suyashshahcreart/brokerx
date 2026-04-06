import $ from 'jquery';
window.$ = window.jQuery = $;
import '../../css/pages/materialIconLiberaryStyles.css';
/* My Liberary Imports */
import { IconLibrary } from '../materialIconLiberary';
import { materialIconList } from '../data/materialIconsList';

// iconLib
const iconLib = new IconLibrary({ materialIconList });

$(document).ready(function() {
    // setup the icon library modal and search input
    iconLib.init('materialIconModal', 'materialIconSearch');
    // open the icon library modal when an icon picker is clicked
    $('#userDetailsButtonIcon').on('click', function() {
        iconLib.open(this, $('#iconPreview_userDetailsButtonIcon'));
    });
})