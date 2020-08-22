<?php

class ModuleSplitProviderUsers extends ExecModule {

  private function _getUsersNum() {
    $s = new SUser;
    $s->setColumnsMask(array('user_id'));
    $res = $this->_app->db->doQuery($s->toString());

    echo sprintf("Users count: %s\n\n", $this->_app->db->getRowsNumber($res));
  }

  protected function _userRun() {
    echo sprintf("Separating users per provider\n");

    $this->_getUsersNum();

    $s = new SUser;
    $s->addStatement(new SqlStatementMono($s->columns['provider_registration'],'%s>1'));
    $s->addOrder(new SqlStatementAsc($s->columns['email']));
    #$s->setLimit(2);
    $s->setColumnsMask(array('user_id','fullname','email','provider_registration',
      'firstname','lastname','email','username','password','address','phone','admin','organiser','provider','validated','disabled',
      'facebook_id','google_id','twitter_id'));
    $res = $this->_app->db->doQuery($s->toString());
    while ($row = $this->_app->db->fetchAssoc($res)) {
      echo sprintf("User: %s - %s (%s) - %sx:\n", $row['email'], $row['fullname'], $row['user_id'], $row['provider_registration']);

      $oldUserId = $row['user_id'];
      $newUserId = null;

      // projdu vsechny registrace uzivatelu s vice registracemi
      $s1 = new SUserRegistration;
      $s1->addStatement(new SqlStatementBi($s1->columns['user'], $oldUserId, '%s=%s'));
      $s1->setColumnsMask(array('userregistration_id','user','provider','provider_name','reception','organiser','power_organiser'));
      $res1 = $this->_app->db->doQuery($s1->toString());
      $this->_app->db->fetchAssoc($res1); // prvni registraci necham tak jak je
      while ($row1 = $this->_app->db->fetchAssoc($res1)) {
        echo sprintf("\t%s: ", $row1['provider_name']);

        $o = new OAddress($row['address']);
        $oldAddressData = $o->getData();
        unset($oldAddressData['address_id']);
        $o = new OAddress;
        $o->setData($oldAddressData);
        $o->save();
        $newAddressId = $o->getId();

        // pro vsechny dalsi registrace zalozim noveho uzivatele
        $o = new OUser;
        $o->setData(array(
          'firstname'   => $row['firstname'],
          'lastname'    => $row['lastname'],
          'email'       => $row['email'],
          'username'    => $row['username'],
          'password'    => $row['password'],
          'address'     => $newAddressId,
          'phone'       => $row['phone'],
          'admin'       => $row['admin'],
          'organiser'   => $row['organiser'],
          'provider'    => $row['provider'],
          'validated'   => $row['validated'],
          'disabled'    => $row['disabled'],
          'facebook_id' => $row['facebook_id'],
          'google_id'   => $row['google_id'],
          'twitter_id'  => $row['twitter_id'],
        ));
        $o->save();
        $newUserId = $o->getId();

        // prelinkuju registraci a nastavim vsechna centra
        $o = new OUserRegistration($row1['userregistration_id']);
        $oData = array('user'=>$newUserId);
        if (($row1['reception']=='Y')||($row1['organiser']=='Y')||($row1['power_organiser']=='Y')) $oData['role_center'] = 'ALL';
        $o->setData($oData);
        $o->save();

        // zmeni vsechny odkazy na uzivatele v ramci poskytovatele
        $this->_app->db->doQuery(sprintf('UPDATE reservation SET user=%d WHERE user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE reservation SET price_user=%d WHERE price_user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE reservationjournal AS rj JOIN reservation ON rj.reservation=reservation_id SET rj.change_user=%d WHERE rj.change_user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));

        $this->_app->db->doQuery(sprintf('UPDATE user_attribute JOIN attribute ON attribute=attribute_id SET user=%d WHERE user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE creditjournal SET change_user=%d WHERE change_user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE userticket JOIN ticket ON ticket=ticket_id SET user=%d WHERE user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE userticketjournal JOIN userticket ON userticket=userticket_id JOIN ticket ON ticket=ticket_id SET change_user=%d WHERE change_user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));

        $this->_app->db->doQuery(sprintf('UPDATE event SET organiser=%d WHERE organiser=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));
        $this->_app->db->doQuery(sprintf('UPDATE eventattendee JOIN event ON event=event_id SET user=%d WHERE user=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));

        $this->_app->db->doQuery(sprintf('UPDATE resource SET organiser=%d WHERE organiser=%d AND provider=%d', $newUserId, $oldUserId, $row1['provider']));

        echo sprintf("done\n");
      }
    }

    $this->_getUsersNum();

    die;
  }
}

?>
