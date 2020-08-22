<?php

class GuiReport extends GuiElement {
  protected $_data;
  protected $_resultData;
  protected $_section;

  protected $_attributeApplicableType;

  protected function _insertCenterSelect() {
    $select = new SCenter;
    $select->setColumnsMask(array('center_id','description'));

    if (!$this->_app->auth->isAdministrator()) $this->_data['providerId'] = $this->_app->auth->getActualProvider();
    if ($this->_data['providerId']) $select->addStatement(new SqlStatementBi($select->columns['provider'], $this->_data['providerId'], '%s=%s'));
    $select->addStatement(new SqlStatementMono($select->columns['center_id'], sprintf('%%s IN (%s)', $this->_app->auth->getAllowedCenter('list'))));

    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
      'id' => 'fi_center',
      'name' => 'centerId',
      'label' => $this->_app->textStorage->getText('label.report_center'),
      'dataSource' => $ds,
      'value' => $this->_data['centerId'],
      'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
      'userTextStorage' => false)), 'fi_center');
  }

  protected function _insertResult() {
    //adump($this->_resultData['result']);
    $templateSummary = $template = '';
    
    if (count($this->_resultData['result'])) {
      if (isset($this->_resultData['resultSummary'])&&count($this->_resultData['resultSummary'])) {
        foreach ($this->_resultData['resultSummary'] as $summary) {
          $templateSummary .= sprintf('<div class="resultSummary">%s</div>', $summary);
        }
      }
      
      $template = '<br><div class="gridTable"><table>';
      
      // hlavicka tabulky
      $template .= '<tr>';
      foreach ($this->_resultData['result'][0] as $key=>$label) {
        $template .= sprintf('<th>%s&nbsp;&nbsp;<a href="index.php?action=eReportSort&order=%s&orderDirection=asc%s"><img src="img/sort_up.gif"/></a>
                                                <a href="index.php?action=eReportSort&order=%s&orderDirection=desc%s"><img src="img/sort_down.gif"/></a>
                              </th>', $label, $key, $this->_app->session->getTagForUrl(), $key, $this->_app->session->getTagForUrl());
      }
      $template .= '</tr>';
      
      // data
      for ($i=1;$i<count($this->_resultData['result']);$i++) {
        $template .= sprintf('<tr class="%s">', $i%2?'Odd':'Even');
        foreach ($this->_resultData['result'][$i] as $key=>$value) {
          // pro sloupce, ktere potrebuji preformatovat (datum,cas,...), je zaveden specialni sloupec formatted__<puvodni_nazev>
          // ten se pouziva na vystup, pro trideni atd. se pouziva DB hodnota
          if (strpos($key, 'formatted__')!==false) continue;
          if (isset($this->_resultData['result'][$i]['formatted__'.$key])) $value = $this->_resultData['result'][$i]['formatted__'.$key];

          $template .= sprintf('<td>%s</td>', str_replace('_NEWLINE_', '<br/>', $value));
        }
        $template .= '</tr>';
      }
      
      $template .= '</table></div>';
    }
    
    $this->insertTemplateVar('fi_resultSummary', $templateSummary, false);
    $this->insertTemplateVar('fi_result', $template, false);
  }
  
  public function exportResult() {
    $ret = '';
    foreach ($this->_resultData['result'] as $item) {
      $item = str_replace(array('_NEWLINE_'), ",", $item);

      foreach ($item as $key=>$value) if (strpos($key, 'formatted__')!==false) unset($item[$key]);

      $newLine = @iconv('UTF-8','WINDOWS-1250//IGNORE',sprintf("\"%s\"\n", implode('","',$item)));
      
      $ret .= $newLine;
    }
    return $ret;
  }
  
  protected function _insertProviderSelect() {
    if ($this->_app->auth->isAdministrator()) {
      $select = new SProvider;
      $select->setColumnsMask(array('provider_id','name'));
      $ds = new SqlDataSource(new DataSourceSettings, $select);
      $this->insert(new GuiFormSelect(array(
              'id' => 'fi_provider',
              'name' => 'providerId',
              'label' => $this->_app->textStorage->getText('label.report_provider'),
              'dataSource' => $ds,
              'value' => $this->_data['providerId'],
              'firstOption' => Application::get()->textStorage->getText('label.select_noMatter'),
              'userTextStorage' => false)), 'fi_provider');
    } else {
      $this->insertTemplateVar('fi_provider', sprintf('<input type="hidden" name="providerId" id="fi_provider" value="%s" />', $this->_data['providerId']), false);
    }
  }

  protected function _insertColumn() {
    $columnV = array();
    $columnG = array();
    $groupValue = array();
    
    // nejdriv budou zobrazeny sloupce, ktere maji byt videt
    foreach ($this->_data['visibleColumn'] as $val) {
      $columnV[$val] = array('checked'=>1,'label'=>$this->_data['labelColumn'][$val]);
    }
    
    // pak pridam ostatni sloupce, ktere muzou byt videt
    global $REPORT_COLUMNS;     
    foreach ($REPORT_COLUMNS[$this->_section] as $i=>$val) {
      if (!in_array($val, $this->_data['visibleColumn'])&&isset($this->_data['labelColumn'][$val])) {
        $columnV[$val] = array('checked'=>0,'label'=>$this->_data['labelColumn'][$val]);
      }
    }
    
    // pak pridam pripadne additional informations uzivatelu/rezervaci pro poskytovatele
    if (in_array($this->_section, array('user','attendee','reservation'))) {
      if ($this->_data['providerId']) {
        $s = new SAttribute;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='USER'"));
        if ($this->_attributeApplicableType) $s->addStatement(new SqlStatementTri($s->columns['applicable_type'], $s->columns['applicable_type'], $this->_attributeApplicableType, "(%s IS NULL OR %s=%s)"));
        $s->addOrder(new SqlStatementAsc($s->columns['category']));
        $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
        $s->setColumnsMask(array('attribute_id','all_name'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $val = 'additional_'.$row['attribute_id'];
          
          if (!in_array($val, $this->_data['visibleColumn'])&&isset($this->_data['labelAdditional'][$row['attribute_id']])) {
            $columnV[$val] = array('checked'=>0,'label'=>ifsetor($this->_data['labelColumn'][$val],$this->_data['labelAdditional'][$row['attribute_id']]));
          }
        }
      }
    }
    if (in_array($this->_section, array('reservation'))) {
      if ($this->_data['providerId']) {
        $s = new SAttribute;
        $s->addStatement(new SqlStatementBi($s->columns['provider'], $this->_data['providerId'], '%s=%s'));
        $s->addStatement(new SqlStatementMono($s->columns['applicable'], "%s='RESERVATION'"));
        $s->addOrder(new SqlStatementAsc($s->columns['category']));
        $s->addOrder(new SqlStatementAsc($s->columns['sequence']));
        $s->setColumnsMask(array('attribute_id','all_name'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          $val = 'additional_'.$row['attribute_id'];
          
          if (!in_array($val, $this->_data['visibleColumn'])&&isset($this->_data['labelAdditional'][$row['attribute_id']])) {
            $columnV[$val] = array('checked'=>0,'label'=>ifsetor($this->_data['labelColumn'][$val],$this->_data['labelAdditional'][$row['attribute_id']]));
          }
        }
      }
    }
    
    // pridam sloupce, podle kterych lze groupovat
    foreach ($REPORT_COLUMNS[$this->_section] as $i=>$val) {
      $checked = in_array($val, $this->_data['groupColumn']);
      $columnG[$val] = array('checked'=>$checked,'label'=>$this->_app->textStorage->getText('label.report_'.$this->_section.'_resultCol_'.$val));
      $groupValue[$val] = ifsetor($this->_data['groupValue'][$val],'none');
    }
    
    // zobrazeni sloupcu ve spravnem poradi
    foreach ($columnV as $val=>$col) {
      if (isset($columnG[$val])) {
        $groupCheck = sprintf('<input type="checkbox" class="agr_check" name="groupColumn[]" value="%s" %s/>
                               <div id="agr_value_%s">
                               <div class="separator">&nbsp;</div><div class="separator">&nbsp;</div>
                               <input type="radio" name="groupValue[%s]" value="none" %s/><div class="separator">&nbsp;</div>
                               <input type="radio" name="groupValue[%s]" value="sum" %s/><div class="separator">&nbsp;</div>
                               <input type="radio" name="groupValue[%s]" value="count" %s/><div class="separator">&nbsp;</div>
                               <input type="radio" name="groupValue[%s]" value="list" %s/><div class="separator">&nbsp;</div>
                               </div>',
                              $val, $columnG[$val]['checked']?'checked="yes"':'',
                              $val,
                              $val, !strcmp($groupValue[$val],'none')?'checked="yes"':'',
                              $val, !strcmp($groupValue[$val],'sum')?'checked="yes"':'',
                              $val, !strcmp($groupValue[$val],'count')?'checked="yes"':'',
                              $val, !strcmp($groupValue[$val],'list')?'checked="yes"':'');
      } else {
        $groupCheck = '';
      }
      //<div class="label">%s</div>
      $checkbox = sprintf('<li class="ui-state-default">
                             <input type="checkbox" name="visibleColumn[]" value="%s" %s/>
                             <input type="text" class="label" name="labelColumn[%s]" value="%s"/>
                             %s
                             <img style="float: right;" src="img/arrange_order.png"/>
                           </li>',
                          $val, $col['checked']?'checked="yes"':'', $val, $col['label'], $groupCheck);
      $this->insertTemplateVar('fi_column', $checkbox, false);
    }  
  }
  
  protected function _userRender() {
    #adump($this->_data);
    foreach ($this->_data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    $this->_insertProviderSelect();
    $this->_insertColumn();
    
    $validator = Validator::get('result', 'ReportValidator');
    $this->_resultData = $validator->getValues();
    
    if ($this->_resultData['loaded']) {
      $this->insertTemplateVar('fb_export', sprintf('
          <input class="fb_eSave" id="fb_eReportExport" type="submit" onclick="$(\'#form\').attr(\'target\',\'_blank\');" name="action_vReportExport?section=%s" value="%s" />',
          $this->_section, $this->_app->textStorage->getText('button.report_export')), false);
      
      $this->_insertResult();
    } else {
      $this->insertTemplateVar('fb_export', '');
      $this->insertTemplateVar('fi_resultSummary', '');
      $this->insertTemplateVar('fi_result', '');
    }
    
    if (!$this->_data['showAdditional']) {
      $this->insertTemplateVar('additionalVisible', ' style="display: none;"', false);
    } else $this->insertTemplateVar('additionalVisible', '');
    
    $this->_app->document->addJavascript(sprintf("
              $(document).ready(function() {
                $('#fi_additionalDiv').draggable({scroll:false});
                
                $('#fi_additionalCloser').click(function() {
                  $('#fi_additionalDiv').css({display:'none'});
                  $('#fi_showAdditional').val('0');
                })
                
                $('#fi_additionalHref').click(function() {
                  if ($('#fi_additionalDiv').is(':visible')) {
                    $('#fi_additionalDiv').css({display:'none'});
                    $('#fi_showAdditional').val('0');
                  } else {
                    $('#fi_additionalDiv').css({display:'block'});
                    $('#fi_showAdditional').val('1');
                  }
                  
                  return false;
                });
                
                $('#fi_column').sortable({placeholder:'ui-state-highlight'});
                //$('#fi_column').disableSelection();
                
                $('.agr_check').click(function() { showHideAgrRadio(); });
                
                function showHideAgrRadio() {
                  var hideAll = $('.agr_check:checkbox:checked').length==0;
                  
                  $('.agr_check:checkbox').each(function() {
                    if (hideAll) {// || $(this).is(':checked')) {
                      $('#agr_value_'+this.value).css({display:'none'});
                      $('#agr_value_'+this.value).children(':radio').attr('disabled',true);
                    } else {
                      $('#agr_value_'+this.value).css({display:'block'});
                      $('#agr_value_'+this.value).children(':radio').attr('disabled',false);
                    }
                  })
                }
                
                showHideAgrRadio();
              });"));
  }
}

?>
