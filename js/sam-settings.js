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
  });
})(jQuery);