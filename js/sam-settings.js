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

    $('#tabs').tabs();

    var
      hvOpts = {direction: 'vertical'},
      el = $('#errorlog'), elfs = $('#errorlogFS'),
      bbp = $('#bbpEnabled'),
      bbpBeforePost = $('label[for=bbpBeforePost],#bbpBeforePost'),
      bbpList = $('label[for=bbpList],#bbpList'),
      bbpMiddlePost = $('label[for=bbpMiddlePost],#bbpMiddlePost'),
      bbpAfterPost = $('label[for=bbpAfterPost],#bbpAfterPost');

    el.click(function() {
      if(el.is(':checked')) elfs.attr('checked', true);
      if(!el.is(':checked')) elfs.attr('checked', false);
    });

    elfs.click(function() {
      if(!elfs.is(':checked')) el.attr('checked', true);
    });

    bbp.click(function() {
      if(bbp.is(':checked')) {
        if(bbpBeforePost.is(':hidden')) bbpBeforePost.show('blind', hvOpts, 500);
        if(bbpList.is(':hidden')) bbpList.show('blind', hvOpts, 500);
        if(bbpMiddlePost.is(':hidden')) bbpMiddlePost.show('blind', hvOpts, 500);
        if(bbpAfterPost.is(':hidden')) bbpAfterPost.show('blind', hvOpts, 500);
      }
      else {
        if(bbpBeforePost.is(':visible')) bbpBeforePost.hide('blind', hvOpts, 500);
        if(bbpList.is(':visible')) bbpList.hide('blind', hvOpts, 500);
        if(bbpMiddlePost.is(':visible')) bbpMiddlePost.hide('blind', hvOpts, 500);
        if(bbpAfterPost.is(':visible')) bbpAfterPost.hide('blind', hvOpts, 500);
      }
    });

    var
      typesDS = options.adTypes, objDS = options.adObjects,
      bpAdsType = $('#bpAdsType'), bpAdsId = $('#bpAdsId'), 
      bpTypeVal = bpAdsType.val(), bpObjVal = bpAdsId.val();

    bpAdsType.ejDropDownList({
      dataSource: typesDS,
      fields: { value: "parentId", text: 'text' },
      cascadeTo: 'bpAdsId'
    });

    bpAdsId.ejDropDownList({
      dataSource: objDS,
      fields: { value: "value", text: 'text' }
    });

    bpAdsType.ejDropDownList('setSelectedValue', bpTypeVal);
    bpAdsId.ejDropDownList('setSelectedValue', bpObjVal);

    var
      mpAdsType = $('#mpAdsType'), mpAdsId = $('#mpAdsId'), 
      mpTypeVal = mpAdsType.val(), mpObjVal = mpAdsId.val();

    mpAdsType.ejDropDownList({
      dataSource: typesDS,
      fields: { value: "parentId", text: 'text' },
      cascadeTo: 'mpAdsId'
    });

    mpAdsId.ejDropDownList({
      dataSource: objDS,
      fields: { value: "value", text: 'text' }
    });

    mpAdsType.ejDropDownList('setSelectedValue', mpTypeVal);
    mpAdsId.ejDropDownList('setSelectedValue', mpObjVal);

    var
      apAdsType = $('#apAdsType'), apAdsId = $('#apAdsId'), 
      apTypeVal = apAdsType.val(), apObjVal = apAdsId.val();

    apAdsType.ejDropDownList({
      dataSource: typesDS,
      fields: { value: "parentId", text: 'text' },
      cascadeTo: 'apAdsId'
    });

    apAdsId.ejDropDownList({
      dataSource: objDS,
      fields: { value: "value", text: 'text' }
    });

    apAdsType.ejDropDownList('setSelectedValue', apTypeVal);
    apAdsId.ejDropDownList('setSelectedValue', apObjVal);

    var
      wptAdsType = $('#wptAdsType'), wptAdsId = $('#wptAdsId'),
      wptTypeVal = wptAdsType.val(), wptObjVal = wptAdsId.val();

    wptAdsType.ejDropDownList({
      dataSource: typesDS,
      fields: {value: 'parentId', text: 'text'},
      cascadeTo: 'wptAdsId'
    });

    wptAdsId.ejDropDownList({
      dataSource: objDS,
      fields: {value: 'value', text: 'text'}
    });

    wptAdsType.ejDropDownList('setSelectedValue', wptTypeVal);
    wptAdsId.ejDropDownList('setSelectedValue', wptObjVal);

    var stats = $('#stats'), keepStats = $('#keepStats');
    keepStats.prop('disabled', (!stats.is(':checked')));
    stats.on('click', function(e) {
      keepStats.prop('disabled', (!stats.is(':checked')));
    })
  });
})(jQuery);
