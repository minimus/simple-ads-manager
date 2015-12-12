/**
 * Created by minimus on 04.09.2015.
 */
(function($) {
  $(document).ready(function() {
    var
      sPointer = (samPointer.pointer.enabled) ? samPointer.pointer : samPointer.pointer2,
      pObject = $('#toplevel_page_sam-list');

    if(sPointer.enabled) {
      pObject.pointer({
        content: '<h3>' + sPointer.title + '</h3>' + sPointer.content,
        position: sPointer.position,
        pointerWidth: 420,
        close: function() {
          $.ajax({
            url: ajaxurl,
            data: {
              action: 'close_sam_pointer',
              pointer: sPointer.pointer
            },
            async: true
          });
        }
      }).pointer('open');
    }
  });
})(jQuery);