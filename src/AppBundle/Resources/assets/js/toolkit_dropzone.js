(function ($) {

  // Disable auto discover for dropzone.
  Dropzone.autoDiscover = false;

  // Define a jquery function for dropzone.
  // This allows us to override default options.
  $.fn.toolkitDropzone = function(media_field_id, options, existing_media) {
    var dropzone_form = $(this);
    var dropzone_form_id = $(this).attr('id');
    var dropzone_form_close = $('.dropzone-form-close.' + dropzone_form_id);
    var media_field = $('#' + media_field_id);
    var media_placeholder_image = $('.media-placeholder-image.' + dropzone_form_id);

    var default_options = {
      maxFiles: 1,
      acceptedMimeTypes: 'image/jpeg,image/png,image/jpg',
      addRemoveLinks: true,
      init: function () {
        this.hiddenFileInput.removeAttribute('multiple');
        this.on("success", function (file, response) {
          mediaUpdate(
            {
              id: response.id,
              path: response.relativepath,
              filename: response.filename
            }
          );
        });
        this.on("addedfile", function () {
          $(dropzone_form_close).css({"display": "none"});
          if (this.files[1] != null) {
            this.removeFile(this.files[0]);
          }
        });
        this.on("removedfile", function (file) {
          $(dropzone_form_close).css({"display": "block"});

          // Revert media to existing.
          mediaUpdate();
        });
      }
    };

    var mediaUpdate = function(new_media) {
      var media = new_media ? new_media : existing_media;

      if (media.id && media.path && media.filename) {
        $(media_placeholder_image).find('.edit').show();
        $(media_placeholder_image).find('.new').hide();
        $(media_placeholder_image).css({
          "background-image": "url(" + media.path + "/" + media.filename + ")"
        });
        $(media_field).val(media.id);
      }
      else {
        $(media_placeholder_image).find('.edit').hide();
        $(media_placeholder_image).find('.new').show();
        $(media_placeholder_image).css({
          "background-image": "none"
        });
        $(media_field).val('');
      }
    };

    // Dropzone manipulation.
    $(media_placeholder_image).click(function() {
      $(media_placeholder_image).css({"display": "none"});
      $(dropzone_form).css({"display": "block"});
      $(dropzone_form_close).css({"display": "block"});
      existing_media = {};
      mediaUpdate();
    });

    $(dropzone_form_close).click(function() {
      $(media_placeholder_image).css({"display": "block"});
      $(dropzone_form).css({"display": "none"});
      $(dropzone_form_close).css({"display": "none"});
    });

    // Update media with existing.
    mediaUpdate();

    return this.dropzone($.extend(default_options, options));
  };

})(jQuery);
