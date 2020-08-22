DELIMITER $$

CREATE
    FUNCTION `gps_distance`(lat1 FLOAT,lng1 FLOAT,lat2 FLOAT,lng2 FLOAT)
    RETURNS FLOAT
    BEGIN

    SET lat1 = lat1 * pi() / 180;
    SET lng1 = lng1 * pi() / 180;
    SET lat2 = lat2 * pi() / 180;
    SET lng2 = lng2 * pi() / 180;

    RETURN acos
    (   cos(lat1)*cos(lng1)*cos(lat2)*cos(lng2)
      + cos(lat1)*sin(lng1)*cos(lat2)*sin(lng2)
      + sin(lat1)*sin(lat2)
    ) * 6372.795;

    END$$

DELIMITER ;

alter table reservation add column payment_online datetime;

create table reservationonlinepayment
(
  reservationonlinepayment_id     bigint not null auto_increment,
  reservation                     bigint,
  type                            varchar(50),
  paymentid                       varchar(20),
  paymentdesc                     varchar(255),
  status                          varchar(255),
  start_timestamp                 datetime,
  end_timestamp                   datetime,
  primary key (reservationonlinepayment_id)
);
alter table reservationonlinepayment add constraint FK_Reference_129_1 foreign key (reservation) references reservation (reservation_id) on delete restrict on update restrict;

alter table reservationjournal change column action action char(20);

create table provideraccounttype
(
  provideraccounttype_id          bigint not null auto_increment,
  provider                        bigint,
  name                            varchar(255),
  primary key (provideraccounttype_id)
);
alter table provideraccounttype add constraint FK_Reference_129_1_1 foreign key (provider) references provider (provider_id) on delete restrict on update restrict;

alter table resource add column accounttype bigint;
alter table resource add constraint FK_Reference_129_1_2 foreign key (accounttype) references provideraccounttype (provideraccounttype_id) on delete restrict on update restrict;
alter table event add column accounttype bigint;
alter table event add constraint FK_Reference_129_1_3 foreign key (accounttype) references provideraccounttype (provideraccounttype_id) on delete restrict on update restrict;

alter table attribute change column type type enum('TEXT','NUMBER','DATE','TIME','DATETIME','TEXTAREA','LIST','FILE');
alter table event add column fe_attendee_public enum('Y','N') default 'N';
alter table event add column fe_quick_reservation enum('Y','N') default 'N';

alter table file change column mime mime varchar(255);

rename table reservationonlinepayment to onlinepayment;
alter table onlinepayment change column reservationonlinepayment_id onlinepayment_id bigint auto_increment not null;
alter table onlinepayment add column target enum('RESERVATION','TICKET','CREDIT') after onlinepayment_id;
alter table onlinepayment drop foreign key FK_Reference_129_1;
alter table onlinepayment drop key FK_Reference_129_1;
alter table onlinepayment change column reservation target_id bigint;
alter table onlinepayment add column target_params varchar(255) after target_id;
alter table onlinepayment add column payed enum('Y','N') default 'N' after paymentid;
alter table onlinepayment add column amount float after onlinepayment_id;
alter table onlinepayment add column userregistration bigint after target_params;
alter table onlinepayment add column refund_timestamp datetime;
update onlinepayment set target='RESERVATION';
update onlinepayment join reservation on target='RESERVATION' and target_id=reservation_id set onlinepayment.payed='Y' where reservation.payed is not null;
update onlinepayment join reservation on target='RESERVATION' and target_id=reservation_id set onlinepayment.amount=reservation.total_price;
alter table creditjournal change column type type enum('CASH','CREDITCARD','BANK','RESERVATION','TICKET','ONLINE');

alter table eventattendee add column failed datetime;

alter table providersettings add column ticket_template text after badge_template;
alter table providersettings add column receipt_template text after ticket_template;
alter table providersettings add column receipt_number varchar(50) after receipt_template;
alter table provider add column receipt_year char(4);
alter table provider add column receipt_counter bigint;
alter table reservation add column receipt_number varchar(50);
alter table providersettings add column generate_receipt enum('Y','N') default 'N' after ticket_template;
alter table file add hash char(32) after file_id;
update file set hash=md5(file_id);

alter table notification add column provider bigint after notification_id;
alter table notification add constraint FK_Reference_129_1_1_1 foreign key (provider) references provider (provider_id) on delete restrict on update restrict;
alter table providersettings add column smtp_host varchar(255);
alter table providersettings add column smtp_user varchar(255);
alter table providersettings add column smtp_password varchar(255);
alter table providersettings add column smtp_secure varchar(255);
alter table user add unique index idx_email (email);

alter table providersettings add column invoice_month_fee float;
alter table providersettings add column invoice_reservation_fee float;
alter table providersettings add column invoice_reservation_price_fee float;
alter table providersettings add column invoice_due_length smallint;
alter table providersettings add column invoice_email varchar(255);

create table providerinvoice
(
  providerinvoice_id              bigint not null auto_increment,
  provider                        bigint,
  number                          varchar(255),
  create_date                     date,
  account_date                    date,
  due_date                        date,
  vs                              varchar(10),
  total_amount                    float,
  file                            bigint,
  created                         datetime,
  accounted                       datetime,
  primary key (providerinvoice_id)
);
alter table providerinvoice add constraint FK_Reference_130_1 foreign key (provider) references provider (provider_id) on delete restrict on update restrict;
alter table providerinvoice add constraint FK_Reference_130_2 foreign key (file) references file (file_id) on delete restrict on update restrict;
alter table providerinvoice add unique index idx_number (number);

alter table reservationjournal add column note_2 varchar(255) after note;

alter table providerinvoice add column paid datetime;
alter table providersettings add column invoice_reservation_price_paid enum('Y','N') default 'N' after invoice_reservation_price_fee;

alter table unitprofile add column time_end_from time;
alter table unitprofile add column time_end_to time;
alter table unitprofile add column unit_rounding enum('day','night') after unit;

alter table onlinepayment change target_id target_id varchar(255);
update onlinepayment set target_id=concat('|',replace(target_id,',','|'),'|') where target='RESERVATION';
alter table providersettings add column smtp_port varchar(255) after smtp_host;

alter table providersettings add column allow_skip_reservation_condition enum('Y','N') default 'N';
alter table reservationcondition add column evaluation enum('ALL','ANY') default null after name;
alter table reservationconditionitem add column cancel_before_start int after advance_payment;
alter table reservationconditionitem add column cancel_payed_before_start int after cancel_before_start;
alter table reservationconditionitem add column cancel_before_start_message varchar(500) after advance_payment_message;
alter table reservationconditionitem add column cancel_payed_before_start_message varchar(500) after cancel_before_start_message;

alter table resourcepool add column url_photo text;
CREATE TABLE `resourcepool_tag` (
  `resourcepool` bigint(20) NOT NULL,
  `tag` bigint(20) NOT NULL,
  PRIMARY KEY (`resourcepool`,`tag`),
  KEY `Reference_34_FK_22` (`resourcepool`),
  KEY `Reference_35_FK_22` (`tag`),
  CONSTRAINT `FK_Reference_34_22` FOREIGN KEY (`resourcepool`) REFERENCES `resourcepool` (`resourcepool_id`),
  CONSTRAINT `FK_Reference_35_22` FOREIGN KEY (`tag`) REFERENCES `tag` (`tag_id`)
);

alter table userregistration add column power_organiser enum('Y','N') default 'N' after organiser;

alter table providerpaymentgateway add column active enum('Y','N') default 'Y' after gateway_name;

alter table reservation add column mandatory enum('Y','N') default 'N' after number;
alter table providersettings add column allow_mandatory_reservation enum('Y','N') default 'N';
alter table providersettings add column organiser_mandatory_reservation enum('Y','N') default 'N';
alter table reservationconditionitem add column limit_quantity_type enum('ALL','MANDATORY','NON_MANDATORY') default 'ALL' after limit_quantity_period;
alter table reservationconditionitem add column limit_quantity_all_users enum('Y','N') default 'N' after limit_quantity_type;
alter table reservationconditionitem add column limit_total_quantity_type enum('ALL','MANDATORY','NON_MANDATORY') default 'ALL' after limit_total_quantity_period;
alter table notification add column error_timestamp datetime;
alter table notification add column error_text varchar(1000);

alter table notificationtemplateitem add column content_type enum('text/html','text/plain') default 'text/plain' after bcc_address;
alter table notification add column content_type enum('text/html','text/plain') default 'text/plain' after to_address;
CREATE TABLE `notification_file` (
  `notification` bigint(20) NOT NULL,
  `file` bigint(20) NOT NULL,
  PRIMARY KEY (`notification`,`file`),
  KEY `Reference_341_FK_22` (`notification`),
  KEY `Reference_351_FK_22` (`file`),
  CONSTRAINT `FK_Reference_341_22` FOREIGN KEY (`notification`) REFERENCES `notification` (`notification_id`),
  CONSTRAINT `FK_Reference_351_22` FOREIGN KEY (`file`) REFERENCES `file` (`file_id`)
);
alter table notification add column generate_params varchar(1000) after body;
alter table notification add column parsed datetime after generate_params;

alter table attribute change column type type enum('TEXT','NUMBER','DECIMALNUMBER','DATE','TIME','DATETIME','TEXTAREA','LIST','FILE');

alter table reservationconditionitem add column limit_overlap_quantity_tag varchar(255) after limit_overlap_quantity_scope;

alter table event add column fe_attendee_visible enum('Y','N','LOGGED_USER') default NULL after fe_attendee_public;
update event set fe_attendee_visible='Y' where fe_attendee_public='Y';
alter table providersettings add column organiser_mandatory_substitute enum('Y','N') default 'N';
alter table eventattendee add column substitute_mandatory enum('Y','N') default 'N' after substitute;

alter table ticket add column validity_from date;
alter table ticket add column validity_to date;
alter table ticket add column validity_type enum('LENGTH','PERIOD') default NULL after active;
update ticket set validity_type='LENGTH' where validity_count is NOT NULL;

alter table provider add column prepayment_invoice_counter bigint;
alter table provider add column invoice_counter bigint;
alter table provider add column creditnote_counter bigint;
alter table providersettings add column prepayment_invoice_template text after receipt_number;
alter table providersettings add column prepayment_invoice_number varchar(50) after prepayment_invoice_template;
alter table providersettings add column invoice_template text after prepayment_invoice_number;
alter table providersettings add column invoice_number varchar(50) after invoice_template;
alter table providersettings add column creditnote_template text after invoice_number;
alter table providersettings add column creditnote_number varchar(50) after creditnote_template;

alter table reservation add column receipt bigint default null after receipt_number;
alter table reservation add constraint FK_Reference_res_130_2 foreign key (receipt) references file (file_id) on delete restrict on update restrict;
alter table reservation add column invoice_number varchar(50) default null after receipt;
alter table reservation add column invoice bigint default null after invoice_number;
alter table reservation add constraint FK_Reference_res_130_3 foreign key (invoice) references file (file_id) on delete restrict on update restrict;

CREATE TABLE `creditnote` (
  `creditnote_id` bigint(20) NOT NULL auto_increment,
  `type` enum('RECEIPT','INVOICE') NOT NULL,
  `number` varchar(50) NOT NULL,
  `reservation` bigint(20) NOT NULL,
  `content` bigint(20) NOT NULL,
  PRIMARY KEY (`creditnote_id`),
  KEY `Reference_341_FK_22_21` (`reservation`),
  KEY `Reference_351_FK_22_22` (`content`),
  CONSTRAINT `FK_Reference_341_22_21` FOREIGN KEY (`reservation`) REFERENCES `reservation` (`reservation_id`),
  CONSTRAINT `FK_Reference_351_22_22` FOREIGN KEY (`content`) REFERENCES `file` (`file_id`)
);

CREATE TABLE `prepaymentinvoice` (
  `prepaymentinvoice_id` bigint(20) NOT NULL auto_increment,
  `number` varchar(50) NOT NULL,
  `userregistration` bigint(20) NOT NULL,
  `creditjournal` bigint(20) NOT NULL,
  `content` bigint(20) NOT NULL,
  PRIMARY KEY (`prepaymentinvoice_id`),
  KEY `Reference_341_FK_22_23` (`userregistration`),
  KEY `Reference_341_FK_22_25` (`creditjournal`),
  KEY `Reference_351_FK_22_24` (`content`),
  CONSTRAINT `FK_Reference_341_22_23` FOREIGN KEY (`userregistration`) REFERENCES `userregistration` (`userregistration_id`),
  CONSTRAINT `FK_Reference_341_22_25` FOREIGN KEY (`creditjournal`) REFERENCES `creditjournal` (`creditjournal_id`),
  CONSTRAINT `FK_Reference_351_22_24` FOREIGN KEY (`content`) REFERENCES `file` (`file_id`)
);

alter table attribute change column allowed_values allowed_values varchar(1000);

alter table resource add column organiser bigint default null after description;
alter table resource add constraint FK_Reference_resource_organiser foreign key (organiser) references user (user_id) on delete restrict on update restrict;

update user set password=md5(password);

alter table event add column repeat_weekday_order tinyint default 0 after repeat_weekday;
alter table event add column repeat_individual varchar(1000) default null after repeat_weekday_order;

CREATE TABLE `voucher` (
  `voucher_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8_czech_ci DEFAULT NULL,
  `provider` bigint(20) DEFAULT NULL,
  `center` bigint(20) DEFAULT NULL,
  `subject_tag` varchar(1000) COLLATE utf8_czech_ci DEFAULT NULL,
  `discount_amount` float DEFAULT NULL,
  `discount_proportion` float DEFAULT NULL,
  `application_total` INT DEFAULT NULL,
  `application_user` INT DEFAULT NULL,
  `active` enum('Y','N') COLLATE utf8_czech_ci DEFAULT 'Y',
  `validity_from` date DEFAULT NULL,
  `validity_to` date DEFAULT NULL,
  PRIMARY KEY (`voucher_id`),
  KEY `FK_Reference_89_v` (`provider`),
  KEY `FK_Reference_90_v` (`center`),
  CONSTRAINT `FK_Reference_89_v` FOREIGN KEY (`provider`) REFERENCES `provider` (`provider_id`),
  CONSTRAINT `FK_Reference_90_v` FOREIGN KEY (`center`) REFERENCES `center` (`center_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;
alter table reservation add column voucher bigint after total_price;
alter table reservation add column voucher_discount_amount float after voucher;
alter table reservation add constraint FK_Reference_res_130_43 foreign key (voucher) references voucher (voucher_id) on delete restrict on update restrict;

alter table event add column fe_allowed_payment tinyint default 111 not null;
alter table resource add column fe_allowed_payment tinyint default 111 not null;

alter table file add unique index idx_hash (hash);

alter table reservation add column open_onlinepayment bigint;
alter table reservation add constraint FK_Reference_res_open_op foreign key (open_onlinepayment) references onlinepayment (onlinepayment_id) on delete restrict on update restrict;

alter table attribute add column url varchar(255) after short_name;

alter table user drop key idx_email;

alter table providersettings add column allow_user_subaccount enum('Y','N') default 'N' after userregistration_validate;
alter table user add column parent_user bigint default null after user_id;
update user set validated=now() where user_id in (1,2,3,4,5,6); -- ??
alter table user add constraint FK_Reference_user_parent foreign key (parent_user) references user (user_id) on delete restrict on update restrict;

alter table eventattendeeperson add column user bigint after eventattendee;
alter table eventattendeeperson add constraint FK_Reference_eventattendeeperson_user foreign key (user) references user (user_id) on delete restrict on update restrict;

alter table notificationtemplateitem add column to_attendee enum('Y','N') default 'N' after to_user;

alter table reservationconditionitem add column limit_quantity_scope enum('USER','ALL_USERS','ATTENDEE') default 'USER' after limit_quantity_all_users;
update reservationconditionitem set limit_quantity_scope='USER' where limit_quantity_all_users='N';
update reservationconditionitem set limit_quantity_scope='ALL_USERS' where limit_quantity_all_users='Y';
alter table reservationconditionitem drop column limit_quantity_all_users;
alter table reservationconditionitem add column limit_other_scope enum('USER','ATTENDEE') default 'USER' after required_resource_all;

alter table onlinepayment change column paymentid paymentid varchar(36);

alter table user add column reservationcondition bigint;
alter table user add constraint FK_Reference_user_reservationcondition foreign key (reservationcondition) references reservationcondition (reservationcondition_id) on delete restrict on update restrict;
alter table providersettings add column user_reservationcondition bigint after allow_skip_reservation_condition;
alter table providersettings add constraint FK_Reference_providersettings_user_reservationcondition foreign key (user_reservationcondition) references reservationcondition (reservationcondition_id) on delete restrict on update restrict;
alter table reservationcondition add column description varchar(2000);

alter table reservationconditionitem add column limit_center bigint after time_to;
alter table reservationconditionitem add constraint FK_Reference_reservationconditionitem_center foreign key (limit_center) references center (center_id) on delete restrict on update restrict;
alter table reservationconditionitem add column limit_center_message varchar(500) after limit_other_scope;

CREATE TABLE providertextstorage (
  `providertextstorage_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `provider` bigint(20) not null,
  `language` char(2) not null,
  `ts_key` varchar(100) not null,
  `original_value` varchar(1000) DEFAULT NULL,
  `new_value` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`providertextstorage_id`),
  KEY `FK_Reference_89_pt_p` (`provider`),
  CONSTRAINT `FK_Reference_89_pt_p` FOREIGN KEY (`provider`) REFERENCES `provider` (`provider_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

alter table attribute add column applicable_type enum('USER','SUBACCOUNT') after applicable;

alter table providersettings add column user_unique varchar(255) after user_reservationcondition;
alter table providersettings add column user_subaccount_unique varchar(255) after user_unique;

alter table userregistration add column supervisor enum('Y','N') default 'N' after credit;
alter table userregistration add column role_center varchar(255);

alter table address add column region varchar(255) after city;

alter table attributename change column name name varchar(1000);

alter table notificationtemplate add column target enum('GENERAL','COMMODITY') after name;
update notificationtemplate set target='COMMODITY' where notificationtemplate_id in (select distinct notificationtemplate from event);
update notificationtemplate set target='COMMODITY' where notificationtemplate_id in (select distinct notificationtemplate from resource);
update notificationtemplate set target='GENERAL' where target is null and notificationtemplate_id in (select distinct notificationtemplate from provider);

alter table notification add column type varchar(50) after provider;

alter table attribute change column restricted restricted  enum('INTERNAL','READONLY','CREATEONLY');
alter table providersettings add column disable_credit enum('Y','N') default 'N';
alter table providersettings add column disable_ticket enum('Y','N') default 'N';
alter table providersettings add column disable_cash enum('Y','N') default 'N';
alter table providersettings add column disable_online enum('Y','N') default 'N';

-- TO PRODUCTION
alter table notificationtemplateitem change column body body text;
alter table attribute change column allowed_values allowed_values text;

create table documenttemplate
(
   documenttemplate_id bigint not null auto_increment,
   provider            bigint,
   name                varchar(255),
   target              enum('GENERAL','COMMODITY'),
   description         varchar(2000),
   primary key (documenttemplate_id)
);
alter table documenttemplate add constraint FK_Reference_dt_97 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;
create table documenttemplateitem
(
   documenttemplateitem_id  bigint not null auto_increment,
   documenttemplate         bigint,
   name                     varchar(255),
   code                     varchar(20),
   type                     varchar(50),
   number                   varchar(255),
   counter                  bigint,
   content                  longtext,
   primary key (documenttemplateitem_id)
);
alter table documenttemplateitem add constraint FK_Reference_dti_98 foreign key (documenttemplate)
      references documenttemplate (documenttemplate_id) on delete restrict on update restrict;

alter table providersettings add column documenttemplate bigint after user_reservationcondition;
alter table providersettings add constraint FK_Reference_providersettings_documenttemplate foreign key (documenttemplate) references documenttemplate (documenttemplate_id) on delete restrict on update restrict;
alter table resource add column documenttemplate bigint after notificationtemplate;
alter table resource add constraint FK_Reference_resource_documenttemplate foreign key (documenttemplate) references documenttemplate (documenttemplate_id) on delete restrict on update restrict;
alter table event add column documenttemplate bigint after notificationtemplate;
alter table event add constraint FK_Reference_event_documenttemplate foreign key (documenttemplate) references documenttemplate (documenttemplate_id) on delete restrict on update restrict;

alter table provider add column document_counter bigint;

create table document
(
   document_id                  bigint not null auto_increment,
   provider                     bigint,
   code                         varchar(20),
   type                         varchar(50),
   number                       varchar(255),
   user                         bigint,
   reservation                  bigint,
   content                      bigint,
   created                      datetime,
   primary key (document_id)
);
alter table document add constraint FK_Reference_document_provider foreign key (provider) references provider (provider_id) on delete restrict on update restrict;
alter table document add constraint FK_Reference_document_user foreign key (user) references user (user_id) on delete restrict on update restrict;
alter table document add constraint FK_Reference_document_reservation foreign key (reservation) references reservation (reservation_id) on delete restrict on update restrict;
alter table document add constraint FK_Reference_document_content foreign key (content) references file (file_id) on delete restrict on update restrict;

alter table providersettings add column invoice_account_from date after smtp_secure;

-- LATER
alter table reservation drop column payment_online;
alter table event drop column fe_attendee_public;
alter table provider change column receipt_year document_year char(4);
--alter table provider drop column receipt_year;
alter table providersettings change column generate_receipt generate_accounting enum('Y','N') default 'N';
--alter table providersettings drop column generate_receipt;
