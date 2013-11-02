(function($) {
  $(document).ready(function() {
    $('#role-slider').slider({
      from: 0,
      to: 4,
      step: 1,
      limits: false,
      dimension: '',
      scale: options.roles,
      skin: 'round_plastic',
      calculate: function(value) {
        return options.roles[value];
      },
      onstatechange: function(value) {
        var values = value.split(';');
        $('#access').val(options.values[values[1]]);
      }
    });
      
    var el = $('#errorlog'), elfs = $('#errorlogFS');

    el.click(function() {
      if(el.is(':checked')) elfs.attr('checked', true);
      if(!el.is(':checked')) elfs.attr('checked', false);
    });

    elfs.click(function() {
      if(!elfs.is(':checked')) el.attr('checked', true);
    });
  });
})(jQuery);