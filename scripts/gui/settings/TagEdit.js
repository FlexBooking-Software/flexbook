function processCopyClick(deleteCopied) {
  var event='';
  var resource='';
  var resourcePool='';
  var tag='';

  $('#editTagDiv input[meaning=commodity]').each(function() {
    if ($(this).is(':checked')) {
      if ($(this).attr('name')==='event') {
        if (event) event += ',';
        event += $(this).val();
      } else if ($(this).attr('name')==='resource') {
        if (resource) resource += ',';
        resource += $(this).val();
      } else if ($(this).attr('name')==='resourcePool') {
        if (resourcePool) resourcePool += ',';
        resourcePool += $(this).val();
      }
    }
  });
  $('#editTagDiv input[meaning=tag]').each(function() {
    if ($(this).is(':checked')) {
      if (tag) tag += ',';
      tag += $(this).val();
    }
  });

  if (!event&&!resource&&!resourcePool) alert('{__error.editTag_noCommodity}');
  else if (!tag) alert('{__error.editTag_noTargetTag}');
  else {
    $('#fi_event').val(event);
    $('#fi_resource').val(resource);
    $('#fi_resourcePool').val(resourcePool);
    $('#fi_targetTag').val(tag);
    $('#fi_deleteCopied').val(deleteCopied);

    $('#fb_eTagCommodityCopyHidden').click();
  }
}

$(document).ready(function() {
  $('#fi_associatedCommodity input[meaning=provider]').click(function() {
    var checked = $(this).is(':checked');
    $(this).closest('tr').find('input[meaning=commodity]').each(function() {
      $(this).prop('checked', checked);
    })
  });

  $('#fb_eTagDelete').click(function() {
    if (!confirm('{__label.editTag_confirmDelete}')) return false;
  });

  $('#fb_eTagDeleteCommodity').click(function() {
    var event='';
    var resource='';
    var resourcePool='';

    $('#editTagDiv input[meaning=commodity]').each(function() {
      if ($(this).is(':checked')) {
        if ($(this).attr('name')==='event') {
          if (event) event += ',';
          event += $(this).val();
        } else if ($(this).attr('name')==='resource') {
          if (resource) resource += ',';
          resource += $(this).val();
        } else if ($(this).attr('name')==='resourcePool') {
          if (resourcePool) resourcePool += ',';
          resourcePool += $(this).val();
        }
      }
    });

    if (!event&&!resource&&!resourcePool) alert('{__error.editTag_noCommodity}');
    else {
      $('#fi_event').val(event);
      $('#fi_resource').val(resource);
      $('#fi_resourcePool').val(resourcePool);

      $('#fb_eTagCommodityDeleteHidden').click();
    }
  });

  $('#fb_eTagCopyCommodity').click(function() {
    processCopyClick('0');
  });

  $('#fb_eTagMoveCommodity').click(function() {
    processCopyClick('1');
  });
});