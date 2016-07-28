// transform cropper dataURI output to a Blob which Dropzone accepts
function dataURItoBlob(dataURI, mime_type) {
  var byteString = atob(dataURI.split(',')[1]);
  var ab = new ArrayBuffer(byteString.length);
  var ia = new Uint8Array(ab);
  for (var i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], { type: mime_type });
}

// Disable auto discover for dropzone.
Dropzone.autoDiscover = false;

(function ($) {
  // Define a jquery function for dropzone.
  // This allows us to override default options.
  $.fn.toolkitDropzone = function(media_field_id, options, existing_media, disabled_events, context) {
    var dropzone_form = $(this);
    var dropzone_form_id = $(this).attr('id');
    var dropzone_form_close = $('.dropzone-form-close.' + dropzone_form_id);
    var dropzone_form_errors = $('.dropzone-form-errors.' + dropzone_form_id);
    var media_field = $('#' + media_field_id);
    var media_placeholder_image = $('.media-placeholder-image.' + dropzone_form_id);
    var existing_media = existing_media || {};
    var disabled_events = disabled_events || [];

    switch(context) {
      case 'user':
        aspect_ratio=1;
        break;
      case 'passenger':
        aspect_ratio=1;
        break;
      case 'brand':
        aspect_ratio=NaN;
        break;
      case 'institution':
        aspect_ratio=NaN;
        break;
      default:
        aspect_ratio= 16/9;
        break;
    }




    var default_options = {
      maxFiles: 1,
      acceptedMimeTypes: 'image/jpeg,image/png,image/jpg',
      addRemoveLinks: true
    };

    var mediaUpdate = function(media, update_field_value) {
      if (media.id && media.path && media.filename) {
        $(media_placeholder_image).find('.edit').show();
        $(media_placeholder_image).find('.new').hide();
        $(media_placeholder_image).css({
          "background-image": "url(" + media.path + "/" + media.filename + ")"
        });

        if (update_field_value) {
          $(media_field).val(media.id);
        }
      }
      else {
        $(media_placeholder_image).find('.edit').hide();
        $(media_placeholder_image).find('.new').show();
        $(media_placeholder_image).css({
          "background-image": "none"
        });

        if (update_field_value) {
          $(media_field).val('');
        }
      }
    };

    // Update media with existing.
    mediaUpdate(existing_media, false);

    // Implement dropzone.
    var dropzone = new Dropzone("#" + dropzone_form_id, jQuery.extend(default_options, options));

    // Dropzone manipulation.
    if ($.inArray("success", disabled_events) == -1) {
      dropzone.on("success", function (file, response) {
        mediaUpdate(
          {
            id: response.id,
            path: response.relativepath,
            filename: response.filename
          },
          true
        );
        $(dropzone_form_errors).css({"display": "none"});
      });
    }

    if ($.inArray("addedfile", disabled_events) == -1) {
      dropzone.on("addedfile", function (file) {
        $(dropzone_form_close).css({"display": "none"});
        if (dropzone.files[1] != null) {
          dropzone.removeFile(dropzone.files[0]);
        }
      });
    }

    if ($.inArray("removedfile", disabled_events) == -1) {
      dropzone.on("removedfile", function (file) {
        $(dropzone_form_close).css({"display": "block"});

        // Revert media to existing.
        mediaUpdate(existing_media, true);
      });
    }

    if ($.inArray("error", disabled_events) == -1) {
      dropzone.on("error", function (file, response) {
        if(file.thumbnail != 'thumbnail_process'){
          var error_message = response;
          // If the response is an object provide a generic error message.
          if (typeof response === 'object' && response.error.code) {
            error_message = 'Could not upload (error code ' + response.error.code + ').';
          }

          dropzone.removeFile(file);
          $(dropzone_form_errors).css({"display": "block"});
          $(dropzone_form_errors).html(error_message);
        }
      });
    }

    if ($.inArray("thumbnail", disabled_events) == -1) {
      dropzone.on("thumbnail", function (file) {
        var myDropzone = this;
        // ignore files which were already cropped and re-rendered
        // to prevent infinite loop
        if (file.cropped) {
          return;
        }
//                        if (file.width < 800) {
//                            // validate width to prevent too small files to be uploaded
//                            // .. add some error message here
//                            return;
//                        }

        // cache filename to re-assign it to cropped file
        var cachedFilename = file.name;
        var mime_type = file.type;
        file.thumbnail = 'thumbnail_process';
        // remove not cropped file from dropzone (we will replace it later)
        myDropzone.removeFile(file);

        // dynamically create divs to allow multiple files processing
        var $cropperDiv = $('<div><div class="image-container"><!-- Cropper Container Here --></div><a class="crop-upload mdl-button mdl-button--raised mdl-button--colored">Crop and Upload</a></div>');
        // 'Crop and Upload' button in a modal
        var $uploadCrop = $cropperDiv.find('.crop-upload');

        var $img = $('<img />');
        // initialize FileReader which reads uploaded file
        var reader = new FileReader();
        reader.onloadend = function () {
          // add uploaded and read image to modal
          $cropperDiv.find('.image-container').html($img);
          $img.attr('src', reader.result);
          // initialize cropper for uploaded image
          $img.cropper({
               aspectRatio: aspect_ratio,
            //    autoCropArea: 1,
            movable: false,
            cropBoxResizable: true,
            minContainerWidth: 120,
            autoCrop: true
          });
        };
        // read uploaded file (triggers code above)
        reader.readAsDataURL(file);

        $("#dialog").html($cropperDiv);
        $("#dialog").dialog("option", "title", "Crop Image");
        $("#dialog").dialog("open");
        var dialogtop = $(".ui-dialog").position().top - 200;
        $(".ui-dialog").css('top', dialogtop + 'px');

        // listener for 'Crop and Upload' button in modal
        $uploadCrop.on('click', function() {
          $("#dialog").dialog("open");
          // get cropped image data
          var blob = $img.cropper('getCroppedCanvas').toDataURL(mime_type);
          // transform it to Blob object
          var newFile = dataURItoBlob(blob, mime_type);
          // set 'cropped to true' (so that we don't get to that listener again)
          newFile.cropped = true;
          // assign original filename
          newFile.name = cachedFilename;

          // add cropped file to dropzone
          myDropzone.addFile(newFile);
          // upload cropped file with dropzone
          myDropzone.processQueue();
          $("#dialog").html('');
          $("#dialog").dialog("option", "title", "");
          $("#dialog").dialog("close");
        });

    });
    }

    $(media_placeholder_image).click(function() {
      $(media_placeholder_image).css({"display": "none"});
      $(dropzone_form).css({"display": "block"});
      $(dropzone_form_close).css({"display": "block"});
      mediaUpdate({}, false);
    });

    $(dropzone_form_close).click(function() {
      $(media_placeholder_image).css({"display": "block"});
      $(dropzone_form).css({"display": "none"});
      $(dropzone_form_close).css({"display": "none"});
    });
  };

})(jQuery);
