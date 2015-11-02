// Get old value for display name
var textDisplayName = $('#tui_toolkit_userbundle_user_displayName');
var textDisplayNameLabel = $('#tui_toolkit_userbundle_user_displayName').parent().find('label').html();
// Get other form field values
$('#tui_toolkit_userbundle_user_firstName').addClass('track_for_display_name').attr('namePart', 'first');
$('#tui_toolkit_userbundle_user_lastName').addClass('track_for_display_name').attr('namePart', 'last');
$('#tui_toolkit_userbundle_user_nickname').addClass('track_for_display_name').attr('namePart', 'nickname');
// Transform select for display name
$('#tui_toolkit_userbundle_user_displayName').parent().hide().after('<div class="mdl-selectfield"><label class="mdl-label-mimic" for="tui_display_name_select">' + textDisplayNameLabel + '</label><select class="mdl-select" id="tui_display_name_select"></select></div>');
var transformSelectDisplayName = function() {
    $('#tui_display_name_select').html('');
    var firstName = $('#tui_toolkit_userbundle_user_firstName').val();
    var lastName = $('#tui_toolkit_userbundle_user_lastName').val();
    var fullName = firstName + ' ' + lastName;
    var nickName = $('#tui_toolkit_userbundle_user_nickname').val();
    if ( firstName.length > 0 && lastName.length > 0  ) {
        $('#tui_display_name_select').append('<option value="' + fullName + '">' + fullName + '</option>');
    };
    if ( firstName.length > 0 ) {
        $('#tui_display_name_select').append('<option value="' + firstName + '">' + firstName + '</option>');
    };
    if ( lastName.length > 0 ) {
        $('#tui_display_name_select').append('<option value="' + lastName + '">' + lastName + '</option>');
    };
    if ( nickName.length > 0 ) {
        $('#tui_display_name_select').append('<option value="' + nickName + '">' + nickName + '</option>');
    };
    $('#tui_display_name_select option[value="' + textDisplayName.val() + '"]').attr('selected', 'selected');
};
// Change the hidden text field value when changing the select dropdown
$(document).on('change', '#tui_display_name_select', function() {
    textDisplayName.val(this.value);
});
$(document).on('change', '.track_for_display_name', function() {
    transformSelectDisplayName();
    $('#tui_display_name_select').trigger('change');
});
// Do on initial page load
$('.track_for_display_name').trigger('change');
