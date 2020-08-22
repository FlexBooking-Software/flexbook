<?php

class AjaxGuiResourceDetail extends AjaxGuiAction2 {
  
  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_resource_%s', $this->_params['prefix'], $this->_params['resourceId']);
    $this->_class = 'flb_resource_detail';
  }
  
  protected function _initDefaultParams() {
    if (!isset($this->_params['renderText'])) $this->_params['renderText'] = array('name','description','price','attribute');
    if (!isset($this->_params['backButton'])) $this->_params['backButton'] = 1;
    
    $this->_params['resourceId'] = str_replace($this->_params['prefix'],'',$this->_params['resourceId']);
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = '<input type="hidden" id="{prefix}flb_resource_id" value="{resource_id}" />';
    
    foreach ($this->_params['renderText'] as $render) {
      switch ($render) {
        case 'name':
          $this->_guiHtml .= '<div class="label flb_resource_name_label"><span>{__label.calendar_resourceName}:</span></div><div class="value flb_resource_name">{name}</div>';
          break;
        case 'description':
          $this->_guiHtml .= '<div class="label flb_resource_description_label"><span>{__label.calendar_resourceDescription}:</span></div><div class="value flb_resource_description">{description}</div>';
          break;
        case 'price':
          $this->_guiHtml .= '<div class="label flb_resource_label_price"><span>{__label.calendar_resourcePrice}:</span></div><div class="value flb_resource_price">{price} {__label.currency_CZK} / {priceUnit}</div>';
          break;
        case 'attribute':
          $this->_guiHtml .= '<div class="flb_commodity_attributes">{attribute}</div>';
          break;
      }
    }
    
    if ($this->_params['backButton']) {
      $this->_guiHtml .= sprintf('<input type="button" id="%sflb_resource_detail_back" value="%s" />',
                                    $this->_params['prefix'], $this->_app->textStorage->getText('button.back'));
    }
    
    $this->_guiHtml .= "<script>$(document).ready(function() {
          $('#{prefix}flb_resource_{resource_id}').on('click','#{prefix}flb_resource_detail_back', function() {
            flbLoadHtml('guiResourceList', $(this).closest('.flb_output').parent(), {params});            
          });
      });</script>";
  }
  
  protected function _getAttributeGui() {
    $ret = '';
    
    $b = new BResource($this->_data['resource_id']);
    $attributes = $b->getAttribute();
    
    $category = '';
    foreach ($attributes as $id=>$attribute) {
      if (isset($this->_params['showAttribute'])&&$this->_params['showAttribute']) {
        if (!in_array($attribute['category'], $this->_params['showAttribute'])) continue;
      }
      // atributy jsou uzavreny do DIVu kategorie
      if (strcmp($category,$attribute['category'])) {
        if ($category) $ret .= '</div>';
        if ($attribute['category']) $ret .= sprintf('<div class="flb_commodity_attributecategory_name">%s</div><div class="flb_commodity_attributecategory" id="flb_commodity_attributecategory_%s">', $attribute['category'], htmlize($attribute['category']));
      }
      switch ($attribute['type']) {
        case 'NUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value']); break;
        case 'DECIMALNUMBER': $value = $this->_app->regionalSettings->convertNumberToHuman($attribute['value'],2); break;
        case 'TIME':
          $value = $this->_app->regionalSettings->convertTimeToHuman($attribute['value'],'h:m');
          if (isset($this->_params['format']['time'])) $value = date($this->_params['format']['time'], strtotime($value));
          break;
        case 'DATETIME':
          $value = $this->_app->regionalSettings->convertDateTimeToHuman($attribute['value']);
          if (isset($this->_params['format']['datetime'])) $value = date($this->_params['format']['datetime'], strtotime($value));
          break;
        case 'DATE':
          $value = $this->_app->regionalSettings->convertDateToHuman($attribute['value']);
          if (isset($this->_params['format']['date'])) $value = date($this->_params['format']['date'], strtotime($value));
          break;
        case 'FILE':
          global $AJAX;
          $value = sprintf('<a target="_attributeFile" href="%s/getfile.php?id=%s">%s</a>', dirname($AJAX['url']), $attribute['valueId'], $attribute['value']);
          break;
        default: $value = $attribute['value'];
      }
      
      $attrHtml = sprintf('<div id="flb_commodity_attribute_%s" class="flb_commodity_attribute"><div class="label">%s:</div><div class="value flb_commodity_attributevalue">%s</div></div>',
        $id, formatAttributeName($attribute['name'], $attribute['url']), $value);
      
      $ret .= $attrHtml;
      
      $category = $attribute['category'];
    }
    if ($category) $ret .= '</div>';
    
    $this->_guiParams['attribute'] = $ret;
  }

  protected function _getData() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementBi($s->columns['resource_id'], $this->_params['resourceId'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    $s->setColumnsMask(array('resource_id','name','description','price',
                             'unitprofile','unit','minimum_quantity','maximum_quantity'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_data=$this->_app->db->fetchAssoc($res)) {
      throw new ExceptionUser('FLB error: invalid resource!');
    } else {
      $this->_guiParams['resource_id'] = $this->_data['resource_id'];
      $this->_guiParams['name'] = $this->_data['name'];
      $this->_guiParams['description'] = formatCommodityDescription($this->_data['description']);
      $this->_guiParams['price'] = $this->_app->regionalSettings->convertNumberToHuman($this->_data['price'],2);
      
      $base = $this->_app->textStorage->getText('label.minute');
      $multiplier = $this->_data['unit'];
      if ($this->_data['unit']%1440 === 0) { $multiplier = $this->_data['unit']/1440; $base = $this->_app->textStorage->getText('label.day'); }
      elseif ($this->_data['unit']%60 === 0) { $multiplier = $this->_data['unit']/60; $base = $this->_app->textStorage->getText('label.hour'); }
      $this->_guiParams['priceUnit'] = sprintf('%s %s', $multiplier, $base);
    }
    
    $this->_getAttributeGui();   
  }
}

?>
