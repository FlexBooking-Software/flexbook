<?php

class GuiGridCellID extends GuiGridCellRenderer {
  private $_columnName;

  public function __construct($params=array()) {
    parent::__construct($params);

    if (isset($params['columnName'])) {
      $this->_columnName = $params['columnName'];
    }
  }

  protected function _userRender() {
    $title = sprintf('mytitle="<b>ID:</b> %s" onmousemove="setMyTitleTimer(event);" onmouseout="cancelMyTitleTimer(event);" ', $this->_rowData[$this->_columnName]);

    $this->setTemplateString('<span class="pointerHand" {title}>{data}</span>');
    $this->insertTemplateVar('data', $this->_outputData);
    $this->insertTemplateVar('title', $title, false);
  }
}

class GridColumnFilter_customTag extends GridColumnFilter_input {
  
  protected function _getGuiElement(){
    #adump($this->getValue());
    $js = '';
    foreach (explode(',',$this->getValue()) as $tagId) {
      if (!$tagId) continue;
      
      $o = new OTag($tagId);
      $oData = $o->getData();
      $tagName = $oData['name'];
      
      $js .= "$('#" . $this->_insertGridNamePrefix().$this->getColumnId() . "').tokenInput('add', {id: $tagId, name: '$tagName'});";
    }
    if ($js) {
      $js = '$(document).ready(function() {'. $js .'});';
      #adump($js);
      
      Application::get()->document->addJavascript($js);
    }
    
    return $this->_generateGuiInput();
  }
}

class GridColumnFilter_customUser extends GridColumnFilter_inputExact {

  protected function _generateGuiInput($subid = false, $title = false, $class = null){
    $app = Application::get();

    $filterValue = null;
    if ($value = $this->getValue()) {
      $filterValue = $value;
      $s = new SUser;
      $s->addStatement(new SqlStatementBi($s->columns['user_id'], $value, '%s=%s'));
      $s->setColumnsMask(array('fullname'));
      $res = $app->db->doQuery($s->toString());
      $row = $app->db->fetchAssoc($res);
      $value = $row['fullname'];
    }

    if (is_array($value) && $subid && isset($value[$subid])) $value = $value[$subid];
    $id = $this->getColumnId();
    $label = $this->getLabel();
    if ($title) $label = $title;
    $input = new GuiFormInput(array_merge(array(
      'id' => $this->_insertGridNamePrefix().$id.($subid ? '_'.$subid : ''),
      'label' => $label,
      'labelHtmlize' => $this->getLabelHtml(),
      'name' => 'filter_customerUser',
      'value' => $value), $this->getFilterParams()));
    if ($class) {
      $input->addClassDiv($class);
    }
    if ($this->_classDiv) {
      $input->addClassDiv($this->_classDiv);
    }
    if ($this->_classInput) {
      $intput->addClassInput($this->_classInput);
    }

    $hidden = new GuiFormInput(array_merge(array(
      'id' => $this->_insertGridNamePrefix().$id.'_id'.($subid ? '_'.$subid : ''),
      'type' => 'hidden',
      'showDiv' => false,
      'name' => 'filter['. $id .']'.($subid ? '['.$subid.']' : ''),
      'value' => $filterValue), $this->getFilterParams()));

    $gui = new GuiElement;
    $gui->insert($input);
    $gui->insert($hidden);
    return $gui;
  }

  protected function _getGuiElement(){
    global $AJAX;

    $app = Application::get();

    $nameColumnId = $this->_insertGridNamePrefix().$this->getColumnId();
    $idColumnId = $this->_insertGridNamePrefix().$this->getColumnId().'_id';

    $js = sprintf("$('#%s').combogrid({
            required: true,
            url: '%s?action=getUser&provider=%s&sessid=%s',
            debug: true,
            colModel: [{'columnName':'id','width':'10','label':'id','hidden':'true'},
                       {'columnName':'name','width':'30','label':'Jméno','align':'left'},
                       {'columnName':'address','width':'40','label':'Adresa','align':'left'},
                       {'columnName':'email','width':'30','label':'Email','align':'left'}],
            select: function(event,ui) {
              $('#%s').val(ui.item.name);
              $('#%s').val(ui.item.id);
        
              return false;
            }
          });",
      $nameColumnId,
      $AJAX['adminUrl'], $app->auth->getActualProvider(), $app->session->getId(),
      $nameColumnId, $idColumnId);
    if ($js) {
      $js = '$(document).ready(function() {'. $js .'});';

      $app->document->addJavascript($js);
    }

    return $this->_generateGuiInput();
  }
}

class GridColumnFilter_calendar extends GridColumnFilter_input {

  protected function _getGuiElement(){
    $js = "$('#" . $this->_insertGridNamePrefix().$this->getColumnId() . "').datetimepicker({format:'d.m.Y',lang:'cz',timepicker:false,dayOfWeekStart:'1',allowBlank:true});";
    if ($js) {
      $js = '$(document).ready(function() {'. $js .'});';

      Application::get()->document->addJavascript($js);
    }

    return $this->_generateGuiInput();
  }
}

class YesNoFilterDataSource extends HashDataSource {
  
  public function __construct() {
    $app = Application::get();
    
    $data = array(''=>$app->textStorage->getText('label.select_filter'));
    $data['Y'] = $app->textStorage->getText('label.yes');
    $data['N'] = $app->textStorage->getText('label.no');
    
    parent::__construct(new DataSourceSettings, $data);
  }
}

class NullNotNullFilterDataSource extends HashDataSource {
  
  public function __construct() {
    $app = Application::get();
    
    $data = array(''=>$app->textStorage->getText('label.select_filter'));
    $data[GridColumnFilter::getFilterIsNotNull()] = $app->textStorage->getText('label.yes');
    $data[GridColumnFilter::getFilterIsNull()] = $app->textStorage->getText('label.no');
    
    parent::__construct(new DataSourceSettings, $data);
  }
}

class SqlFilterDataSource extends HashDataSource {
  
  public function __construct($className, $statement=array(), $order=null) {
    $app = Application::get();
    
    $settingsClass = 'GridSettings'.$className;
    $settingsName = 'select'.$className;
    $selectClass = 'S'.$className;
    
    $settings = new $settingsClass($settingsName);
    $select = new $selectClass($settings->getSqlSelectSettings());

    if (is_array($statement)) {
      foreach ($statement as $st) {
        $condition = ifsetor($st['condition'],'%s=%s');
        if ($st['value']) $select->addStatement(new SqlStatementBi($select->columns[$st['source']], $st['value'], $condition));
        else $select->addStatement(new SqlStatementMono($select->columns[$st['source']], $condition));
      }
    }

    if ($order) $select->addOrder(new SqlStatementAsc($select->columns[$order]));

    $res = $app->db->doQuery($select->toString());
    $data = array(''=>$app->textStorage->getText('label.select_filter'));
    while ($row = $app->db->fetchArray($res)) {
      $data[$row[0]] = $row[1]; 
    }
  
    parent::__construct(new DataSourceSettings, $data);
  }
}

class GuiGridCellValidFor extends GuiGridCellRenderer {

  protected function _userRender() {
    $output = '';

    if ($this->_rowData['validity_type']=='LENGTH') {
      $output .= sprintf('<b>%s %s</b>', $this->_rowData['validity_count'],
        $this->_app->textStorage->getText('label.'.strtolower($this->_rowData['validity_unit']).'_l'));
    } elseif ($this->_rowData['validity_type']=='PERIOD') {
      if ($this->_rowData['validity_from']) $output .= sprintf('%s: <b>%s</b>', $this->_app->textStorage->getText('label.validFor_from'),
        $this->_app->regionalSettings->checkDate($this->_rowData['validity_from'])?$this->_app->regionalSettings->convertDateToHuman($this->_rowData['validity_from']):$this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['validity_from']));
      if ($this->_rowData['validity_to']) $output .= sprintf(' %s: <b>%s</b>', $this->_app->textStorage->getText('label.validFor_to'),
        $this->_app->regionalSettings->checkDate($this->_rowData['validity_to'])?$this->_app->regionalSettings->convertDateToHuman($this->_rowData['validity_to']):$this->_app->regionalSettings->convertDateTimeToHuman($this->_rowData['validity_to']));
    } else $output .= sprintf('<b>%s</b>',$this->_app->textStorage->getText('label.validFor_unlimited'));

    if ($this->_rowData['center']) {
      $o = new OCenter($this->_rowData['center']);
      $oData = $o->getData();
      if ($output) $output .= '<br/>';
      $output .= sprintf('<b>%s</b>: %s', $this->_app->textStorage->getText('label.validFor_center'), $oData['name']);
    } else {
      if ($output) $output .= '<br/>';
      $output .= sprintf('<b>%s</b>',$this->_app->textStorage->getText('label.validFor_allCenter'));
    }

    if ($this->_rowData['subject_tag']) {
      $tag = '';
      if ($this->_rowData['subject_tag']) {
        $s = new STag;
        $s->addStatement(new SqlStatementMono($s->columns['tag_id'], sprintf('%%s IN (%s)', $this->_rowData['subject_tag'])));
        $s->setColumnsMask(array('name'));
        $res = $this->_app->db->doQuery($s->toString());
        while ($row = $this->_app->db->fetchAssoc($res)) {
          if ($tag) $tag .= ',';
          $tag .= $row['name'];
        }
      }
      $output .= sprintf('<br/><b>%s</b>: %s', $this->_app->textStorage->getText('label.validFor_tag'), $tag);
    }

    $this->setTemplateString($output);
  }
}

?>
