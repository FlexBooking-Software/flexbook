<?php

class GuiEditTag extends GuiElement {

  private function _insertPortalSelect($data) {
    $select = new SPortal;
    $select->setColumnsMask(array('portal_id','name'));
    $ds = new SqlDataSource(new DataSourceSettings, $select);
    $this->insert(new GuiFormSelect(array(
            'id' => 'fi_portal',
            'name' => 'portal[]',
            'multiple' => true,
            'dataSource' => $ds,
            'value' => $data['portal'],
            'showDiv' => false,
            'userTextStorage' => false)), 'fi_portal');
  }

  private function _insertTagAssoc($data) {
    if ($data['id']) {
      // nactu prirazene akce/zdroje
      $commodity = array();
      $s = new SEventTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['event_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['provider_name']));
      $s->addOrder(new SqlStatementAsc($s->columns['event_name']));
      $s->setColumnsMask(array('event_id','event_name','event_start','provider_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $commodity[$row['provider_name']]['event'][$row['event_id']] = array('name'=>$row['event_name'],
                                                                             'start'=>$this->_app->regionalSettings->convertDateTimeToHuman($row['event_start']));
      }

      $s = new SResourceTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resource_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['provider_name']));
      $s->addOrder(new SqlStatementAsc($s->columns['resource_name']));
      $s->setColumnsMask(array('resource_id','resource_name','provider_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $commodity[$row['provider_name']]['resource'][$row['resource_id']] = $row['resource_name'];
      }

      $s = new SResourcePoolTag;
      $s->addStatement(new SqlStatementBi($s->columns['tag'], $data['id'], '%s=%s'));
      $s->addStatement(new SqlStatementBi($s->columns['resourcepool_provider'], $this->_app->auth->getActualProvider(), '%s=%s'));
      $s->addOrder(new SqlStatementAsc($s->columns['provider_name']));
      $s->addOrder(new SqlStatementAsc($s->columns['resourcepool_name']));
      $s->setColumnsMask(array('resourcepool_id','resourcepool_name','provider_name'));
      $res = $this->_app->db->doQuery($s->toString());
      while ($row = $this->_app->db->fetchAssoc($res)) {
        $commodity[$row['provider_name']]['resourcePool'][$row['resourcepool_id']] = $row['resourcepool_name'];
      }

      $usedTemplate = sprintf('<div class="gridTable"><table class="gridTable" id="fi_associatedCommodity"><tr><th class="checkbox">&nbsp;</th><th>%s</th><th>%s</th><th>%s</th></tr>',
        ucfirst($this->_app->textStorage->getText('label.listTag_usedDetailed_event')),
        ucfirst($this->_app->textStorage->getText('label.listTag_usedDetailed_resource')),
        ucfirst($this->_app->textStorage->getText('label.listTag_usedDetailed_resourcePool')));
      $index = 0;
      foreach ($commodity as $provider=>$c) {
        $class = $index%2?'Even':'Odd';
        $usedTemplate .= sprintf('<tr class="%s"><td class="checkbox"><input type="checkbox" class="inputCheckbox" meaning="provider"/></td>', $class);
        $events = '';
        if (isset($c['event'])) {
          foreach ($c['event'] as $id=>$event) {
            if ($events) $events .= ', ';
            $events .= sprintf('<input type="checkbox" class="checkbox" meaning="commodity" name="event" value="%s"/>&nbsp;<span title="%s">%s</span>', $id, $event['start'], $event['name']);
          }
        }
        $usedTemplate .= sprintf('<td>%s</td>', $events);
        $resources = '';
        if (isset($c['resource'])) {
          foreach ($c['resource'] as $id=>$name) {
            if ($resources) $resources .= ', ';
            $resources .= sprintf('<input type="checkbox" class="checkbox" meaning="commodity" name="resource" value="%s"/>&nbsp;%s', $id, $name);
          }
        }
        $usedTemplate .= sprintf('<td>%s</td>', $resources);
        $resourcePools = '';
        if (isset($c['resourcePool'])) {
          foreach ($c['resourcePool'] as $id=>$name) {
            if ($resourcePools) $resourcePools .= ', ';
            $resourcePools .= sprintf('<input type="checkbox" class="checkbox" meaning="commodity" name="resourcePool" value="%s"/>&nbsp;%s', $id, $name);
          }
        }
        $usedTemplate .= sprintf('<td>%s</td>', $resourcePools);

        $usedTemplate .= '</tr>';

        $index++;
      }
      $usedTemplate .= '</table></div>';

      $gui = new GuiElement(array('template'=>'
          <br/>
          <label class="bold" for="fi_portal">{__label.editTag_used}:</label>
          {fi_used}
          <div class="formButton">
            <input class="fb_eSave" id="fb_eTagCopyCommodity" type="button" name="action_eTagSave" value="{__button.editTag_copyCommodity}" />
            <input class="fb_eSave" id="fb_eTagMoveCommodity" type="button" name="action_eTagSave" value="{__button.editTag_moveCommodity}" />
            <input class="fb_eSave" id="fb_eTagDeleteCommodity" type="button" value="{__button.editTag_deleteCommodity}" />
          </div>
          <br/>
          <label class="bold">{__label.editTag_similarTag}:</label>
          {fi_similarTag}'));
      $gui->insertTemplateVar('fi_used', $usedTemplate, false);
      $gui->insert(new GuiListTag('listSimilarTag'), 'fi_similarTag');

      $this->insert($gui, 'fi_tagAssociation');

      // button na smazani celeho tagu
      $this->insertTemplateVar('fb_delete', sprintf('<input class="fb_eDelete" id="fb_eTagDelete" type="submit" name="action_eTagDelete?backwards=2" value="%s" />',
                                                    $this->_app->textStorage->getText('button.editTag_delete')), false);
    } else {
      $this->insertTemplateVar('fb_delete','');
      $this->insertTemplateVar('fi_tagAssociation','');
    }
  }

  protected function _userRender() {
    $this->setTemplateFile(dirname(__FILE__).'/TagEdit.html');

    $validator = Validator::get('tag', 'TagValidator');
    $data = $validator->getValues();

    foreach ($data as $k => $v) {
      if (!is_array($v)) { $this->insertTemplateVar($k, $v); }
    }

    if (!$data['id']) {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editTag_titleNew'));
    } else {
      $this->insertTemplatevar('title', $this->_app->textStorage->getText('label.editTag_titleExisting'));
    }
    
    $this->_insertPortalSelect($data);
    $this->_insertTagAssoc($data);

    $this->_app->document->addJavascriptTemplateFile(dirname(__FILE__).'/TagEdit.js');
  }
}

?>
