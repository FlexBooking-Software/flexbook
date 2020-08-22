<?php

class GuiEditResourcePool extends GuiElement {
  
  private function _insertActive($data) {
    $ds = new HashDataSource(new DataSourceSettings, array('Y'=>$this->_app->textStorage->getText('label.yes'),'N'=>$this->_app->textStorage->getText('label.no')));
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_active',
            'name' => 'active',
            'showDiv' => false, 
            'dataSource' => $ds,
            'value' => $data['active'],
            'userTextStorage' => false)), 'fi_active');
  }
  
  private function _insertButton($data) {
    if ($this->_app->auth->haveRight('commodity_admin', $this->_app->auth->getActualProvider())) {
      $this->insertTemplateVar('fb_resourcePoolSave',
        sprintf('<input class="fb_eSave" id="fb_eResourceSave" type="submit" name="action_eResourcePoolSave?nextAction=resourcePoolAdd" value="%s" />',
                $this->_app->textStorage->getText('button.editResourcePool_addResource')), false);
      $this->insertTemplateVar('fb_resourcePoolSave', '&nbsp;', false);
      $this->insertTemplateVar('fb_resourcePoolSave',
          sprintf('<input class="fb_eSave" id="fb_eResourceSave" type="submit" name="action_eResourcePoolSave" value="%s" />',
                  $this->_app->textStorage->getText('button.editResourcePool_save')), false);
    } else {
      $this->insertTemplateVar('fb_resourcePoolSave', '');
    }
  }
  
  private function _insertResource($data) {
    $templateResource = '';
    
    if (is_array($data['resource'])&&count($data['resource'])) {
      $templateResource .= sprintf('<div class="gridTable"><table id="fi_resource">
                        <thead><tr><th>%s</th><th>%s</th><th>%s</th><th>&nbsp;</th></tr></thead><tbody>',
                        $this->_app->textStorage->getText('label.editResourcePool_resourceName'),
                        $this->_app->textStorage->getText('label.editResourcePool_resourceUnitProfile'),
                        $this->_app->textStorage->getText('label.editResourcePool_resourcePrice'));
      $i = 0;
      foreach ($data['resource'] as $key=>$res) {
        if (!in_array($res['id'],$data['resourceId'])) continue;
        
        if ($this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
          $action = sprintf('[<a href="#" id="fi_attributeRemove">%s</a>]', $this->_app->textStorage->getText('button.grid_remove'));
        } else $action = '';
        if ($i++%2) $class = 'Even'; else $class = 'Odd';
        $formVariable = sprintf('<input type="hidden" name="resourceId[]" value="%s"/>', $res['id']);
        $templateResource .= sprintf('<tr class="%s" id="%d"><td id="name">%s</td><td id="unitprofile">%s</td><td id="price">%s / %s</td><td class="action">%s</td>%s</tr>',
                             $class,
                             $res['id'],
                             $res['name'],
                             $res['unitprofile'],
                             $res['price'], $res['pricelist'],
                             $action,
                             $formVariable);
      }
      $templateResource .= '</tbody></table></div>';
    }
    
    $this->insertTemplateVar('fi_resource', $templateResource, false);
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/ResourcePoolEdit.html');

    $validator = Validator::get('resourcePool', 'ResourcePoolValidator');
    $data = $validator->getValues();
    #adump($data);

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }
    
    if (!$data['id']) {
      $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.editResourcePool_titleNew'));
    } else {
      $this->insertTemplateVar('title', $this->_app->textStorage->getText('label.editResourcePool_titleExisting').' '.$data['name']);
    }

    $editJS = $readonlyTag = '';
    if (!$this->_app->auth->haveRight('commodity_admin', $data['providerId'])) {
      $editJS .= "$('#editResourcePool .formItem').find('input, textarea, button, select').attr('disabled','disabled');";
      $readonlyTag = ', readonly:true ';
    } else {
      $editJS .= "$('#editResourcePool').on('click','#fi_attributeRemove', function() { $(this).closest('tr').remove(); return false; });";
    }

    $tokenValues = '';
    foreach (explode(',',$data['tag']) as $tag) {
      if (!$tag) continue;

      $s = new STag;
      $s->addStatement(new SqlStatementBi($s->columns['name'], $tag, '%s=%s'));
      $s->setColumnsMask(array('tag_id', 'name'));
      $res = $this->_app->db->doQuery($s->toString());
      $row = $this->_app->db->fetchAssoc($res);
      $id = ifsetor($row['tag_id']);

      $tokenValues .= "$('#fi_tag').tokenInput('add', {id: '$id', name: '$tag'$readonlyTag});";
    }

    global $AJAX;
    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/ResourcePoolEdit.js',
      array(
        'ajaxUrl'           => $AJAX['adminUrl'],
        'ajaxUrlPath'       => dirname($AJAX['adminUrl']),
        'tagTokenInit'      => $tokenValues,
        'additionalEditJS'  => $editJS,
        'language'          => $this->_app->language->getLanguage(),
        'provider'          => $this->_app->auth->getActualProvider(),
      ));
    
    $this->_insertActive($data);
    $this->_insertResource($data);
    $this->_insertButton($data);
  }
}

?>
