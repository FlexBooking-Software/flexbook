<?php

class AjaxGuiResourceList extends AjaxGuiAction2 {

  public function __construct($request) {
    parent::__construct($request);
    
    $this->_id = sprintf('%sflb_resource_list', $this->_params['prefix']);
    $this->_class = 'flb_list flb_resource_list';
  }
  
  protected function _createTemplate() {
    $this->_guiHtml = "{resourceList}
                      <script>
                        $(document).ready(function() {
                          $('#{prefix}flb_resource_list').on('click','.flb_resource_list_item_desc', function() {
                            flbLoadHtml('guiResource{clickAction}', $('#{prefix}flb_resource_list').parent(), $.extend({params}, { resourceId: $(this).closest('.flb_resource_list_item').attr('id') }));  
                          });
                          $('#{prefix}flb_resource_list').on('click','.flb_resource_list_item_button', function() {
                            flbLoadHtml('guiResource{clickAction}', $('#{prefix}flb_resource_list').parent(), $.extend({params}, { resourceId: $(this).closest('.flb_resource_list_item').attr('id') }));  
                          });
                        });
                      </script>";
  }

  private function _parseResourceLine($lineData) {
    foreach ($lineData as $key=>$value) $data['@@'.strtoupper($key)] = $value;

    if (strpos($this->_params['resourceTemplate'],'RESOURCE_ATTRIBUTE')!==false) {
      // nejdriv vsechny atributy poskytovatele "vynuluju"
      $s = new SAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute_id','short_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@RESOURCE_ATTRIBUTE('.$row['short_name'].')'] = ''; }
      $s = new SResourceAttribute;
      $s->addStatement(new SqlStatementBi($s->columns['resource'], $lineData['id'], '%s=%s'));
      $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='COMMODITY'"));
      $s->setColumnsMask(array('attribute','short_name','value'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) { if ($row['short_name']) $data['@@RESOURCE_ATTRIBUTE('.$row['short_name'].')'] = $row['value']; }
    }

    return str_replace(array_keys($data), $data, $this->_params['resourceTemplate']);
  }

  protected function _initDefaultParams() {
    if (!isset($this->_params['clickAction'])) $this->_params['clickAction'] = 'Calendar';
    if (!isset($this->_params['resourceTemplate'])) $this->_params['resourceTemplate'] = '<span class="name">@@RESOURCE_NAME</span> <span class="center">(@@RESOURCE_CENTER)</span>';

    if (isset($this->_params['tag'])&&!is_array($this->_params['tag'])) $this->_params['tag'] = array($this->_params['tag']);
  }
  
  protected function _getData() {
    $s = new SResource;
    $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_params['provider'], '%s=%s'));
    $s->addStatement(new SqlStatementMono($s->columns['active'], "%s='Y'"));
    if (isset($this->_params['tag'])&&$this->_params['tag']) {
      $tag = $this->_params['tag'];
      foreach ($tag as $key=>$value) {
        $tag[$key] = sprintf("'%s'", $this->_app->db->escapeString($value));
      }
      $s->addStatement(new SqlStatementMono($s->columns['tag_name'], sprintf("%%s IN (%s)", implode(',',$tag))));
    }
    if (isset($this->_params['center'])&&$this->_params['center']) {
      if (!is_array($this->_params['center'])) $this->_params['center'] = array($this->_params['center']);
      $s->addStatement(new SqlStatementMono($s->columns['center'], sprintf("%%s IN (%s)", $this->_app->db->escapeString(implode(',',$this->_params['center'])))));
    }
    if (isset($this->_params['region'])&&$this->_params['region']) {
      if (!is_array($this->_params['region'])) $this->_params['region'] = array($this->_params['region']);
      $regionString = '';
      foreach ($this->_params['region'] as $region) {
        if ($regionString) $regionString .= ',';
        $regionString .= sprintf("'%s'", $this->_app->db->escapeString($region));
      }
      $s->addStatement(new SqlStatementMono($s->columns['region'], sprintf("%%s IN (%s)", $regionString)));
    }
    if (isset($this->_params['organiser'])) {
      if (!strcmp($this->_params['organiser'],'loggedInUser')) $s->addStatement(new SqlStatementBi($s->columns['organiser'], $this->_app->auth->getUserId(), '%s=%s'));
      else $s->addStatement(new SqlStatementBi($s->columns['organiser_email'], $this->_params['organiser'], '%s=%s'));
    }
    
    $s->addOrder(new SqlStatementAsc($s->columns['name']));
    $s->addOrder(new SqlStatementAsc($s->columns['center_name']));
    $s->setColumnsMask(array('resource_id','name','description','center_name','price','unit'));
    $res = $this->_app->db->doQuery($s->toString());
    if (!$this->_app->db->getRowsNumber($res)) {
      $html = sprintf('<span class="nodata">%s</span>', $this->_app->textStorage->getText('label.grid_noData'));
    } else {
      $html = ''; $count = 0;
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $base = $this->_app->textStorage->getText('label.minute');
        $multiplier = $row['unit'];
        if ($row['unit']%1440 === 0) { $multiplier = $row['unit']/1440; $base = $this->_app->textStorage->getText('label.day'); }
        elseif ($row['unit']%60 === 0) { $multiplier = $row['unit']/60; $base = $this->_app->textStorage->getText('label.hour'); }
        $priceUnit = sprintf('%s %s', $multiplier, $base);
        $price = sprintf('%s %s / %s', $this->_app->regionalSettings->convertNumberToHuman($row['price'],2), $this->_app->textStorage->getText('label.currency_CZK'), $priceUnit);

        $data = array(
          'id'                      => $row['resource_id'],
          'resource_name'           => $row['name'],
          'resource_description'    => $row['description'],
          'resource_center'         => $row['center_name'],
          'resource_price'          => $price,
        );

        $dataString = $this->_parseResourceLine($data);
        $html .= sprintf('<div class="flb_resource_list_item flb_list_item" id="%s%s"><div class="flb_resource_list_item_desc">%s</div><div class="flb_button flb_resource_list_item_button">DETAIL</div></div>',
                         $this->_params['prefix'],
                         $row['resource_id'],
                         $dataString
                        );
        $count++;
        
        if (isset($this->_params['count'])&&($count>=$this->_params['count'])) break;
      }
    }
    
    $this->_guiParams['resourceList'] = $html;
    $this->_guiParams['clickAction'] = $this->_params['clickAction'];
  }
}

?>
