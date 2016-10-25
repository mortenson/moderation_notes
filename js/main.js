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
    }
  };

  // Local variables.
  var $add_tooltip = initializeAddTooltip();
  var $view_tooltip = initializeViewTooltip();
  var view_tooltip_timeout;
  var mousedown_pos = [];
  var mouseup_pos = [];

  // This whole nonsense about keeping track of the mouse position is only
  // necessary to properly display a tooltip in the center of a Range.
  // Range.getBoundingClientRect().left is fine within a container, but as soon
  // as you start selecting complex elements it stops being accurate.
  $(document).mousedown(function(e) { mousedown_pos = [e.pageX, e.pageY]; });
  $(document).mousemove(function(e) { mouseup_pos = [e.pageX, e.pageY]; });

  /**
   * Command to remove a Moderation Note.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {String} response.id
   *   The ID for the moderation note.
   * @param {Number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.remove_moderation_note = function (ajax, response, status) {
    var id = response.id;
    var $wrapper = $('[data-moderation-note-highlight-id="' + id + '"]');
    $wrapper.contents().unwrap();
  };

  /**
   * Command to add a Moderation Note.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {Object} response.note
   *   An object representing a moderation note.
   * @param {Number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.add_moderation_note = function (ajax, response, status) {
    var note = response.note;
    showModerationNote(note);
  };

  /**
   * Makes another AJAX call after the reply form is submitted to re-load it.
   *
   * @param {Drupal.Ajax} [ajax]
   *   The ajax object.
   * @param {Object} response
   *   Object holding the server response.
   * @param {String} response.id
   *   The ID for the moderation note.
   * @param {Number} [status]
   *   The HTTP status code.
   */
  Drupal.AjaxCommands.prototype.reply_moderation_note = function (ajax, response, status) {
    var reply_ajax = new Drupal.ajax({
      url: Drupal.formatString(Drupal.url('moderation-note/!id/reply'), {'!id': response.id}),
      dialogType: 'dialog_offcanvas',
      progress: {type: 'fullscreen'}
    });
    reply_ajax.execute();
  };

  /**
   * Builds a URL based on a given field ID.
   *
   * Identical to Drupal.quickedit.utils.buildUrl.
   *
   * @param {Number} id
   *   A field ID, as provied by moderation_notes_preprocess_field().
   * @param {String} urlFormat
   *   A string with placeholders matching field ID parts.
   * @returns {String}
   *  The built URL.
   */
  function buildUrl (id, urlFormat) {
    var parts = id.split('/');
    return Drupal.formatString(decodeURIComponent(urlFormat), {
      '!entity_type': parts[0],
      '!id': parts[1],
      '!field_name': parts[2],
      '!langcode': parts[3],
      '!view_mode': parts[4]
    });
  }

  /**
   * Performs a text search within the page based on a given string.
   *
   * Modified from http://stackoverflow.com/a/5887719, written by @tpdown.
   *
   * @param {String} text
   *   The string to search for. Should not contain HTML.
   * @param {Node} element
   *   The parent element to perform the search within. Defaults to body.
   * @param {Number} offset
   *   The text offset from the start of the element to start the search.
   * @returns {Boolean}
   *   The status of the search. Use window.getSelection() to access the Range.
   */
  function doSearch (text, element, offset) {
    var scroll = $(window).scrollTop();
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

    selection.collapseToEnd();
    $(window).scrollTop(scroll);
    return match;
  }

  /**
   * Finds the offset of a range relative to a given parent element.
   *
   * Modified from http://stackoverflow.com/a/11358084, written by benjamin-rögner.
   *
   * @param {Node} element
   *   The element to compare against. Defaults to body.
   * @param {Range} range
   *   The range that requires comparison.
   * @returns {Number}
   *   The offset of the range.
   */
  function getCursorPositionInTextOf (element, range) {
    element = element || document.body;
    var parent_range = document.createRange();
    parent_range.setStart(element, 0);
    parent_range.setEnd(range.startContainer, range.startOffset);
    // Measure the length of the text from the start of the given element to
    // the start of the current range (position of the cursor).
    return parent_range.cloneContents().textContent.length;
  }

  /**
   * Initializes the tooltip used to add new notes.
   *
   * @returns {Object}
   *   The tooltip.
   */
  function initializeAddTooltip () {
    var $tooltip = $('<a class="moderation-notes-tooltip" href="javascript;">' + Drupal.t('Add note') + '</a>').hide();

    // Click callback.
    $tooltip.on('click', function (e) {
      var form_ajax = new Drupal.ajax({
        url: $tooltip.attr('href'),
        dialogType: 'dialog_offcanvas',
        progress: {type: 'fullscreen'}
      });
      form_ajax.execute();
      e.preventDefault();
    });

    $('body').append($tooltip);

    return $tooltip;
  }

  /**
   * Initializes the tooltip used to view existing notes.
   *
   * @returns {Object}
   *   The tooltip.
   */
  function initializeViewTooltip () {
    var $tooltip = $('<a class="moderation-notes-tooltip">' + Drupal.t('View note') + '</a>').hide();

    $('body').append($tooltip);

    // Click callback.
    $tooltip.on('click', function (e) {
      var view_ajax = new Drupal.ajax({
        url: $tooltip.attr('href'),
        dialogType: 'dialog_offcanvas',
        progress: {type: 'fullscreen'}
      });
      view_ajax.execute();
      e.preventDefault();

      $tooltip.hide();
      showContextHighlight($tooltip.data('moderation-note'));
    });

    $tooltip.on('mouseleave', function () {
      clearTimeout(view_tooltip_timeout);
      view_tooltip_timeout = setTimeout(function () {
        $tooltip.fadeOut('fast');
      }, 500);
    });

    $tooltip.on('mousemove', function () {
      $tooltip.finish().fadeIn();
      clearTimeout(view_tooltip_timeout);
    });

    return $tooltip;
  }

  /**
   * Displays the tooltip at a position relative to the current Range.
   *
   * @param {Object} $tooltip
   *   The tooltip.
   * @param {String} field_id
   *   The field ID.
   */
  function showAddTooltip ($tooltip, field_id) {
    var selection = window.getSelection();
    var range = selection.getRangeAt(0);
    var top = range.getBoundingClientRect().top - ($tooltip.outerHeight() + 5);
    var left = mousedown_pos[0] + ((mouseup_pos[0] - mousedown_pos[0]) / 2) - ($tooltip.outerWidth() / 2);
    $tooltip.css('left', left + document.body.scrollLeft);
    $tooltip.css('top', top + document.body.scrollTop);

    var url = buildUrl(field_id, Drupal.url('moderation-notes/add/!entity_type/!id/!field_name/!langcode/!view_mode'));
    $tooltip.attr('href', url);

    $tooltip.fadeIn('fast');
  }

  /**
   * Displays the tooltip at a position relative to the given element.
   *
   * @param {Object} $tooltip
   *   The tooltip.
   * @param {Object} $element
   *   The element to display to tooltip on.
   */
  function showViewTooltip ($tooltip, $element) {
    var width_offset = ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2);
    var offset = $element.offset();
    $tooltip.css('left', offset.left + width_offset);
    $tooltip.css('top', offset.top - ($tooltip.outerHeight() + 5));

    var id = $element.data('moderation-note-highlight-id');
    var url = Drupal.formatString(Drupal.url('moderation-note/!id'), {'!id': id});
    $tooltip.attr('href', url);
    $tooltip.data('moderation-note', $element.data('moderation-note'));

    $tooltip.fadeIn('fast');
  }

  /**
   * Shows the given moderation note as a highlighted range.
   *
   * @param {Object} note
   *   An objects representing a Moderation Note.
   */
  function showModerationNote (note) {
    // Remove all existing context highlights.
    removeContextHighlights();

    var $field = $('[data-moderation-notes-field-id="' + note.field_id + '"]');
    if ($field.length) {
      var match = doSearch(note.quote, $field[0], note.quote_offset);
      if (match) {
        var $wrap = highliteRange(match, 'moderation-note-highlight');

        // This allows notes to be found by their ID.
        $wrap.attr('data-moderation-note-highlight-id', note.id);
        $wrap.data('moderation-note', note);

        $wrap.on('mouseover', function () {
          showViewTooltip($view_tooltip, $(this));
          $view_tooltip.stop().fadeIn();
          clearTimeout(view_tooltip_timeout);
        });

        $wrap.on('mouseleave', function () {
          clearTimeout(view_tooltip_timeout);
          view_tooltip_timeout = setTimeout(function () {
            $view_tooltip.fadeOut('fast');
          }, 500);
        });
      }
    }
  }

  /**
   * Highlights focused text while the sidebar is open.
   *
   * @param {Object} note
   *   An objects representing a Moderation Note.
   */
  function showContextHighlight (note) {
    // Remove all existing context highlights.
    removeContextHighlights();

    // If this note is already highlighted, simply add a class.
    if (note.id) {
      var $note = $('[data-moderation-note-highlight-id="' + note.id + '"]');
      if ($note.length) {
        $note.addClass('moderation-note-contextual-highlight existing');
      }
    }
    // Otherwise, we need to create a new highlight.
    else {
      var $field = $('[data-moderation-notes-field-id="' + note.field_id + '"]');
      var match = doSearch(note.quote, $field[0], note.quote_offset);
      if (match) {
        highliteRange(match, 'moderation-note-contextual-highlight new');
      }
    }
  }

  /**
   * Removes all contextual highlights from the page.
   */
  function removeContextHighlights () {
    $('.moderation-note-contextual-highlight').each(function () {
      if ($(this).data('moderation-note-highlight-id')) {
        $(this).removeClass('moderation-note-contextual-highlight existing');
      }
      else {
        $(this).contents().unwrap();
      }
    });
  }

  /**
   * Wraps a given range in a <span> tag with the provided classes.
   *
   * @param {Range} range
   *   The given range.
   * @param {String} classes
   *   Classes you want to add to the highlight, separated by a space.
   * @returns {Object}
   *   The jQuery object for the wrap (could contain multiple elements).
   */
  function highliteRange (range, classes) {
    var selection = window.getSelection();

    selection.removeAllRanges();
    selection.addRange(range);
    document.designMode = 'on';
    var spellcheck = document.body.spellcheck;
    document.body.spellcheck = false;
    document.execCommand('hilitecolor', false, 'yellow');
    document.designMode = 'off';
    document.body.spellcheck = spellcheck;

    var wrap_range = selection.getRangeAt(0);
    var $wrap = $(wrap_range.startContainer.parentNode).add(wrap_range.endContainer.parentNode);
    $wrap.removeAttr('style').addClass(classes);
    selection.collapseToEnd();

    return $wrap;
  }

  // We use timeouts to throttle calls to this event.
  var timeout;
  $(document).on('selectionchange', function (e) {
    clearTimeout(timeout);
    $add_tooltip.fadeOut('fast');

    timeout = setTimeout(function () {
      if (window.getSelection) {
        var selection = window.getSelection();
        var text = selection.toString();
        if (text.length) {
          // Ensure that this selection is contained inside a field wrapper.
          var range = selection.getRangeAt(0);
          var $ancestor = $(range.commonAncestorContainer);
          var $field = $ancestor.closest('[data-moderation-notes-field-id]');
          if ($field.length) {
            // Show the tooltip.
            showAddTooltip($add_tooltip, $field.data('moderation-notes-field-id'));

            // Store the current selection so that it can be added to the form
            // later.
            var offset = getCursorPositionInTextOf($field[0], range);
            Drupal.moderation_notes.selection.quote = text;
            Drupal.moderation_notes.selection.quote_offset = offset;
          }
        }
      }
    }, 500);
  });

  $(document).on('dialogclose', function () {
    removeContextHighlights();
  });

  /**
   * Contains all Moderation Notes behaviors.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.moderation_notes = {
    attach: function (context, settings) {
      // Auto-fill the new note form with the current selection.
      var $new_form = $('[data-moderation-notes-new-form]', context);
      if ($new_form.length) {
        var selection = Drupal.moderation_notes.selection;
        $new_form.find('.field-moderation-note-quote').val(selection.quote);
        $new_form.find('.field-moderation-note-quote-offset').val(selection.quote_offset);
        showContextHighlight(selection);
      }

      // On page load, display all notes given to us.
      if (settings.moderation_notes) {
        var notes = settings.moderation_notes;
        delete settings.moderation_notes;
        for (var i in notes) {
          var note = notes[i];
          showModerationNote(note);
        }
      }
    }
  }

}(jQuery, Drupal));
