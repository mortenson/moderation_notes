/**
 * @file
 * Contains all Moderation Notes behaviors.
 */

(function ($, Drupal) {

  "use strict";

  /**
   * Modified from http://stackoverflow.com/a/5887719, written by @tpdown.
   */
  var doSearch = function (text, element, offset) {
    element = element || document.body;
    offset = offset || 0;
    var match = false;

    if (window.find && window.getSelection) {
      var spellcheck = document.body.spellcheck;
      document.body.spellcheck = false;
      document.designMode = 'on';
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

      document.designMode = 'off';
      document.body.spellcheck = spellcheck;
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
          var rect = range.getBoundingClientRect();
          var width_offset = (rect.width / 2) - ($tooltip.outerWidth() / 2);
          $tooltip.css('left', rect.left + document.body.scrollLeft + width_offset);
          $tooltip.css('top', rect.top + document.body.scrollTop - ($tooltip.outerHeight() + 5));
          $tooltip.show();
          /*var offset = getCursorPositionInTextOf($field[0], range);
          var match = doSearch(text, $field[0], offset);
          if (match) {
            selection.removeAllRanges();
            selection.addRange(match);
            document.execCommand('HiliteColor', false, 'yellow');
          }*/
        }
      }
    }
  });

}(jQuery, Drupal));
