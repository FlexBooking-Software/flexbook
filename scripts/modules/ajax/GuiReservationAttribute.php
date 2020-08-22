<?php

class AjaxGuiReservationAttribute extends AjaxGuiAction {
  
  public function __construct($request) {
    AjaxAction::__construct($request);
    
    if (!isset($this->_params['readonly'])) $this->_params['readonly'] = false;
  }
  
  protected function _getAttributeForBackoffice() {
    $ret = '';
    $js = '';
    
    if (isset($this->_params['eventId'])) {
      $b = new BEvent($this->_params['eventId']);
      $attributes = $b->getAttribute('RESERVATION',null,true,true);
    } else {
      $b = new BResource($this->_params['resourceId']);
      $attributes = $b->getAttribute('RESERVATION',null,true,true);
    }
    
    if (isset($this->_params['reservationId'])) {
      $b = new BReservation($this->_params['reservationId']);
      foreach ($b->getAttribute() as $id=>$attribute) {
        if (isset($attributes[$id])) {
          switch ($attributes[$id]['type']) {
            case 'NUMBER': $attributes[$id]['value'] = $this->_app->regionalSettings->convertNumberToHuman($attribute['value']); break;
            case 'DECIMALNUMBER': $attributes[$id]['value'] = $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2); break;
            case 'DATE': $attributes[$id]['value'] = $this->_app->regionalSettings->convertDateToHuman($attribute['value']); break;
            case 'TIME': $attributes[$id]['value'] = $this->_app->regionalSettings->convertTimeToHuman($attribute['value'],'h:m'); break;
            case 'DATETIME': $attributes[$id]['value'] = $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']); break;
            case 'FILE': $attributes[$id]['value'] = $attribute['value']; $attributes[$id]['valueId'] = $attribute['valueId']; break;
            default: $attributes[$id]['value'] = $attribute['value'];
          }
        }
      }
    }
    
    if (isset($this->_params['values'])) {
      foreach ($this->_params['values'] as $id=>$value) {
        if (isset($attributes[$id])&&strcmp($value,'__no_change__')) $attributes[$id]['value'] = $value;
      }
    }
    
    foreach ($attributes as $id=>$attribute) {
      switch ($attribute['type']) {
        case 'NUMBER':
        case 'DECIMALNUMBER':
        case 'TEXT': $inputHtml = sprintf('<input class="" type="text" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s"%s/>', $id, $id, $attribute['value'], $this->_params['readonly']?' readonly="yes"':'');
                     break;
        case 'TIME': $inputHtml = sprintf('<input class="" type="text" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s"%s/>', $id, $id, $attribute['value'], $this->_params['readonly']?' readonly="yes"':'');
                     if (!$this->_params['readonly']) $js .= sprintf("$(function() { $('#fi_attr_%d').datetimepicker({format:'H:i',datepicker:false,timepicker:true,allowBlank:true,scrollInput:false}); });", $id);
                     break;
        case 'DATETIME':
                     $inputHtml = sprintf('<input class="" type="text" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s"%s/>', $id, $id, $attribute['value'], $this->_params['readonly']?' readonly="yes"':'');
                     if (!$this->_params['readonly']) $js .= sprintf("$(function() { $('#fi_attr_%d').datetimepicker({format:'d.m.Y H:i',datepicker:true,timepicker:true,allowBlank:true,scrollInput:false}); });", $id);
                     break;
        case 'DATE': $inputHtml = sprintf('<input class="" type="text" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s"%s/>', $id, $id, $attribute['value'], $this->_params['readonly']?' readonly="yes"':'');
                     if (!$this->_params['readonly']) $js .= sprintf("$(function() { $('#fi_attr_%d').datetimepicker({format:'d.m.Y',datepicker:true,timepicker:false,allowBlank:true,scrollInput:false}); });", $id);
                     break;
        case 'TEXTAREA':
                     $inputHtml = sprintf('<textarea class="" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]"%s>%s</textarea>', $id, $id, $this->_params['readonly']?' readonly="yes"':'', $attribute['value']);
                     break;
        case 'LIST': $inputHtml = sprintf('<select id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]">', $id, $id);
                     if (!$this->_params['readonly']) {
                      $inputHtml .= sprintf('<option value="">%s</option>', $this->_app->textStorage->getText('label.select_choose'));
                      foreach (explode(',',$attribute['allowedValues']) as $value) {
                        $inputHtml .= sprintf('<option value="%s"%s>%s</option>', $value, !strcmp($value,$attribute['value'])?' selected="selected"':'', $value);
                      }
                     } else $inputHtml .= sprintf('<option value="%s" selected="selected">%s</option>', $attribute['value'], $attribute['value']);
                     $inputHtml .= '</select>';
                     break;
        case 'FILE': global $AJAX;
                     if ($attribute['value']) $value = sprintf('<a target="_file" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), ifsetor($attribute['valueId']), $attribute['value']);
                     else $value = '';
                     $inputHtml = sprintf('<label id="fi_attr_%d_label" class="file">%s</label><input type="hidden" id="fi_attr_%d" meaning="reservation_attribute" name="attribute[%d]" value="__no_change__"/><div class="file" id="fi_attribute_file_%d"></div>',
                                          $id, $value, $id, $id, $id);
                     if (!$this->_params['readonly']) {
                       $js .= sprintf("var uploadObj = $('#fi_attribute_file_%d').uploadFile({
                                url: '%s/uploadfile.php',
                                fileName: 'uploadfile',
                                dragDrop: false,
                                maxFileCount: 1,
                                uploadStr: '%s',
                                maxFileCountErrorStr: '%s&nbsp;',
                                onSuccess: function(files,data,xhr,pd) {
                                  if (files) {
                                    var data = JSON.parse(data);
                                    $('#fi_attr_%d').val(data.id);
                                    $('#fi_attr_%d_label').hide();
                                    $('#fi_attribute_file_%d .ajax-file-upload').hide();
                                  }
                                },
                              });", $id, dirname($AJAX['url']),
                         $this->_app->textStorage->getText('button.ajax_profile_fileUpload'),
                         $this->_app->textStorage->getText('label.ajax_profile_fileUpload_maxCount'),
                         $id, $id, $id);
                     }
                     break;
        default: $inputHtml = 'Unknown type!';
      }
      
      if ($attribute['mandatory']=='Y') $class = ' class="bold"';
      else $class = '';
      $attrHtml = sprintf('<div class="formItem"><label%s>%s:</label>%s</div>', $class, formatAttributeName($attribute['name'], $attribute['url']), $inputHtml);
      
      $ret .= $attrHtml;
    }
    if ($ret) $ret .= '<br/>';
    
    $this->_html = $ret;
    $this->_js = $js;
  }
  
  protected function _getAttributeForPortal() {
    $ret = '';
    $js = '';
    
    if (isset($this->_params['eventId'])) {
      $b = new BEvent($this->_params['eventId']);
      $attributes = $b->getAttribute('RESERVATION');
    } else {
      $b = new BResource($this->_params['resourceId']);
      $attributes = $b->getAttribute('RESERVATION');
    }
    
    if (isset($this->_params['reservationId'])) {
      $b = new BReservation($this->_params['reservationId']);
      foreach ($b->getAttribute() as $id=>$attribute) {
        if (isset($attributes[$id])) $attributes[$id]['value'] = $attribute['value'];
      }
    }
    
    $category = '';
    foreach ($attributes as $id=>$attribute) {
      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        $ret .= sprintf('<div class="flb_reserve_attributecategory_name">%s</div><div class="flb_reserve_attributecategory flb_reserve_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      
      switch ($attribute['type']) {
        case 'NUMBER':
        case 'DECIMALNUMBER':
        case 'TEXT': $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="" />', $this->_params['prefix'], $id, $id);
                     break;
        case 'TIME': $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="" />', $this->_params['prefix'], $id, $id);
                     $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'H:i',datepicker:false,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     break;
        case 'DATETIME':
                     $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="" />', $this->_params['prefix'], $id, $id);
                     $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y H:i',datepicker:true,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     break;
        case 'DATE': $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="" />', $this->_params['prefix'], $id, $id);
                     $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y',datepicker:true,timepicker:false,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     break;
        case 'TEXTAREA':
                     $inputHtml = sprintf('<textarea class="flb_reserve_attribute" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]">%s</textarea>', $this->_params['prefix'], $id, $id, $attribute['value']);
                     break;
        case 'LIST': $inputHtml = sprintf('<select class="flb_reserve_attribute" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]"><option value="">%s</option>', $this->_params['prefix'], $id, $id, $this->_app->textStorage->getText('label.select_choose'));
                     foreach (explode(',',$attribute['allowedValues']) as $value) {
                      $inputHtml .= sprintf('<option value="%s">%s</option>', $value, $value);
                     }
                     $inputHtml .= '</select>';
                     break;
        case 'FILE': global $AJAX;
                     if ($attribute['value']) $value = sprintf('<a target="_file" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), ifsetor($attribute['valueId']), $attribute['value']);
                     else $value = '';
                     $inputHtml = sprintf('<label id="%sattr_%d_label" class="file">%s</label><input type="hidden" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]"/><div class="file" id="%sattribute_file_%d"></div>',
                       $this->_params['prefix'], $id, $value, $this->_params['prefix'], $id, $id, $this->_params['prefix'], $id);
                     $js .= sprintf("var uploadObj = $('#%sattribute_file_%d').uploadFile({
                                url: $('#flb_core_url_path').val()+'uploadfile.php',
                                fileName: 'uploadfile',
                                dragDrop: false,
                                maxFileCount: 1,
                                uploadStr: '%s',
                                maxFileCountErrorStr: '%s&nbsp;',
                                onSuccess: function(files,data,xhr,pd) {
                                  if (files) {
                                    var data = JSON.parse(data);
                                    $('#%sattr_%d').val(data.id);
                                    $('#%sattr_%d_label').hide();
                                    $('#%sattribute_file_%d .ajax-file-upload').hide();
                                  }
                                },
                              });", $this->_params['prefix'], $id,
                              $this->_app->textStorage->getText('button.ajax_profile_fileUpload'),
                              $this->_app->textStorage->getText('label.ajax_profile_fileUpload_maxCount'),
                              $this->_params['prefix'], $id, $this->_params['prefix'], $id, $this->_params['prefix'], $id);
                     break;
        default: $inputHtml = 'Unknown type!';
      }
      $class = ' flb_reserve_attribute_'.$id;
      if ($attribute['mandatory']=='Y') {
        $class .= ' flb_reserve_attribute_mandatory';
        $labelPrefix = '* ';
      } else {
        $labelPrefix = '';
      }
      $attrHtml = sprintf('<div class="label%s"><span>%s%s:</span></div>%s<br/>', $class, $labelPrefix, formatAttributeName($attribute['name'], $attribute['url']), $inputHtml);
      
      $ret .= $attrHtml;
      
      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';
    
    $this->_html = $ret;
    $this->_js = $js;
  }
  
  protected function _getAttributeForCalendar() {
    $ret = '';
    $js = '';
    
    if (isset($this->_params['eventId'])) {
      $b = new BEvent($this->_params['eventId']);
      $attributes = $b->getAttribute('RESERVATION');
    } else {
      $b = new BResource($this->_params['resourceId']);
      $attributes = $b->getAttribute('RESERVATION');
    }
    
    if (isset($this->_params['reservationId'])) {
      $b = new BReservation($this->_params['reservationId']);
      foreach ($b->getAttribute() as $id=>$attribute) {
        if (isset($attributes[$id])) $attributes[$id]['value'] = $attribute['value'];
      }
    }
    
    foreach ($attributes as $id=>$attribute) {
      switch ($attribute['type']) {
        case 'NUMBER':
                     if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value']));
                     else $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value']));
                     break;
        case 'DECIMALNUMBER':
                     if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2));
                     else $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2));
                     break;
        case 'TEXT': if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, ifsetor($attribute['value']));
                     else $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, ifsetor($attribute['value']));
                     break;
        case 'TIME': if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $this->_app->regionalSettings->convertTimeToHuman($attribute['value']));
                     else {
                      $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, $this->_app->regionalSettings->convertTimeToHuman($attribute['value']));
                      $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'H:i',datepicker:false,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     }
                     break;
        case 'DATETIME':
                     if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']));
                     else {
                      $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']));
                      $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y H:i',datepicker:true,timepicker:true,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     }
                     break;
        case 'DATE': if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $this->_app->regionalSettings->convertDateToHuman($attribute['value']));
                     else {
                      $inputHtml = sprintf('<input class="flb_reserve_attribute" type="text" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]" value="%s" />', $this->_params['prefix'], $id, $id, $this->_app->regionalSettings->convertDateToHuman($attribute['value']));
                      $js .= sprintf("$(function() { $('#%sattr_%d').datetimepicker({format:'d.m.Y',datepicker:true,timepicker:false,allowBlank:true,scrollInput:false}); });", $this->_params['prefix'], $id);
                     } 
                     break;
        case 'TEXTAREA':
                     if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, ifsetor($attribute['value']));
                     else $inputHtml = sprintf('<textarea class="flb_reserve_attribute" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]">%s</textarea>', $this->_params['prefix'], $id, $id, ifsetor($attribute['value']));
                     break;
        case 'LIST': if ($this->_params['readonly']) $inputHtml = sprintf('<span id="%sattr_%d">%s</span>', $this->_params['prefix'], $id, $attribute['value']);
                     else {
                      $inputHtml = sprintf('<select class="flb_reserve_attribute" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]"><option value="">%s</option>', $this->_params['prefix'], $id, $id, $this->_app->textStorage->getText('label.select_choose'));
                      foreach (explode(',',$attribute['allowedValues']) as $value) {
                        $inputHtml .= sprintf('<option value="%s"%s>%s</option>', $value, !strcmp($value,$attribute['value'])?' selected="selected"':'', $value);
                      }
                      $inputHtml .= '</select>';
                     }
                     break;
        case 'FILE': $inputHtml = sprintf('<label id="%sattr_%d_label" class="file">%s</label><input type="hidden" id="%sattr_%d" meaning="reservation_attribute" name="attribute[%d]"/><div class="file" id="%sattribute_file_%d"></div>',
                        $this->_params['prefix'], $id, $attribute['value'], $this->_params['prefix'], $id, $id, $this->_params['prefix'], $id);
                     $js .= sprintf("var uploadObj = $('#%sattribute_file_%d').uploadFile({
                                url: $('#flb_core_url_path').val()+'uploadfile.php',
                                fileName: 'uploadfile',
                                dragDrop: false,
                                maxFileCount: 1,
                                uploadStr: '%s',
                                maxFileCountErrorStr: '%s&nbsp;',
                                onSuccess: function(files,data,xhr,pd) {
                                  if (files) {
                                    var data = JSON.parse(data);
                                    $('#%sattr_%d').val(data.id);
                                    $('#%sattr_%d_label').hide();
                                    $('#%sattribute_file_%d .ajax-file-upload').hide();
                                  }
                                },
                              });", $this->_params['prefix'], $id,
                              $this->_app->textStorage->getText('button.ajax_profile_fileUpload'),
                              $this->_app->textStorage->getText('label.ajax_profile_fileUpload_maxCount'),
                              $this->_params['prefix'], $id, $this->_params['prefix'], $id, $this->_params['prefix'], $id);
                     break;
        default: $inputHtml = 'Unknown type!';
      }
      $attrHtml = sprintf('<div class="line"><span class="bold">%s: </span>%s</div>', formatAttributeName($attribute['name'], $attribute['url']), $inputHtml);
      
      $ret .= $attrHtml;
    }
    
    $this->_html = $ret;
    $this->_js = $js;
  }
  
  protected function _userRun() {
    if (isset($this->_params['target'])) {
      if (!strcmp($this->_params['target'],'backoffice')) $this->_getAttributeForBackoffice();
      elseif (!strcmp($this->_params['target'],'portal')) $this->_getAttributeForPortal();
      elseif (!strcmp($this->_params['target'],'calendar')) $this->_getAttributeForCalendar();
    } else $this->_getAttributeForPortal();
    
    $this->_result['output'] = sprintf('%s%s', $this->_js?'<script>'.$this->_js.'</script>':'', $this->_html);
  }
}

?>
