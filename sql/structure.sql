/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     31.7.2019 19:00:24                           */
/*==============================================================*/


drop table if exists address;

drop table if exists attribute;

drop table if exists attributename;

drop table if exists availabilityexception;

drop table if exists availabilityexceptionprofile;

drop table if exists availabilityexceptionprofileitem;

drop table if exists availabilityprofile;

drop table if exists availabilityprofileitem;

drop table if exists center;

drop table if exists creditjournal;

drop table if exists customer;

drop table if exists customerregistration;

drop table if exists employee;

drop table if exists eventattendee_attribute;

drop table if exists event;

drop table if exists event_attribute;

drop table if exists event_portal;

drop table if exists event_resource;

drop table if exists event_tag;

drop table if exists eventattendee;

drop table if exists eventattendeeperson;

drop table if exists file;

drop table if exists language;

drop table if exists notification;

drop table if exists notificationtemplate;

drop table if exists notificationtemplateitem;

drop table if exists pagetemplate;

drop table if exists portal;

drop table if exists portaltemplate;

drop table if exists portaltemplate_pagetemplate;

drop table if exists pricelist;

drop table if exists provider;

drop table if exists providerfee;

drop table if exists providerfile;

drop table if exists providerpaymentgateway;

drop table if exists providerportal;

drop table if exists providerportalfile;

drop table if exists providerportalmenu;

drop table if exists providerportalpage;

drop table if exists providerportaltext;

drop table if exists providersettings;

drop table if exists reservartion_attribute;

drop table if exists reservation;

drop table if exists reservationcondition;

drop table if exists reservationconditionitem;

drop table if exists reservationjournal;

drop table if exists resource;

drop table if exists resource_attribute;

drop table if exists resource_portal;

drop table if exists resource_tag;

drop table if exists resourceavailability;

drop table if exists resourcepool;

drop table if exists resourcepoolitem;

drop table if exists season;

drop table if exists service;

drop table if exists service_attribute;

drop table if exists service_resource;

drop table if exists service_tag;

drop table if exists state;

drop table if exists tag;

drop table if exists tag_portal;

drop table if exists tag_provider;

drop table if exists ticket;

drop table if exists unitprofile;

drop table if exists user;

drop table if exists user_attribute;

drop table if exists userregistration;

drop table if exists userticket;

drop table if exists userticketjournal;

drop table if exists uservalidation;

/*==============================================================*/
/* Table: address                                               */
/*==============================================================*/
create table address
(
   address_id           bigint not null auto_increment,
   street               varchar(255),
   city                 varchar(255),
   postal_code          CHAR(5),
   state                CHAR(2),
   gps_latitude         float,
   gps_longitude        float,
   primary key (address_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: attribute                                             */
/*==============================================================*/
create table attribute
(
   attribute_id         bigint not null auto_increment,
   provider             bigint,
   applicable           enum('USER','COMMODITY','RESERVATION'),
   short_name           char(50),
   restricted           enum('INTERNAL','READONLY'),
   mandatory            enum('Y','N'),
   category             varchar(255),
   sequence             tinyint,
   type                 enum('TEXT','NUMBER','DATE','TIME','DATETIME','LIST','FILE'),
   allowed_values       varchar(255),
   disabled             enum('Y','N') default 'N',
   primary key (attribute_id)
);

/*==============================================================*/
/* Table: attributename                                         */
/*==============================================================*/
create table attributename
(
   attributename_id     bigint not null auto_increment,
   attribute            bigint,
   lang                 char(2),
   name                 varchar(255),
   primary key (attributename_id)
);

/*==============================================================*/
/* Table: availabilityexception                                 */
/*==============================================================*/
create table availabilityexception
(
   availabilityexception_id bigint not null auto_increment,
   availabilityexceptionprofile bigint,
   name                 varchar(255),
   date_from            date,
   date_to              date,
   primary key (availabilityexception_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: availabilityexceptionprofile                          */
/*==============================================================*/
create table availabilityexceptionprofile
(
   availabilityexceptionprofile_id bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   primary key (availabilityexceptionprofile_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: availabilityexceptionprofileitem                      */
/*==============================================================*/
create table availabilityexceptionprofileitem
(
   availabilityexceptionprofileitem_id bigint not null auto_increment,
   availabilityexceptionprofile bigint,
   name                 varchar(255),
   time_from            datetime,
   time_to              datetime,
   repeated             enum('Y','N') default 'N',
   repeat_cycle         CHAR(10),
   repeat_weekday       tinyint(3) unsigned,
   repeat_until         date,
   primary key (availabilityexceptionprofileitem_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: availabilityprofile                                   */
/*==============================================================*/
create table availabilityprofile
(
   availabilityprofile_id bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   primary key (availabilityprofile_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: availabilityprofileitem                               */
/*==============================================================*/
create table availabilityprofileitem
(
   availabilityprofileitem_id bigint not null auto_increment,
   availabilityprofile  bigint,
   weekday              CHAR(3),
   time_from            time,
   time_to              time,
   primary key (availabilityprofileitem_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: center                                                */
/*==============================================================*/
create table center
(
   center_id            bigint not null auto_increment,
   name                 varchar(255),
   provider             bigint,
   address              bigint,
   payment_info         text,
   primary key (center_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: creditjournal                                         */
/*==============================================================*/
create table creditjournal
(
   creditjournal_id     bigint not null auto_increment,
   provider             bigint,
   userregistration     bigint,
   customerregistration bigint,
   amount               float,
   change_timestamp     datetime,
   change_user          bigint,
   flag                 enum('C','D'),
   type                 enum('CASH','CREDITCARD','BANK','RESERVATION','TICKET'),
   note                 varchar(500),
   primary key (creditjournal_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: customer                                              */
/*==============================================================*/
create table customer
(
   customer_id          bigint not null auto_increment,
   code                 CHAR(20),
   name                 varchar(255),
   address              bigint,
   ic                   CHAR(8),
   dic                  CHAR(12),
   email                varchar(255),
   phone                CHAR(15),
   credit               float,
   provider             bigint,
   primary key (customer_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: customerregistration                                  */
/*==============================================================*/
create table customerregistration
(
   customerregistration_id bigint not null auto_increment,
   customer             bigint,
   provider             bigint,
   registration_timestamp datetime,
   receive_advertising  enum('Y','N') default 'Y',
   credit               float,
   primary key (customerregistration_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: employee                                              */
/*==============================================================*/
create table employee
(
   employee_id          bigint not null auto_increment,
   customer             bigint,
   user                 bigint,
   credit_access        enum('Y','N') default 'N',
   primary key (employee_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: eventattendee_attribute                                */
/*==============================================================*/
create table eventattendee_attribute
(
   eventattendee        bigint not null,
   attribute            bigint not null,
   value                text,
   primary key (eventattendee, attribute)
);

/*==============================================================*/
/* Table: event                                                 */
/*==============================================================*/
create table event
(
   event_id             bigint not null auto_increment,
   name                 varchar(255),
   provider             bigint,
   external_id          varchar(255),
   center               bigint,
   organiser            bigint,
   start                datetime,
   end                  datetime,
   description          text,
   max_attendees        smallint,
   max_coattendees      smallint,
   max_substitutes      smallint,
   reservation_max_attendees smallint default 1,
   price                float,
   badge                enum('Y','N') default 'N',
   active               enum('Y','N') default 'Y',
   repeat_parent        bigint,
   repeat_index         smallint,
   repeat_cycle         CHAR(10),
   repeat_weekday       tinyint(3) unsigned,
   repeat_price         float,
   repeat_reservation   enum('SINGLE','PACK','BOTH') default 'SINGLE',
   repeat_until         date,
   url_description      varchar(255),
   url_price            varchar(255),
   url_opening          varchar(255),
   url_photo            text,
   reservationcondition bigint,
   notificationtemplate bigint,
   primary key (event_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: event_attribute                                       */
/*==============================================================*/
create table event_attribute
(
   event                bigint not null,
   attribute            bigint not null,
   value                text,
   primary key (event, attribute)
);

/*==============================================================*/
/* Table: event_portal                                          */
/*==============================================================*/
create table event_portal
(
   event                bigint not null,
   portal               bigint not null,
   primary key (event, portal)
)
engine = InnoDB;

/*==============================================================*/
/* Table: event_resource                                        */
/*==============================================================*/
create table event_resource
(
   event                bigint not null,
   resource             bigint not null,
   primary key (event, resource)
)
engine = InnoDB;

/*==============================================================*/
/* Table: event_tag                                             */
/*==============================================================*/
create table event_tag
(
   event                bigint not null,
   tag                  bigint not null,
   primary key (event, tag)
)
engine = InnoDB;

/*==============================================================*/
/* Table: eventattendee                                         */
/*==============================================================*/
create table eventattendee
(
   eventattendee_id     bigint not null auto_increment,
   event                bigint not null,
   reservation          bigint,
   subscription_time    datetime,
   substitute           enum('Y','N') default 'N',
   places               smallint,
   user                 bigint,
   primary key (eventattendee_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: eventattendeeperson                                   */
/*==============================================================*/
create table eventattendeeperson
(
   eventattendeeperson_id bigint not null auto_increment,
   eventattendee        bigint,
   firstname            varchar(255),
   lastname             varchar(255),
   email                varchar(255),
   primary key (eventattendeeperson_id)
);

/*==============================================================*/
/* Table: file                                                  */
/*==============================================================*/
create table file
(
   file_id              bigint not null auto_increment,
   name                 varchar(255),
   mime                 char(50),
   length               bigint unsigned,
   content              longblob,
   primary key (file_id)
);

/*==============================================================*/
/* Table: language                                              */
/*==============================================================*/
create table language
(
   code                 char(2) not null,
   name                 varchar(255),
   primary key (code)
);

/*==============================================================*/
/* Table: notification                                          */
/*==============================================================*/
create table notification
(
   notification_id      bigint not null auto_increment,
   from_address         varchar(255),
   cc_address           varchar(255),
   bcc_address          varchar(255),
   to_address           varchar(255),
   subject              varchar(255),
   body                 text,
   created              datetime,
   to_send              datetime,
   sent                 datetime,
   reservation          bigint,
   reservation_not_payed enum('Y','N') default 'N',
   primary key (notification_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: notificationtemplate                                  */
/*==============================================================*/
create table notificationtemplate
(
   notificationtemplate_id bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   description          varchar(2000),
   primary key (notificationtemplate_id)
);

/*==============================================================*/
/* Table: notificationtemplateitem                              */
/*==============================================================*/
create table notificationtemplateitem
(
   notificationtemplateitem_id bigint not null auto_increment,
   notificationtemplate bigint,
   name                 varchar(255),
   type                 varchar(50),
   offset               int,
   to_provider          enum('Y','N') default 'N',
   to_organiser         enum('Y','N') default 'N',
   to_user              enum('Y','N') default 'N',
   to_substitute        enum('Y','N'),
   from_address         varchar(255),
   cc_address           varchar(255),
   bcc_address          varchar(255),
   subject              varchar(255),
   body                 varchar(2000),
   primary key (notificationtemplateitem_id)
);

/*==============================================================*/
/* Table: pagetemplate                                          */
/*==============================================================*/
create table pagetemplate
(
   pagetemplate_id      bigint not null auto_increment,
   name                 varchar(255),
   content              mediumblob,
   primary key (pagetemplate_id)
);

/*==============================================================*/
/* Table: portal                                                */
/*==============================================================*/
create table portal
(
   portal_id            bigint not null auto_increment,
   name                 varchar(255),
   primary key (portal_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: portaltemplate                                        */
/*==============================================================*/
create table portaltemplate
(
   portaltemplate_id    bigint not null auto_increment,
   name                 varchar(255),
   css                  text,
   content              mediumblob,
   preview              bigint,
   primary key (portaltemplate_id)
);

/*==============================================================*/
/* Table: portaltemplate_pagetemplate                           */
/*==============================================================*/
create table portaltemplate_pagetemplate
(
   portaltemplate       bigint not null,
   pagetemplate         bigint not null,
   menu_sequence_code   char(10),
   primary key (portaltemplate, pagetemplate)
);

/*==============================================================*/
/* Table: pricelist                                             */
/*==============================================================*/
create table pricelist
(
   pricelist_id         bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   primary key (pricelist_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: provider                                              */
/*==============================================================*/
create table provider
(
   provider_id          bigint not null auto_increment,
   invoice_other        enum('Y','N'),
   invoice_name         varchar(255),
   invoice_address      bigint,
   short_name           CHAR(20),
   bank_account_number  CHAR(16),
   bank_account_suffix  CHAR(4),
   phone_1              CHAR(15),
   phone_2              CHAR(15),
   www                  varchar(255),
   vat                  enum('Y','N'),
   vat_rate             float,
   notificationtemplate bigint,
   primary key (provider_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: providerfee                                           */
/*==============================================================*/
create table providerfee
(
   providerfee_id       bigint not null auto_increment,
   provider             bigint,
   reservation          bigint,
   fee_ammount          float,
   fee_time             datetime,
   transfer_time        datetime,
   primary key (providerfee_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: providerfile                                          */
/*==============================================================*/
create table providerfile
(
   providerfile_id      bigint,
   provider             bigint,
   short_name           varchar(50),
   name                 varchar(255),
   file                 bigint
);

/*==============================================================*/
/* Table: providerpaymentgateway                                */
/*==============================================================*/
create table providerpaymentgateway
(
   providerpaymentgateway_id bigint not null auto_increment,
   provider             bigint,
   gateway_name         varchar(255),
   gateway_params       varchar(1000),
   primary key (providerpaymentgateway_id)
);

/*==============================================================*/
/* Table: providerportal                                        */
/*==============================================================*/
create table providerportal
(
   providerportal_id    bigint not null auto_increment,
   provider             bigint,
   from_template        bigint,
   name                 varchar(255),
   url_name             varchar(50),
   active               enum('Y','N'),
   css                  text,
   javascript           text,
   content              mediumblob,
   home_page            bigint,
   primary key (providerportal_id)
);

/*==============================================================*/
/* Table: providerportalfile                                    */
/*==============================================================*/
create table providerportalfile
(
   providerportalfile_id bigint not null auto_increment,
   providerportal       bigint,
   name                 varchar(255),
   type                 char(20),
   file                 bigint,
   primary key (providerportalfile_id)
);

/*==============================================================*/
/* Table: providerportalmenu                                    */
/*==============================================================*/
create table providerportalmenu
(
   providerportalmenu_id bigint not null auto_increment,
   providerportal       bigint,
   name                 varchar(255),
   sequence_code        char(10),
   providerportalpage   bigint,
   primary key (providerportalmenu_id)
);

/*==============================================================*/
/* Table: providerportalpage                                    */
/*==============================================================*/
create table providerportalpage
(
   providerportalpage_id bigint not null auto_increment,
   providerportal       bigint,
   from_template        bigint,
   short_name           char(50),
   name                 varchar(255),
   content              mediumblob,
   primary key (providerportalpage_id)
);

/*==============================================================*/
/* Table: providerportaltext                                    */
/*==============================================================*/
create table providerportaltext
(
   providerportaltext_id bigint not null auto_increment,
   providerportal       bigint,
   placeholder          varchar(255),
   value                varchar(255),
   primary key (providerportaltext_id)
);

/*==============================================================*/
/* Table: providersettings                                      */
/*==============================================================*/
create table providersettings
(
   provider             bigint not null,
   userregistration_validate enum('Y','N') default 'Y',
   reservation_cancel_message varchar(255),
   badge_photo          bigint,
   badge_template       text,
   show_company         enum('Y','N') default 'N',
   primary key (provider)
);

/*==============================================================*/
/* Table: reservation_attribute                                */
/*==============================================================*/
create table reservation_attribute
(
   reservation          bigint not null,
   attribute            bigint not null,
   value                text,
   primary key (reservation, attribute)
);

/*==============================================================*/
/* Table: reservation                                           */
/*==============================================================*/
create table reservation
(
   reservation_id       bigint not null auto_increment,
   number               varchar(20),
   provider             bigint,
   center               bigint,
   created              datetime,
   failed               datetime,
   cancelled            datetime,
   payed                datetime,
   payed_by             enum('U','C'),
   payed_ticket         bigint,
   user                 bigint,
   customer             bigint,
   start                datetime,
   end                  datetime,
   resource             bigint,
   resource_from        datetime,
   resource_to          datetime,
   pool                 enum('Y','N') default 'N',
   event                bigint,
   event_places         smallint,
   event_pack           enum('Y','N') default 'N',
   total_price          float,
   price_comment        varchar(500),
   price_timestamp      datetime,
   price_user           bigint,
   notification         CHAR(10),
   note                 varchar(500),
   primary key (reservation_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: reservationcondition                                  */
/*==============================================================*/
create table reservationcondition
(
   reservationcondition_id bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   primary key (reservationcondition_id)
);

/*==============================================================*/
/* Table: reservationconditionitem                              */
/*==============================================================*/
create table reservationconditionitem
(
   reservationconditionitem_id bigint not null auto_increment,
   reservationcondition bigint,
   name                 varchar(255),
   time_from            datetime,
   time_to              datetime,
   limit_first_time_before_start int,
   limit_first_time_before_message varchar(500),
   limit_last_time_before_start int,
   limit_last_time_before_message varchar(500),
   limit_anonymous_before_start int,
   limit_anonymous_before_message varchar(500),
   limit_after_start_event enum('Y','N'),
   limit_after_start_event_message varchar(500),
   advance_payment      int,
   advance_payment_message varchar(500),
   limit_quantity       int,
   limit_quantity_period char(10),
   limit_quantity_message varchar(500),
   required_event       varchar(1000),
   required_event_exists enum('Y','N') default 'Y',
   required_event_all   enum('Y','N') default 'Y',
   required_event_payed enum('Y','N') default 'Y',
   required_event_message varchar(500),
   required_resource    varchar(1000),
   required_resource_exists enum('Y','N') default 'Y',
   required_resource_all enum('Y','N') default 'Y',
   required_resource_payed enum('Y','N') default 'Y',
   required_resource_message varchar(500),
   limit_total_quantity int,
   limit_total_quantity_period char(10),
   limit_total_quantity_tag varchar(255),
   limit_total_quantity_message varchar(500),
   limit_overlap_quantity int,
   limit_overlap_quantity_scope char(10),
   limit_overlap_quantity_message varchar(500),
   primary key (reservationconditionitem_id)
);

/*==============================================================*/
/* Table: reservationjournal                                    */
/*==============================================================*/
create table reservationjournal
(
   reservationjournal_id bigint not null auto_increment,
   reservation          bigint,
   change_timestamp     datetime,
   change_user          bigint,
   change_ip            char(15),
   action               char(10),
   note                 varchar(255),
   primary key (reservationjournal_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: resource                                              */
/*==============================================================*/
create table resource
(
   resource_id          bigint not null auto_increment,
   name                 varchar(255),
   provider             bigint,
   external_id          varchar(255),
   center               bigint,
   availabilityprofile  bigint,
   availabilityexceptionprofile bigint,
   unitprofile          bigint,
   description          text,
   price                float,
   pricelist            bigint,
   active               enum('Y','N') default 'Y',
   url_description      varchar(255),
   url_price            varchar(255),
   url_opening          varchar(255),
   url_photo            text,
   reservationcondition bigint,
   notificationtemplate bigint,
   primary key (resource_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: resource_attribute                                    */
/*==============================================================*/
create table resource_attribute
(
   resource             bigint not null,
   attribute            bigint not null,
   value                text,
   primary key (resource, attribute)
);

/*==============================================================*/
/* Table: resource_portal                                       */
/*==============================================================*/
create table resource_portal
(
   resource             bigint not null,
   portal               bigint not null,
   primary key (resource, portal)
)
engine = InnoDB;

/*==============================================================*/
/* Table: resource_tag                                          */
/*==============================================================*/
create table resource_tag
(
   resource             bigint not null,
   tag                  bigint not null,
   primary key (resource, tag)
)
engine = InnoDB;

/*==============================================================*/
/* Table: resourceavailability                                  */
/*==============================================================*/
create table resourceavailability
(
   resourceavailability_id bigint not null auto_increment,
   resource             bigint,
   start                datetime,
   end                  datetime,
   primary key (resourceavailability_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: resourcepool                                          */
/*==============================================================*/
create table resourcepool
(
   resourcepool_id      bigint not null auto_increment,
   provider             bigint,
   center               bigint,
   external_id          varchar(255),
   name                 varchar(255),
   description          text,
   active               enum('Y','N') default 'Y',
   primary key (resourcepool_id)
);

/*==============================================================*/
/* Table: resourcepoolitem                                      */
/*==============================================================*/
create table resourcepoolitem
(
   resourcepool         bigint not null,
   resource             bigint not null,
   primary key (resourcepool, resource)
);

/*==============================================================*/
/* Table: season                                                */
/*==============================================================*/
create table season
(
   season_id            bigint not null auto_increment,
   pricelist            bigint,
   name                 varchar(255),
   start                date,
   end                  date,
   base_price           float,
   mon_price            varchar(500),
   tue_price            varchar(500),
   wed_price            varchar(500),
   thu_price            varchar(500),
   fri_price            varchar(500),
   sat_price            varchar(500),
   sun_price            varchar(500),
   primary key (season_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: service                                               */
/*==============================================================*/
create table service
(
   service_id           bigint not null auto_increment,
   name                 varchar(255),
   provider_id          bigint,
   external_id          varchar(255),
   center               bigint,
   organiser            bigint,
   duration             int,
   description          text,
   max_attendees        smallint,
   price                float,
   price_extra_attendee float,
   active               enum('Y','N') default 'Y',
   url_description      varchar(255),
   url_price            varchar(255),
   url_opening          varchar(255),
   url_photo            text,
   notificationtemplate_id bigint,
   reservationcondition bigint,
   primary key (service_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: service_attribute                                     */
/*==============================================================*/
create table service_attribute
(
   service              bigint not null,
   attribute            bigint not null,
   primary key (service, attribute)
);

/*==============================================================*/
/* Table: service_resource                                      */
/*==============================================================*/
create table service_resource
(
   service              bigint not null,
   resource             bigint not null,
   primary key (service, resource)
);

/*==============================================================*/
/* Table: service_tag                                           */
/*==============================================================*/
create table service_tag
(
   service              bigint not null,
   tag                  bigint not null,
   primary key (service, tag)
);

/*==============================================================*/
/* Table: state                                                 */
/*==============================================================*/
create table state
(
   state_id             bigint not null auto_increment,
   code                 char(2),
   name                 varchar(255),
   disabled             enum('Y','N') default 'N',
   primary key (state_id)
);

/*==============================================================*/
/* Table: tag                                                   */
/*==============================================================*/
create table tag
(
   tag_id               bigint not null auto_increment,
   name                 varchar(255),
   primary key (tag_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: tag_portal                                            */
/*==============================================================*/
create table tag_portal
(
   tag                  bigint not null,
   portal               bigint not null,
   primary key (tag, portal)
)
engine = InnoDB;

/*==============================================================*/
/* Table: tag_provider                                          */
/*==============================================================*/
create table tag_provider
(
   tag                  bigint not null,
   provider             bigint not null,
   primary key (tag, provider)
);

/*==============================================================*/
/* Table: ticket                                                */
/*==============================================================*/
create table ticket
(
   ticket_id            bigint not null auto_increment,
   name                 varchar(255),
   provider             bigint,
   validity_count       int,
   validity_unit        enum('DAY','WEEK','MONTH','YEAR'),
   center               bigint,
   subject_tag          varchar(1000),
   price                float,
   value                float,
   active               enum('Y','N') default 'Y',
   primary key (ticket_id)
);

/*==============================================================*/
/* Table: unitprofile                                           */
/*==============================================================*/
create table unitprofile
(
   unitprofile_id       bigint not null auto_increment,
   provider             bigint,
   name                 varchar(255),
   unit                 int,
   minimum_quantity     smallint default 1,
   maximum_quantity     smallint,
   time_alignment_from  time,
   time_alignment_to    time,
   time_alignment_grid  int,
   primary key (unitprofile_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: user                                                  */
/*==============================================================*/
create table user
(
   user_id              bigint not null auto_increment,
   firstname            varchar(255),
   lastname             varchar(255),
   email                varchar(255),
   username             varchar(255),
   password             varchar(255),
   facebook_id          char(100),
   google_id            char(100),
   twitter_id           char(100),
   address              bigint,
   phone                CHAR(15),
   company              varchar(255),
   ic                   CHAR(8),
   dic                  CHAR(12),
   admin                enum('Y','N') default 'N',
   organiser            enum('Y','N') default 'N',
   provider             enum('Y','N') default 'N',
   validated            datetime,
   disabled             enum('Y','N') default 'N',
   primary key (user_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: user_attribute                                        */
/*==============================================================*/
create table user_attribute
(
   user                 bigint not null,
   attribute            bigint not null,
   value                text,
   primary key (user, attribute)
);

/*==============================================================*/
/* Table: userregistration                                      */
/*==============================================================*/
create table userregistration
(
   userregistration_id  bigint not null auto_increment,
   user                 bigint not null,
   provider             bigint not null,
   registration_timestamp datetime,
   receive_advertising  enum('Y','N') default 'Y',
   credit               float,
   organiser            enum('Y','N') default 'N',
   admin                enum('Y','N') default 'N',
   reception            enum('Y','N') default 'N',
   primary key (userregistration_id)
)
engine = InnoDB;

/*==============================================================*/
/* Table: userticket                                            */
/*==============================================================*/
create table userticket
(
   userticket_id        bigint not null auto_increment,
   user                 bigint,
   ticket               bigint,
   from_timestamp       datetime,
   to_timestamp         datetime,
   name                 varchar(255),
   original_value       float,
   value                float,
   created              datetime,
   primary key (userticket_id)
);

/*==============================================================*/
/* Table: userticketjournal                                     */
/*==============================================================*/
create table userticketjournal
(
   userticketjournal_id bigint not null auto_increment,
   userticket           bigint,
   change_timestamp     datetime,
   change_user          bigint,
   amount               float,
   flag                 enum('C','D') default 'D',
   type                 enum('CREATE','RESERVATION','REFUND'),
   note                 varchar(500),
   primary key (userticketjournal_id)
);

/*==============================================================*/
/* Table: uservalidation                                        */
/*==============================================================*/
create table uservalidation
(
   user                 bigint not null,
   validation_string    CHAR(20),
   primary key (user)
)
engine = InnoDB;

alter table attribute add constraint FK_Reference_62 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table attributename add constraint FK_Reference_63 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table attributename add constraint FK_Reference_64 foreign key (lang)
      references language (code) on delete restrict on update restrict;

alter table availabilityexception add constraint FK_Reference_4 foreign key (availabilityexceptionprofile)
      references availabilityexceptionprofile (availabilityexceptionprofile_id) on delete restrict on update restrict;

alter table availabilityexceptionprofile add constraint FK_Reference_11 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table availabilityexceptionprofileitem add constraint FK_Reference_45 foreign key (availabilityexceptionprofile)
      references availabilityexceptionprofile (availabilityexceptionprofile_id) on delete restrict on update restrict;

alter table availabilityprofile add constraint FK_Reference_13 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table availabilityprofileitem add constraint FK_Reference_3 foreign key (availabilityprofile)
      references availabilityprofile (availabilityprofile_id) on delete restrict on update restrict;

alter table center add constraint FK_Reference_37 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table center add constraint FK_Reference_38_2 foreign key (address)
      references address (address_id) on delete restrict on update restrict;

alter table creditjournal add constraint FK_Reference_112 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table creditjournal add constraint FK_Reference_49_3 foreign key (customerregistration)
      references customerregistration (customerregistration_id) on delete restrict on update restrict;

alter table creditjournal add constraint FK_Reference_51_2 foreign key (userregistration)
      references userregistration (userregistration_id) on delete restrict on update restrict;

alter table creditjournal add constraint FK_Reference_52_1 foreign key (change_user)
      references user (user_id) on delete restrict on update restrict;

alter table customer add constraint FK_Reference_17 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table customer add constraint FK_Reference_18 foreign key (address)
      references address (address_id) on delete restrict on update restrict;

alter table customerregistration add constraint FK_Reference_48_0 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table customerregistration add constraint FK_Reference_49_4 foreign key (customer)
      references customer (customer_id) on delete restrict on update restrict;

alter table employee add constraint FK_Reference_50 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table employee add constraint FK_Reference_51_1 foreign key (customer)
      references customer (customer_id) on delete restrict on update restrict;

alter table eventattendee_attribute add constraint FK_Reference_105 foreign key (eventattendee)
      references eventattendee (eventattendee_id) on delete restrict on update restrict;

alter table eventattendee_attribute add constraint FK_Reference_106 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_109 foreign key (notificationtemplate)
      references notificationtemplate (notificationtemplate_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_111 foreign key (reservationcondition)
      references reservationcondition (reservationcondition_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_14 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_38_1 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_44 foreign key (repeat_parent)
      references event (event_id) on delete restrict on update restrict;

alter table event add constraint FK_Reference_51_3 foreign key (organiser)
      references user (user_id) on delete restrict on update restrict;

alter table event_attribute add constraint FK_Reference_101 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table event_attribute add constraint FK_Reference_102 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table event_portal add constraint FK_Reference_56 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table event_portal add constraint FK_Reference_57 foreign key (portal)
      references portal (portal_id) on delete restrict on update restrict;

alter table event_resource add constraint FK_Reference_41 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table event_resource add constraint FK_Reference_42 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table event_tag add constraint FK_Reference_32_1 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table event_tag add constraint FK_Reference_33 foreign key (tag)
      references tag (tag_id) on delete restrict on update restrict;

alter table eventattendee add constraint FK_Reference_16 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table eventattendee add constraint FK_Reference_36 foreign key (reservation)
      references reservation (reservation_id) on delete restrict on update restrict;

alter table eventattendee add constraint FK_Reference_52_3 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table eventattendeeperson add constraint FK_Reference_95 foreign key (eventattendee)
      references eventattendee (eventattendee_id) on delete restrict on update restrict;

alter table notification add constraint FK_Reference_55 foreign key (reservation)
      references reservation (reservation_id) on delete restrict on update restrict;

alter table notificationtemplate add constraint FK_Reference_97 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table notificationtemplateitem add constraint FK_Reference_98 foreign key (notificationtemplate)
      references notificationtemplate (notificationtemplate_id) on delete restrict on update restrict;

alter table portaltemplate add constraint FK_Reference_84 foreign key (preview)
      references file (file_id) on delete restrict on update restrict;

alter table portaltemplate_pagetemplate add constraint FK_Reference_73 foreign key (portaltemplate)
      references portaltemplate (portaltemplate_id) on delete restrict on update restrict;

alter table portaltemplate_pagetemplate add constraint FK_Reference_74 foreign key (pagetemplate)
      references pagetemplate (pagetemplate_id) on delete restrict on update restrict;

alter table pricelist add constraint FK_Reference_48_2 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table provider add constraint FK_Reference_107 foreign key (notificationtemplate)
      references notificationtemplate (notificationtemplate_id) on delete restrict on update restrict;

alter table provider add constraint FK_Reference_7 foreign key (invoice_address)
      references address (address_id) on delete restrict on update restrict;

alter table providerfee add constraint FK_Reference_28_1 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table providerfee add constraint FK_Reference_29_1 foreign key (reservation)
      references reservation (reservation_id) on delete restrict on update restrict;

alter table providerfile add constraint FK_Reference_85 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table providerfile add constraint FK_Reference_86 foreign key (file)
      references file (file_id) on delete restrict on update restrict;

alter table providerpaymentgateway add constraint FK_Reference_88 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table providerportal add constraint FK_Reference_83 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table providerportal add constraint FK_Reference_87 foreign key (home_page)
      references providerportalpage (providerportalpage_id) on delete restrict on update restrict;

alter table providerportalfile add constraint FK_Reference_79 foreign key (providerportal)
      references providerportal (providerportal_id) on delete restrict on update restrict;

alter table providerportalfile add constraint FK_Reference_80 foreign key (file)
      references file (file_id) on delete restrict on update restrict;

alter table providerportalmenu add constraint FK_Reference_81 foreign key (providerportal)
      references providerportal (providerportal_id) on delete restrict on update restrict;

alter table providerportalmenu add constraint FK_Reference_82 foreign key (providerportalpage)
      references providerportalpage (providerportalpage_id) on delete restrict on update restrict;

alter table providerportalpage add constraint FK_Reference_76 foreign key (providerportal)
      references providerportal (providerportal_id) on delete restrict on update restrict;

alter table providerportaltext add constraint FK_Reference_78 foreign key (providerportal)
      references providerportal (providerportal_id) on delete restrict on update restrict;

alter table providersettings add constraint FK_Reference_67 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table providersettings add constraint FK_Reference_68 foreign key (badge_photo)
      references file (file_id) on delete restrict on update restrict;

alter table reservation_attribute add constraint FK_Reference_103 foreign key (reservation)
      references reservation (reservation_id) on delete restrict on update restrict;

alter table reservation_attribute add constraint FK_Reference_104 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_117 foreign key (price_user)
      references user (user_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_22 foreign key (customer)
      references customer (customer_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_23_1 foreign key (event)
      references event (event_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_24_1 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_40 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_49_2 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_52_2 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table reservation add constraint FK_Reference_96 foreign key (payed_ticket)
      references userticket (userticket_id) on delete restrict on update restrict;

alter table reservationcondition add constraint FK_Reference_69 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table reservationconditionitem add constraint FK_Reference_70 foreign key (reservationcondition)
      references reservationcondition (reservationcondition_id) on delete restrict on update restrict;

alter table reservationjournal add constraint FK_Reference_53 foreign key (reservation)
      references reservation (reservation_id) on delete restrict on update restrict;

alter table reservationjournal add constraint FK_Reference_54 foreign key (change_user)
      references user (user_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_10 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_108 foreign key (notificationtemplate)
      references notificationtemplate (notificationtemplate_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_110 foreign key (reservationcondition)
      references reservationcondition (reservationcondition_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_2 foreign key (availabilityprofile)
      references availabilityprofile (availabilityprofile_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_29_2 foreign key (unitprofile)
      references unitprofile (unitprofile_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_38_3 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_46 foreign key (pricelist)
      references pricelist (pricelist_id) on delete restrict on update restrict;

alter table resource add constraint FK_Reference_5 foreign key (availabilityexceptionprofile)
      references availabilityexceptionprofile (availabilityexceptionprofile_id) on delete restrict on update restrict;

alter table resource_attribute add constraint FK_Reference_100 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table resource_attribute add constraint FK_Reference_99 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table resource_portal add constraint FK_Reference_58 foreign key (portal)
      references portal (portal_id) on delete restrict on update restrict;

alter table resource_portal add constraint FK_Reference_59 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table resource_tag add constraint FK_Reference_34 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table resource_tag add constraint FK_Reference_35 foreign key (tag)
      references tag (tag_id) on delete restrict on update restrict;

alter table resourceavailability add constraint FK_Reference_39 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table resourcepool add constraint FK_Reference_113 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table resourcepool add constraint FK_Reference_114 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table resourcepoolitem add constraint FK_Reference_115 foreign key (resourcepool)
      references resourcepool (resourcepool_id) on delete restrict on update restrict;

alter table resourcepoolitem add constraint FK_Reference_116 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table season add constraint FK_Reference_47 foreign key (pricelist)
      references pricelist (pricelist_id) on delete restrict on update restrict;

alter table service add constraint FK_Reference_118 foreign key (provider_id)
      references provider (provider_id) on delete restrict on update restrict;

alter table service add constraint FK_Reference_119 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table service add constraint FK_Reference_120 foreign key (organiser)
      references user (user_id) on delete restrict on update restrict;

alter table service add constraint FK_Reference_123 foreign key (notificationtemplate_id)
      references notificationtemplate (notificationtemplate_id) on delete restrict on update restrict;

alter table service add constraint FK_Reference_124 foreign key (reservationcondition)
      references reservationcondition (reservationcondition_id) on delete restrict on update restrict;

alter table service_attribute add constraint FK_Reference_127 foreign key (service)
      references service (service_id) on delete restrict on update restrict;

alter table service_attribute add constraint FK_Reference_128 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table service_resource add constraint FK_Reference_125 foreign key (resource)
      references resource (resource_id) on delete restrict on update restrict;

alter table service_resource add constraint FK_Reference_126 foreign key (service)
      references service (service_id) on delete restrict on update restrict;

alter table service_tag add constraint FK_Reference_121 foreign key (service)
      references service (service_id) on delete restrict on update restrict;

alter table service_tag add constraint FK_Reference_122 foreign key (tag)
      references tag (tag_id) on delete restrict on update restrict;

alter table tag_portal add constraint FK_Reference_60 foreign key (tag)
      references tag (tag_id) on delete restrict on update restrict;

alter table tag_portal add constraint FK_Reference_61 foreign key (portal)
      references portal (portal_id) on delete restrict on update restrict;

alter table tag_provider add constraint FK_Reference_71_1 foreign key (tag)
      references tag (tag_id) on delete restrict on update restrict;

alter table tag_provider add constraint FK_Reference_72_1 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table ticket add constraint FK_Reference_89 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table ticket add constraint FK_Reference_90 foreign key (center)
      references center (center_id) on delete restrict on update restrict;

alter table unitprofile add constraint FK_Reference_28_2 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table user add constraint FK_Reference_48_1 foreign key (address)
      references address (address_id) on delete restrict on update restrict;

alter table user_attribute add constraint FK_Reference_65 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table user_attribute add constraint FK_Reference_66 foreign key (attribute)
      references attribute (attribute_id) on delete restrict on update restrict;

alter table userregistration add constraint FK_Reference_50_1 foreign key (provider)
      references provider (provider_id) on delete restrict on update restrict;

alter table userregistration add constraint FK_Reference_50_2 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table userticket add constraint FK_Reference_91 foreign key (ticket)
      references ticket (ticket_id) on delete restrict on update restrict;

alter table userticket add constraint FK_Reference_92 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

alter table userticketjournal add constraint FK_Reference_93 foreign key (userticket)
      references userticket (userticket_id) on delete restrict on update restrict;

alter table userticketjournal add constraint FK_Reference_94 foreign key (change_user)
      references user (user_id) on delete restrict on update restrict;

alter table uservalidation add constraint FK_Reference_49_1 foreign key (user)
      references user (user_id) on delete restrict on update restrict;

