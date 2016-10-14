/**
 * @file
 * Contains all Moderation Notes behaviors.
 */

(function ($, Drupal) {

  "use strict";

  Drupal.moderation_notes = {
    selection: {
      quote: false,
      quote_offset: false,
      field_id: false
    },
    util: {}
  };

  Drupal.moderation_notes.util.buildUrl = function (id, urlFormat) {
    var parts = id.split('/');
    return Drupal.formatString(decodeURIComponent(urlFormat), {
      '!entity_type': parts[0],
      '!id': parts[1],
      '!field_name': parts[2],
      '!langcode': parts[3],
      '!view_mode': parts[4]
    });
  };

  /**
   * Modified from http://stackoverflow.com/a/5887719, written by @tpdown.
   */
  var doSearch = function (text, element, offset) {
    element = element || document.body;
    offset = offset || 0;
    var match = false;

    if (window.find && window.getSelection) {
      var selection = window.getSelection();
      selection.collapse(element, 0);

      var offset_difference = element.innerHTML.length;
      while (window.find(text)) {
        var range = selection.getRangeAt(0);
        var $ancestor = $(range.commonAncestorContainer);
        if ($ancestor.closest(element).length) {
          var current_offset = getCursorPositionInTextOf(element, range);
          var current_difference = Math.abs(current_offset - offset);
          if (current_difference < offset_difference) {
            offset_difference = current_difference;
            match = range;
          }
        }
        else {
          break;
        }
        selection.collapseToEnd();
      }
    }

    return match;
  };

  /**
   * Modified from http://stackoverflow.com/a/11358084, written by benjamin-rögner.
   */
  var getCursorPositionInTextOf = function (element, range) {
    var parent_range = document.createRange();
    parent_range.setStart(element, 0);
    parent_range.setEnd(range.startContainer, range.startOffset);
    // Measure the length of the text from the start of the given element to
    // the start of the current range (position of the cursor).
    return parent_range.cloneContents().textContent.length;
  };

  var $tooltip = $('<a id="moderation-notes-tooltip" href="javascript:;">Add Note</a>').hide();
  document.addEventListener('selectionchange', function(e) {
    $tooltip.hide();
  }, false);

  // Click callback.
  $tooltip.on('click', function () {
    var field_id = Drupal.moderation_notes.selection.field_id;
    var form_ajax = new Drupal.ajax({
      url: Drupal.moderation_notes.util.buildUrl(field_id, Drupal.url('moderation-notes/add/!entity_type/!id/!field_name/!langcode/!view_mode')),
      dialogType: 'dialog_offcanvas'
    });
    form_ajax.execute();
  });

  $('body').append($tooltip);

  $(document).on('mouseup', function (e) {
    if (window.getSelection) {
      var selection = window.getSelection();
      var text = selection.toString();
      if (text.length) {
        // Ensure that this selection is contained inside a field wrapper.
        var range = selection.getRangeAt(0);
        var $ancestor = $(range.commonAncestorContainer);
        var $field = $ancestor.closest('[data-quickedit-field-id]');
        if ($field.length) {
          var offset = getCursorPositionInTextOf($field[0], range);
          // Positioning.
          var rect = range.getBoundingClientRect();
          var width_offset = (rect.width / 2) - ($tooltip.outerWidth() / 2);
          $tooltip.css('left', rect.left + document.body.scrollLeft + width_offset);
          $tooltip.css('top', rect.top + document.body.scrollTop - ($tooltip.outerHeight() + 5));
          $tooltip.show();
          Drupal.moderation_notes.selection.quote = text;
          Drupal.moderation_notes.selection.quote_offset = offset;
          Drupal.moderation_notes.selection.field_id = $field.data('moderation-notes-field-id');
        }
      }
    }
  });

  /**
   * Contains all Moderation Notes behaviors.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.moderation_notes = {
    attach: function (context, settings) {
      var $new_form = $('[data-moderation-notes-new-form]', context);
      if ($new_form.length) {
        $new_form.find('input[name="quote"]').val(Drupal.moderation_notes.selection.quote);
        $new_form.find('input[name="quote_offset"]').val(Drupal.moderation_notes.selection.quote_offset);
      }
      if (settings.moderation_notes) {
        var notes = settings.moderation_notes;
        delete settings.moderation_notes;
        var selection = window.getSelection();
        for (var i in notes) {
          var note = notes[i];
          var $field = $('[data-moderation-notes-field-id="' + note.field_id + '"]');
          if ($field.length) {
            var match = doSearch(note.quote, $field[0], note.quote_offset);
            if (match) {
              var wrap = document.createElement('span');
              wrap.classList = 'moderation-note';
              match.surroundContents(wrap);
              document.execCommand('HiliteColor', false, 'yellow');
            }
          }
        }
      }
    }
  }

}(jQuery, Drupal));
