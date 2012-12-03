drop table if exists c_addresses;
create table c_addresses (
  `id` int(11) not null auto_increment,
  `customer_id` int(11) not null ,
  `company` varchar(64) not null ,
  `name` varchar(64) not null ,
  `address1` varchar(64) not null ,
  `address2` varchar(64) not null ,
  `city` varchar(32) not null ,
  `postcode` varchar(8) not null ,
  `country_id` int(11) not null ,
  `zone_id` int(11) not null ,
  PRIMARY KEY (id)
);

drop table if exists c_categories;
create table c_categories (
  `id` int(11) not null auto_increment,
  `parent_id` int(11) not null ,
  `status` tinyint(1) not null ,
  `code` varchar(64) not null ,
  `image` varchar(64) not null ,
  `priority` tinyint(4) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_categories (`id`, `parent_id`, `status`, `code`, `image`, `priority`, `date_updated`, `date_created`) values ('1', '', '1', '12345', 'categories/1-kategori-a.png', '', '2012-04-27 13:28:21', '2012-03-29 13:22:12');

insert into c_categories (`id`, `parent_id`, `status`, `code`, `image`, `priority`, `date_updated`, `date_created`) values ('15', '9', '1', '', 'categories/15-underkategori.jpg', '', '0000-00-00 00:00:00', '2012-07-10 16:12:45');

insert into c_categories (`id`, `parent_id`, `status`, `code`, `image`, `priority`, `date_updated`, `date_created`) values ('16', '15', '1', '', '', '', '0000-00-00 00:00:00', '2012-08-06 11:24:41');

insert into c_categories (`id`, `parent_id`, `status`, `code`, `image`, `priority`, `date_updated`, `date_created`) values ('17', '9', '1', '', '', '', '0000-00-00 00:00:00', '2012-08-06 11:37:17');

insert into c_categories (`id`, `parent_id`, `status`, `code`, `image`, `priority`, `date_updated`, `date_created`) values ('9', '', '1', '', 'categories/9-67736_gummiankor.jpg', '', '0000-00-00 00:00:00', '2012-07-10 15:27:22');

drop table if exists c_categories_info;
create table c_categories_info (
  `id` int(11) not null auto_increment,
  `category_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(128) not null ,
  `short_description` varchar(256) not null ,
  `description` text not null ,
  `keywords` varchar(256) not null ,
  `head_title` varchar(128) not null ,
  `meta_description` varchar(256) not null ,
  `meta_keywords` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('1', '1', 'sv', 'Kategori A', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('8', '5', 'sv', 'Ytterdörrar', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('4', '4', 'sv', 'Badkläder', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('25', '4', 'en', 'Swimwear', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('32', '9', 'en', 'Rubber Ducks', '', 'Lorem ipsum dolor sit amet, pentapolim Cyrenaeorum tertia veni est se ad suis ut a lenoni. Adipiscing enixa ait regem adventu nihil impetrat est in. Nomen in modo compungi mulierem volutpat cum magna anima interim statuam Praesta enim formam unitas reddere Dionysiadem. Horreo Athenagora deo hanc nec caecatus dum miror diligere quem acceperat utique dolorem in fuerat eum ego esse in. Atqui plurium venenosamque serpentium ne velocitate renovasti dominus, famuli sed haec sed eu fides Concordi fabricata ait.', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('9', '5', 'en', 'OUter doors', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('30', '9', 'da', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('11', '6', 'sv', 'Underkategori till innerdörrar', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('12', '6', 'en', 'Subcat', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('13', '7', 'sv', 'Fönsterkarmar', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('31', '9', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('26', '8', 'sv', 'Underkategori 2', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('23', '1', 'en', 'Category A', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('24', '2', 'en', 'Category B', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('29', '4', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('33', '9', 'sv', 'Gummiankor', '', 'Lorem ipsum dolor sit amet, pentapolim Cyrenaeorum tertia veni est se ad suis ut a lenoni. Adipiscing enixa ait regem adventu nihil impetrat est in. Nomen in modo compungi mulierem volutpat cum magna anima interim statuam Praesta enim formam unitas reddere Dionysiadem. Horreo Athenagora deo hanc nec caecatus dum miror diligere quem acceperat utique dolorem in fuerat eum ego esse in. Atqui plurium venenosamque serpentium ne velocitate renovasti dominus, famuli sed haec sed eu fides Concordi fabricata ait.', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('54', '15', 'da', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('35', '10', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('36', '10', 'en', 'Rubber Ducks', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('37', '10', 'sv', 'Gummiankor', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('39', '11', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('40', '11', 'en', 'Rubber Ducks', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('41', '11', 'sv', 'Gummiankor', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('43', '12', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('44', '12', 'en', 'Rubber Ducks', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('45', '12', 'sv', 'Gummiankor', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('56', '15', 'en', 'Sub Category', 'Lorem ipsum dolor sit amet.', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('47', '13', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('48', '13', 'en', 'Rubber Ducks', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('49', '13', 'sv', 'Gummiankor', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('55', '15', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('51', '14', 'de', '', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('52', '14', 'en', 'Rubber Ducks', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('53', '14', 'sv', 'Gummiankor', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('57', '15', 'sv', 'Underkategori', 'Lorem ipsum dolor sit amet.', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('58', '16', 'en', '3rd Level Category', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('59', '16', 'sv', '3:e nivå kategori', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('60', '17', 'en', 'Subcategory 2', '', '', '', '', '', '');

insert into c_categories_info (`id`, `category_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`) values ('61', '17', 'sv', 'Underkategori 2', '', '', '', '', '', '');

drop table if exists c_countries;
create table c_countries (
  `id` int(11) not null auto_increment,
  `status` tinyint(1) default '1' not null ,
  `name` varchar(64) not null ,
  `domestic_name` varchar(64) not null ,
  `iso_code_2` varchar(2) not null ,
  `iso_code_3` varchar(3) not null ,
  `address_format` text not null ,
  `postcode_required` tinyint(1) not null ,
  `currency_code` varchar(3) not null ,
  `phone_code` varchar(3) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id),
  KEY status (status)
);

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('1', '', 'Afghanistan', '', 'AF', 'AFG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('2', '', 'Albania', '', 'AL', 'ALB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('3', '', 'Algeria', '', 'DZ', 'DZA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('4', '', 'American Samoa', '', 'AS', 'ASM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('5', '', 'Andorra', '', 'AD', 'AND', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('6', '', 'Angola', '', 'AO', 'AGO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('7', '', 'Anguilla', '', 'AI', 'AIA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('8', '', 'Antarctica', '', 'AQ', 'ATA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('9', '', 'Antigua and Barbuda', '', 'AG', 'ATG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('10', '', 'Argentina', '', 'AR', 'ARG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('11', '', 'Armenia', '', 'AM', 'ARM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('12', '', 'Aruba', '', 'AW', 'ABW', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('13', '', 'Australia', '', 'AU', 'AUS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('14', '1', 'Austria', '', 'AT', 'AUT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 10:02:42', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('15', '', 'Azerbaijan', '', 'AZ', 'AZE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('16', '', 'Bahamas', '', 'BS', 'BHS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('17', '', 'Bahrain', '', 'BH', 'BHR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('18', '', 'Bangladesh', '', 'BD', 'BGD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('19', '', 'Barbados', '', 'BB', 'BRB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('20', '', 'Belarus', '', 'BY', 'BLR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('21', '1', 'Belgium', '', 'BE', 'BEL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('22', '', 'Belize', '', 'BZ', 'BLZ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('23', '', 'Benin', '', 'BJ', 'BEN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('24', '', 'Bermuda', '', 'BM', 'BMU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('25', '', 'Bhutan', '', 'BT', 'BTN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('26', '', 'Bolivia', '', 'BO', 'BOL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('27', '', 'Bosnia and Herzegowina', '', 'BA', 'BIH', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('28', '', 'Botswana', '', 'BW', 'BWA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('29', '', 'Bouvet Island', '', 'BV', 'BVT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('30', '', 'Brazil', '', 'BR', 'BRA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('31', '', 'British Indian Ocean Territory', '', 'IO', 'IOT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('32', '', 'Brunei Darussalam', '', 'BN', 'BRN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('33', '1', 'Bulgaria', '', 'BG', 'BGR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:56:45', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('34', '', 'Burkina Faso', '', 'BF', 'BFA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('35', '', 'Burundi', '', 'BI', 'BDI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('36', '', 'Cambodia', '', 'KH', 'KHM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('37', '', 'Cameroon', '', 'CM', 'CMR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('38', '', 'Canada', '', 'CA', 'CAN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('39', '', 'Cape Verde', '', 'CV', 'CPV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('40', '', 'Cayman Islands', '', 'KY', 'CYM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('41', '', 'Central African Republic', '', 'CF', 'CAF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('42', '', 'Chad', '', 'TD', 'TCD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('43', '', 'Chile', '', 'CL', 'CHL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('44', '', 'China', '', 'CN', 'CHN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('45', '', 'Christmas Island', '', 'CX', 'CXR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('46', '', 'Cocos (Keeling) Islands', '', 'CC', 'CCK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('47', '', 'Colombia', '', 'CO', 'COL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('48', '', 'Comoros', '', 'KM', 'COM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('49', '', 'Congo', '', 'CG', 'COG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('50', '', 'Cook Islands', '', 'CK', 'COK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('51', '', 'Costa Rica', '', 'CR', 'CRI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('52', '', 'Cote D\'Ivoire', '', 'CI', 'CIV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('53', '', 'Croatia', '', 'HR', 'HRV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('54', '', 'Cuba', '', 'CU', 'CUB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('55', '1', 'Cyprus', '', 'CY', 'CYP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:50:47', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('56', '1', 'Czech Republic', '', 'CZ', 'CZE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:51:01', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('57', '1', 'Denmark', '', 'DK', 'DNK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('58', '', 'Djibouti', '', 'DJ', 'DJI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('59', '', 'Dominica', '', 'DM', 'DMA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('60', '', 'Dominican Republic', '', 'DO', 'DOM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('61', '', 'East Timor', '', 'TP', 'TMP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('62', '', 'Ecuador', '', 'EC', 'ECU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('63', '', 'Egypt', '', 'EG', 'EGY', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('64', '', 'El Salvador', '', 'SV', 'SLV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('65', '', 'Equatorial Guinea', '', 'GQ', 'GNQ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('66', '', 'Eritrea', '', 'ER', 'ERI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('67', '1', 'Estonia', '', 'EE', 'EST', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:51:22', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('68', '', 'Ethiopia', '', 'ET', 'ETH', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('69', '', 'Falkland Islands (Malvinas)', '', 'FK', 'FLK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('70', '', 'Faroe Islands', '', 'FO', 'FRO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('71', '', 'Fiji', '', 'FJ', 'FJI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('72', '1', 'Finland', '', 'FI', 'FIN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('73', '1', 'France', '', 'FR', 'FRA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('74', '', 'France, Metropolitan', '', 'FX', 'FXX', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('75', '', 'French Guiana', '', 'GF', 'GUF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('76', '', 'French Polynesia', '', 'PF', 'PYF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('77', '', 'French Southern Territories', '', 'TF', 'ATF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('78', '', 'Gabon', '', 'GA', 'GAB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('79', '', 'Gambia', '', 'GM', 'GMB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('80', '', 'Georgia', '', 'GE', 'GEO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('81', '1', 'Germany', '', 'DE', 'DEU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('82', '', 'Ghana', '', 'GH', 'GHA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('83', '', 'Gibraltar', '', 'GI', 'GIB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('84', '1', 'Greece', '', 'GR', 'GRC', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:51:48', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('85', '', 'Greenland', '', 'GL', 'GRL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('86', '', 'Grenada', '', 'GD', 'GRD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('87', '', 'Guadeloupe', '', 'GP', 'GLP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('88', '', 'Guam', '', 'GU', 'GUM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('89', '', 'Guatemala', '', 'GT', 'GTM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('90', '', 'Guinea', '', 'GN', 'GIN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('91', '', 'Guinea-bissau', '', 'GW', 'GNB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('92', '', 'Guyana', '', 'GY', 'GUY', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('93', '', 'Haiti', '', 'HT', 'HTI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('94', '', 'Heard and Mc Donald Islands', '', 'HM', 'HMD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('95', '', 'Honduras', '', 'HN', 'HND', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('96', '', 'Hong Kong', '', 'HK', 'HKG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('97', '1', 'Hungary', '', 'HU', 'HUN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:52:09', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('98', '', 'Iceland', '', 'IS', 'ISL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('99', '', 'India', '', 'IN', 'IND', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('100', '', 'Indonesia', '', 'ID', 'IDN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('101', '', 'Iran (Islamic Republic of)', '', 'IR', 'IRN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('102', '', 'Iraq', '', 'IQ', 'IRQ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('103', '1', 'Ireland', '', 'IE', 'IRL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:52:18', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('104', '', 'Israel', '', 'IL', 'ISR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('105', '1', 'Italy', '', 'IT', 'ITA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:52:48', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('106', '', 'Jamaica', '', 'JM', 'JAM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('107', '', 'Japan', '', 'JP', 'JPN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('108', '', 'Jordan', '', 'JO', 'JOR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('109', '', 'Kazakhstan', '', 'KZ', 'KAZ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('110', '', 'Kenya', '', 'KE', 'KEN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('111', '', 'Kiribati', '', 'KI', 'KIR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('112', '', 'North Korea', '', 'KP', 'PRK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('113', '', 'Korea, Republic of', '', 'KR', 'KOR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('114', '', 'Kuwait', '', 'KW', 'KWT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('115', '', 'Kyrgyzstan', '', 'KG', 'KGZ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('116', '', 'Lao People\'s Democratic Republic', '', 'LA', 'LAO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('117', '1', 'Latvia', '', 'LV', 'LVA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:53:15', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('118', '', 'Lebanon', '', 'LB', 'LBN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('119', '', 'Lesotho', '', 'LS', 'LSO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('120', '', 'Liberia', '', 'LR', 'LBR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('121', '', 'Libyan Arab Jamahiriya', '', 'LY', 'LBY', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('122', '', 'Liechtenstein', '', 'LI', 'LIE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('123', '1', 'Lithuania', '', 'LT', 'LTU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:53:24', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('124', '1', 'Luxembourg', '', 'LU', 'LUX', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:53:35', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('125', '', 'Macau', '', 'MO', 'MAC', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('126', '', 'Macedonia', '', 'MK', 'MKD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('127', '', 'Madagascar', '', 'MG', 'MDG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('128', '', 'Malawi', '', 'MW', 'MWI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('129', '', 'Malaysia', '', 'MY', 'MYS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('130', '', 'Maldives', '', 'MV', 'MDV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('131', '', 'Mali', '', 'ML', 'MLI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('132', '1', 'Malta', '', 'MT', 'MLT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:53:50', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('133', '', 'Marshall Islands', '', 'MH', 'MHL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('134', '', 'Martinique', '', 'MQ', 'MTQ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('135', '', 'Mauritania', '', 'MR', 'MRT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('136', '', 'Mauritius', '', 'MU', 'MUS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('137', '', 'Mayotte', '', 'YT', 'MYT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('138', '', 'Mexico', '', 'MX', 'MEX', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('139', '', 'Micronesia, Federated States of', '', 'FM', 'FSM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('140', '', 'Moldova, Republic of', '', 'MD', 'MDA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('141', '', 'Monaco', '', 'MC', 'MCO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('142', '', 'Mongolia', '', 'MN', 'MNG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('143', '', 'Montserrat', '', 'MS', 'MSR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('144', '', 'Morocco', '', 'MA', 'MAR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('145', '', 'Mozambique', '', 'MZ', 'MOZ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('146', '', 'Myanmar', '', 'MM', 'MMR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('147', '', 'Namibia', '', 'NA', 'NAM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('148', '', 'Nauru', '', 'NR', 'NRU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('149', '', 'Nepal', '', 'NP', 'NPL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('150', '1', 'Netherlands', '', 'NL', 'NLD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('151', '', 'Netherlands Antilles', '', 'AN', 'ANT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('152', '', 'New Caledonia', '', 'NC', 'NCL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('153', '', 'New Zealand', '', 'NZ', 'NZL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('154', '', 'Nicaragua', '', 'NI', 'NIC', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('155', '', 'Niger', '', 'NE', 'NER', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('156', '', 'Nigeria', '', 'NG', 'NGA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('157', '', 'Niue', '', 'NU', 'NIU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('158', '', 'Norfolk Island', '', 'NF', 'NFK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('159', '', 'Northern Mariana Islands', '', 'MP', 'MNP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('160', '1', 'Norway', '', 'NO', 'NOR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('161', '', 'Oman', '', 'OM', 'OMN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('162', '', 'Pakistan', '', 'PK', 'PAK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('163', '', 'Palau', '', 'PW', 'PLW', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('164', '', 'Panama', '', 'PA', 'PAN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('165', '', 'Papua New Guinea', '', 'PG', 'PNG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('166', '', 'Paraguay', '', 'PY', 'PRY', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('167', '', 'Peru', '', 'PE', 'PER', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('168', '', 'Philippines', '', 'PH', 'PHL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('169', '', 'Pitcairn', '', 'PN', 'PCN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('170', '1', 'Poland', '', 'PL', 'POL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:54:17', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('171', '1', 'Portugal', '', 'PT', 'PRT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('172', '', 'Puerto Rico', '', 'PR', 'PRI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('173', '', 'Qatar', '', 'QA', 'QAT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('174', '', 'Reunion', '', 'RE', 'REU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('175', '1', 'Romania', '', 'RO', 'ROM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:54:36', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('176', '', 'Russian Federation', '', 'RU', 'RUS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('177', '', 'Rwanda', '', 'RW', 'RWA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('178', '', 'Saint Kitts and Nevis', '', 'KN', 'KNA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('179', '', 'Saint Lucia', '', 'LC', 'LCA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('180', '', 'Saint Vincent and the Grenadines', '', 'VC', 'VCT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('181', '', 'Samoa', '', 'WS', 'WSM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('182', '', 'San Marino', '', 'SM', 'SMR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('183', '', 'Sao Tome and Principe', '', 'ST', 'STP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('184', '', 'Saudi Arabia', '', 'SA', 'SAU', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('185', '', 'Senegal', '', 'SN', 'SEN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('186', '', 'Seychelles', '', 'SC', 'SYC', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('187', '', 'Sierra Leone', '', 'SL', 'SLE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('188', '', 'Singapore', '', 'SG', 'SGP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('189', '1', 'Slovak Republic', '', 'SK', 'SVK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-08-21 09:55:03', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('190', '1', 'Slovenia', '', 'SI', 'SVN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('191', '', 'Solomon Islands', '', 'SB', 'SLB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('192', '', 'Somalia', '', 'SO', 'SOM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('193', '', 'South Africa', '', 'ZA', 'ZAF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('194', '', 'South Georgia &amp; South Sandwich Islands', '', 'GS', 'SGS', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('195', '1', 'Spain', '', 'ES', 'ESP', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('196', '', 'Sri Lanka', '', 'LK', 'LKA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('197', '', 'St. Helena', '', 'SH', 'SHN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('198', '', 'St. Pierre and Miquelon', '', 'PM', 'SPM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('199', '', 'Sudan', '', 'SD', 'SDN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('200', '', 'Suriname', '', 'SR', 'SUR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('201', '', 'Svalbard and Jan Mayen Islands', '', 'SJ', 'SJM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('202', '', 'Swaziland', '', 'SZ', 'SWZ', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('203', '1', 'Sweden', '', 'SE', 'SWE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '2012-07-03 17:00:36', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('204', '', 'Switzerland', '', 'CH', 'CHE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('205', '', 'Syrian Arab Republic', '', 'SY', 'SYR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('206', '', 'Taiwan', '', 'TW', 'TWN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('207', '', 'Tajikistan', '', 'TJ', 'TJK', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('208', '', 'Tanzania, United Republic of', '', 'TZ', 'TZA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('209', '', 'Thailand', '', 'TH', 'THA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('210', '', 'Togo', '', 'TG', 'TGO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('211', '', 'Tokelau', '', 'TK', 'TKL', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('212', '', 'Tonga', '', 'TO', 'TON', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('213', '', 'Trinidad and Tobago', '', 'TT', 'TTO', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('214', '', 'Tunisia', '', 'TN', 'TUN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('215', '', 'Turkey', '', 'TR', 'TUR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('216', '', 'Turkmenistan', '', 'TM', 'TKM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('217', '', 'Turks and Caicos Islands', '', 'TC', 'TCA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('218', '', 'Tuvalu', '', 'TV', 'TUV', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('219', '', 'Uganda', '', 'UG', 'UGA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('220', '', 'Ukraine', '', 'UA', 'UKR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('221', '', 'United Arab Emirates', '', 'AE', 'ARE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('222', '1', 'United Kingdom', '', 'GB', 'GBR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '1', '', '', '2012-08-21 09:55:45', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('223', '1', 'United States', '', 'US', 'USA', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('224', '', 'United States Minor Outlying Islands', '', 'UM', 'UMI', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('225', '', 'Uruguay', '', 'UY', 'URY', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('226', '', 'Uzbekistan', '', 'UZ', 'UZB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('227', '', 'Vanuatu', '', 'VU', 'VUT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('228', '', 'Vatican City State (Holy See)', '', 'VA', 'VAT', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('229', '', 'Venezuela', '', 'VE', 'VEN', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('230', '', 'Viet Nam', '', 'VN', 'VNM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('231', '', 'Virgin Islands (British)', '', 'VG', 'VGB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('232', '', 'Virgin Islands (U.S.)', '', 'VI', 'VIR', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('233', '', 'Wallis and Futuna Islands', '', 'WF', 'WLF', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('234', '', 'Western Sahara', '', 'EH', 'ESH', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('235', '', 'Yemen', '', 'YE', 'YEM', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('236', '', 'Yugoslavia', '', 'YU', 'YUG', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('237', '', 'Democratic Republic of Congo', '', 'CD', 'COD', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('238', '', 'Zambia', '', 'ZM', 'ZMB', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_countries (`id`, `status`, `name`, `domestic_name`, `iso_code_2`, `iso_code_3`, `address_format`, `postcode_required`, `currency_code`, `phone_code`, `date_updated`, `date_created`) values ('239', '', 'Zimbabwe', '', 'ZW', 'ZWE', '%company
%firstname %lastname
%address1
%address2
%postcode %city
%zone_name
%country_name', '', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

drop table if exists c_currencies;
create table c_currencies (
  `id` int(11) not null auto_increment,
  `status` tinyint(1) not null ,
  `code` varchar(3) not null ,
  `name` varchar(32) not null ,
  `value` decimal(10,4) not null ,
  `decimals` tinyint(1) not null ,
  `prefix` varchar(8) not null ,
  `suffix` varchar(8) not null ,
  `priority` tinyint(4) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_currencies (`id`, `status`, `code`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) values ('1', '1', 'SEK', 'Svenska kronor', '1.0000', '2', '', ' kr', '', '2012-07-05 16:40:52', '2012-03-29 13:24:36');

insert into c_currencies (`id`, `status`, `code`, `name`, `value`, `decimals`, `prefix`, `suffix`, `priority`, `date_updated`, `date_created`) values ('2', '1', 'EUR', 'Euro', '0.1216', '2', '', ' €', '', '2012-08-01 03:14:50', '2012-05-30 20:15:31');

drop table if exists c_customers;
create table c_customers (
  `id` int(11) not null auto_increment,
  `email` varchar(128) not null ,
  `password` varchar(128) not null ,
  `tax_id` varchar(32) not null ,
  `company` varchar(64) not null ,
  `firstname` varchar(64) not null ,
  `lastname` varchar(64) not null ,
  `address1` varchar(64) not null ,
  `address2` varchar(64) not null ,
  `postcode` varchar(8) not null ,
  `city` varchar(32) not null ,
  `country_code` varchar(4) not null ,
  `zone_code` varchar(8) not null ,
  `phone` varchar(24) not null ,
  `mobile` varchar(24) not null ,
  `different_shipping_address` tinyint(1) not null ,
  `shipping_company` varchar(64) not null ,
  `shipping_firstname` varchar(64) not null ,
  `shipping_lastname` varchar(64) not null ,
  `shipping_address1` varchar(64) not null ,
  `shipping_address2` varchar(64) not null ,
  `shipping_city` varchar(32) not null ,
  `shipping_postcode` varchar(8) not null ,
  `shipping_country_code` varchar(4) not null ,
  `shipping_zone_code` varchar(8) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_customers (`id`, `email`, `password`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `mobile`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `date_updated`, `date_created`) values ('12', 'sda.almeida@gmail.com', '5ed136acb5ea85705f27df1ddf5836b6cbe6d580dc58bc5cea8db2939d85c97c', '', '', 'sandra', 'almeida', '', '', '', '', '', '', '+351918805808', '', '', '', '', '', '', '', '', '', '', '', '2012-05-30 03:44:23', '2012-05-30 03:44:23');

insert into c_customers (`id`, `email`, `password`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `mobile`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `date_updated`, `date_created`) values ('13', 'jsw-5@hotmail.com', 'fa165853d45266d32ba4593a27c65ba7d7b18751', '', '', 'johan', 'waselin', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '2012-06-02 05:53:12', '2012-06-02 05:53:12');

insert into c_customers (`id`, `email`, `password`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `mobile`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `date_updated`, `date_created`) values ('23', 'test@tim-international.net', '3b4c8d674ebaa93267719abbe7c38f01b3322ad9f47013aa59cf95ef1268c368', '', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5C', '', '72344', 'V?ster', 'SE', 'VML', '46704160090', '', '', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5C', '', 'V?ster', '72344', 'SE', 'VML', '2012-07-05 23:14:23', '2012-07-05 23:14:23');

insert into c_customers (`id`, `email`, `password`, `tax_id`, `company`, `firstname`, `lastname`, `address1`, `address2`, `postcode`, `city`, `country_code`, `zone_code`, `phone`, `mobile`, `different_shipping_address`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_zone_code`, `date_updated`, `date_created`) values ('22', 'timmy.almroth@tim-international.net', '64e23a7821c8b57b3f64589b9c47e4bbd77544a47d2e169ea1e9f9a88944444e', 'SE19800627699501', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', '72344', 'Västerås', 'SE', 'H', '46704160090', '', '', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'H', '2012-07-17 05:18:09', '2012-07-05 22:23:38');

drop table if exists c_delivery_status;
create table c_delivery_status (
  `id` int(11) not null auto_increment,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_delivery_status (`id`, `date_updated`, `date_created`) values ('1', '2012-08-08 04:29:54', '2012-08-08 04:29:54');

insert into c_delivery_status (`id`, `date_updated`, `date_created`) values ('2', '2012-08-08 04:31:06', '2012-08-08 04:30:46');

drop table if exists c_delivery_status_info;
create table c_delivery_status_info (
  `id` int(11) not null auto_increment,
  `delivery_status_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(32) not null ,
  `description` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_delivery_status_info (`id`, `delivery_status_id`, `language_code`, `name`, `description`) values ('1', '1', 'en', '1-3 days', '');

insert into c_delivery_status_info (`id`, `delivery_status_id`, `language_code`, `name`, `description`) values ('2', '1', 'sv', '1-3 dagar', '');

insert into c_delivery_status_info (`id`, `delivery_status_id`, `language_code`, `name`, `description`) values ('3', '2', 'en', '3-7 days', '');

insert into c_delivery_status_info (`id`, `delivery_status_id`, `language_code`, `name`, `description`) values ('4', '2', 'sv', '3-7 dagar', '');

drop table if exists c_geo_zones;
create table c_geo_zones (
  `id` int(11) not null auto_increment,
  `name` varchar(64) not null ,
  `description` varchar(256) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_geo_zones (`id`, `name`, `description`, `date_updated`, `date_created`) values ('1', 'EU VAT Zone', 'EU, ej Sverige', '2012-08-21 10:05:02', '2012-04-01 16:06:37');

insert into c_geo_zones (`id`, `name`, `description`, `date_updated`, `date_created`) values ('2', 'SE VAT Zone', 'Sverige', '2012-07-17 04:34:34', '2012-07-03 15:54:56');

insert into c_geo_zones (`id`, `name`, `description`, `date_updated`, `date_created`) values ('3', 'SE Shipping Zone', 'Leveranszon för Sverige', '2012-08-07 01:16:02', '2012-08-07 01:16:02');

drop table if exists c_languages;
create table c_languages (
  `id` int(11) not null auto_increment,
  `status` tinyint(1) not null ,
  `code` varchar(2) not null ,
  `name` varchar(32) not null ,
  `locale` varchar(32) not null ,
  `charset` varchar(16) not null ,
  `raw_date` varchar(32) not null ,
  `raw_time` varchar(32) not null ,
  `raw_datetime` varchar(32) not null ,
  `format_date` varchar(32) not null ,
  `format_time` varchar(32) not null ,
  `format_datetime` varchar(32) not null ,
  `decimal_point` varchar(1) not null ,
  `thousands_sep` varchar(1) not null ,
  `currency_code` varchar(3) not null ,
  `priority` tinyint(3) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  UNIQUE id (id)
);

insert into c_languages (`id`, `status`, `code`, `name`, `locale`, `charset`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) values ('1', '1', 'sv', 'Svenska', 'sv_SE.utf8', 'UTF-8', 'Y-m-d', 'H:i', 'Y-m-d H:i', '%e %b %Y', '%R', '%e %b %Y %R', ',', ' ', 'SEK', '', '2012-08-21 08:10:44', '2012-03-29 13:23:54');

insert into c_languages (`id`, `status`, `code`, `name`, `locale`, `charset`, `raw_date`, `raw_time`, `raw_datetime`, `format_date`, `format_time`, `format_datetime`, `decimal_point`, `thousands_sep`, `currency_code`, `priority`, `date_updated`, `date_created`) values ('2', '1', 'en', 'English', 'en_US.utf8', 'UTF-8', 'm/d/y', 'h:i:s A', 'm/d/y h:i:s A', '%b %e %Y', '%I:%M %p', '%b %e %Y %I:%M %p', '.', ',', 'EUR', '', '2012-08-21 08:22:16', '2012-03-29 16:20:52');

drop table if exists c_manufacturers;
create table c_manufacturers (
  `id` int(11) not null auto_increment,
  `status` tinyint(4) not null ,
  `image` varchar(64) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_manufacturers (`id`, `status`, `image`, `date_updated`, `date_created`) values ('1', '1', 'manufacturers/1-acme-corporation.jpg', '2012-07-05 16:39:27', '2012-04-11 16:32:07');

drop table if exists c_manufacturers_info;
create table c_manufacturers_info (
  `id` int(11) not null auto_increment,
  `manufacturer_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  `short_description` varchar(256) not null ,
  `description` text not null ,
  `keywords` varchar(256) not null ,
  `head_title` varchar(128) not null ,
  `meta_description` varchar(256) not null ,
  `meta_keywords` varchar(256) not null ,
  `link` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_manufacturers_info (`id`, `manufacturer_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `link`) values ('1', '', '', '', '', '', '', '', '', '', '');

insert into c_manufacturers_info (`id`, `manufacturer_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `link`) values ('2', '1', 'en', 'ACME Corporation', '', 'Lorem ipsum dolor sit amet, pentapolim Cyrenaeorum tertia veni est se ad suis ut a lenoni. Adipiscing enixa ait regem adventu nihil impetrat est in. Nomen in modo compungi mulierem volutpat cum magna anima interim statuam Praesta enim formam unitas reddere Dionysiadem. Horreo Athenagora deo hanc nec caecatus dum miror diligere quem acceperat utique dolorem in fuerat eum ego esse in.', '', '', '', '', '');

insert into c_manufacturers_info (`id`, `manufacturer_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `link`) values ('3', '1', 'sv', 'ACME Corporation', '', 'Lorem ipsum dolor sit amet, pentapolim Cyrenaeorum tertia veni est se ad suis ut a lenoni. Adipiscing enixa ait regem adventu nihil impetrat est in. Nomen in modo compungi mulierem volutpat cum magna anima interim statuam Praesta enim formam unitas reddere Dionysiadem. Horreo Athenagora deo hanc nec caecatus dum miror diligere quem acceperat utique dolorem in fuerat eum ego esse in.', '', '', '', '', '');

insert into c_manufacturers_info (`id`, `manufacturer_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `link`) values ('5', '3', 'sv', 'ACME Corporation', '', '', '', '', '', '', '');

drop table if exists c_orders;
create table c_orders (
  `id` int(11) not null auto_increment,
  `order_status_id` int(11) not null ,
  `customer_id` int(11) not null ,
  `customer_company` varchar(64) not null ,
  `customer_firstname` varchar(64) not null ,
  `customer_lastname` varchar(64) not null ,
  `customer_email` varchar(128) not null ,
  `customer_phone` varchar(24) not null ,
  `customer_mobile` varchar(24) not null ,
  `customer_tax_id` varchar(32) not null ,
  `customer_address1` varchar(64) not null ,
  `customer_address2` varchar(64) not null ,
  `customer_city` varchar(32) not null ,
  `customer_postcode` varchar(8) not null ,
  `customer_country_code` varchar(2) not null ,
  `customer_country_name` varchar(64) not null ,
  `customer_zone_code` varchar(8) not null ,
  `customer_zone_name` varchar(32) not null ,
  `shipping_company` varchar(64) not null ,
  `shipping_firstname` varchar(64) not null ,
  `shipping_lastname` varchar(64) not null ,
  `shipping_address1` varchar(64) not null ,
  `shipping_address2` varchar(64) not null ,
  `shipping_city` varchar(32) not null ,
  `shipping_postcode` varchar(8) not null ,
  `shipping_country_code` varchar(2) not null ,
  `shipping_country_name` varchar(64) not null ,
  `shipping_zone_code` varchar(8) not null ,
  `shipping_zone_name` varchar(32) not null ,
  `shipping_option_id` varchar(32) not null ,
  `shipping_option_name` varchar(64) not null ,
  `payment_option_id` varchar(32) not null ,
  `payment_option_name` varchar(64) not null ,
  `payment_transaction_id` varchar(128) not null ,
  `language_code` varchar(2) not null ,
  `weight` decimal(11,4) not null ,
  `weight_class` varchar(2) not null ,
  `currency_code` varchar(3) not null ,
  `currency_value` float not null ,
  `payment_due` float not null ,
  `tax_total` float not null ,
  `client_ip` varchar(39) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('3', '3', '', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', '', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', '', '', 'weight_table:1', 'E-post företag (Kosta viktprocent)', 'cod:a', 'Cash On Delivery (Text Way)', '', 'sv', '1.0000', 'kg', 'SEK', '1', '150', '20', '193.14.28.120', '2012-08-09 12:37:56', '2012-08-03 23:59:24');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('4', '3', '', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', '', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', '', '', 'weight_table:1', 'E-post företag (Kosta viktprocent)', 'invoice:invoice', 'Invoice (Paper Invoice)', '', 'sv', '1.0000', 'kg', 'SEK', '1', '225', '45', '193.14.28.120', '2012-08-05 06:49:11', '2012-08-04 00:00:59');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('5', '3', '', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', '', '1', '2', '3', '4', '5', '6', 'SE', 'Sweden', '', '', 'weight_table:1', 'E-post företag (Kosta viktprocent)', 'invoice:invoice', 'Invoice (Paper Invoice)', '', 'sv', '2.0000', 'kg', 'SEK', '1', '2000', '400', '193.14.28.120', '2012-08-16 04:38:18', '2012-08-04 00:06:32');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('6', '2', '', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', '', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', '', '', 'weight_table:1', 'Fraktbolag (Viktkostnad)', 'cod:a', 'Cash On Delivery (Text Way)', '', 'sv', '1.0000', 'kg', 'SEK', '1', '142.5', '12.5', '193.14.28.120', '2012-08-05 06:57:59', '2012-08-05 04:34:05');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('7', '2', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'weight_table:1', 'Fraktbolag (Viktkostnad)', 'cod:a', 'Cash On Delivery (Text Way)', '', 'sv', '1.0000', 'kg', 'SEK', '1', '162.5', '32.5', '193.14.28.120', '2012-08-05 06:59:34', '2012-08-05 06:59:34');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('8', '2', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_flat_rate:flat', 'Fast pris (Fast pris)', 'pm_cod:a', 'Cash On Delivery (Text Way)', '', 'sv', '4.0000', 'kg', 'SEK', '1', '1800', '360', '193.14.28.120', '2012-08-08 14:58:34', '2012-08-08 14:57:06');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('9', '2', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_flat_rate:flat', 'Fast pris (Fast pris)', 'pm_cod:a', 'Cash On Delivery (Text Way)', '', 'sv', '3.0000', 'kg', 'SEK', '1', '500', '100', '193.14.28.120', '2012-08-13 06:55:09', '2012-08-13 06:55:09');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('17', '', '', '', 'Timmy 2', 'Almroth', 'test2@tim-international.net', '', '', '', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'VML', 'Västmanland', '', 'Timmy 2', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'VML', 'Västmanland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_invoice:invoice', 'Invoice (Paper Invoice)', '', 'sv', '1.0000', 'kg', 'SEK', '1', '531.25', '106.25', '193.14.28.120', '2012-08-20 08:33:46', '2012-08-20 08:33:46');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('12', '', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_paypal:card', 'Paypal (Card Payment)', '', 'sv', '0.0000', 'kg', 'SEK', '1', '2.5', '0.5', '193.14.28.120', '2012-08-15 02:59:23', '2012-08-15 02:45:58');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('13', '2', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_paypal:card', 'Paypal (Card Payment)', '4D216303WF2817902', 'sv', '0.0000', 'kg', 'SEK', '1', '2.5', '0.5', '193.14.28.120', '2012-08-15 03:07:59', '2012-08-15 03:06:01');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('14', '', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_paypal:card', 'Paypal (Card Payment)', '', 'sv', '0.0000', 'kg', 'EUR', '0.121', '2.5', '0.5', '193.14.28.120', '2012-08-15 07:03:15', '2012-08-15 07:03:15');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('15', '2', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_paypal:card', 'Paypal (Card Payment)', '', 'sv', '0.0000', 'kg', 'EUR', '0.121', '2.5', '0.5', '193.14.28.120', '2012-08-15 07:08:32', '2012-08-15 07:07:07');

insert into c_orders (`id`, `order_status_id`, `customer_id`, `customer_company`, `customer_firstname`, `customer_lastname`, `customer_email`, `customer_phone`, `customer_mobile`, `customer_tax_id`, `customer_address1`, `customer_address2`, `customer_city`, `customer_postcode`, `customer_country_code`, `customer_country_name`, `customer_zone_code`, `customer_zone_name`, `shipping_company`, `shipping_firstname`, `shipping_lastname`, `shipping_address1`, `shipping_address2`, `shipping_city`, `shipping_postcode`, `shipping_country_code`, `shipping_country_name`, `shipping_zone_code`, `shipping_zone_name`, `shipping_option_id`, `shipping_option_name`, `payment_option_id`, `payment_option_name`, `payment_transaction_id`, `language_code`, `weight`, `weight_class`, `currency_code`, `currency_value`, `payment_due`, `tax_total`, `client_ip`, `date_updated`, `date_created`) values ('16', '', '22', 'TiM International', 'Timmy', 'Almroth', 'timmy.almroth@tim-international.net', '46704160090', '', 'SE19800627699501', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'TiM International', 'Timmy', 'Almroth', 'Klockargatan 5 C', '', 'Västerås', '72344', 'SE', 'Sweden', 'H', 'Halland', 'sm_free:free', 'Gratis frakt (Gratis)', 'pm_paypal:card', 'Paypal (Card Payment)', '', 'sv', '0.0000', 'kg', 'SEK', '1', '2.5', '0.5', '193.14.28.120', '2012-08-15 07:40:52', '2012-08-15 07:40:34');

drop table if exists c_orders_items;
create table c_orders_items (
  `id` int(11) not null auto_increment,
  `order_id` int(11) not null ,
  `product_id` varchar(32) not null ,
  `option_id` int(11) not null ,
  `name` varchar(128) not null ,
  `model` varchar(64) not null ,
  `sku` varchar(64) not null ,
  `upc` varchar(12) not null ,
  `taric` varchar(16) not null ,
  `quantity` int(11) not null ,
  `price` decimal(11,4) not null ,
  `tax` decimal(11,4) not null ,
  `tax_class_id` int(11) not null ,
  PRIMARY KEY (id)
);

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('11', '7', '19', '28', 'Gummianka (Grön)', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('15', '3', '20', '', 'Basketboll anka', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('9', '5', '23', '', 'Badhandukar', '', '', '', '', '4', '380.0000', '95.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('7', '6', '30', '', 'Prickig anka', '', '', '', '', '1', '80.0000', '0.0000', '');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('10', '4', '21', '', 'Ankfamilj', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('12', '8', '29', '', 'Rosa anka', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('13', '8', '25', '', 'Badhanddukar', '', '', '', '', '2', '400.0000', '100.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('14', '8', '23', '', 'Badhandukar', '', '', '', '', '1', '400.0000', '100.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('16', '9', '30', '', 'Prickig anka', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('17', '9', '26', '', 'Cool anka', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('18', '9', '21', '', 'Ankfamilj', '', '', '', '', '1', '80.0000', '20.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('34', '17', '23', '', 'Badhandukar', '', '', '', '', '1', '400.0000', '100.0000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('20', '12', '31', '', 'Paypal testprodukt åäö', '12345', '', '', '', '1', '2.0000', '0.5000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('21', '13', '31', '', 'Paypal testprodukt åäö', '12345', '', '', '', '1', '2.0000', '0.5000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('22', '14', '31', '', 'Paypal testprodukt åäö', '12345', '', '', '', '1', '2.0000', '0.5000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('23', '15', '31', '', 'Paypal testprodukt åäö', '12345', '', '', '', '1', '2.0000', '0.5000', '1');

insert into c_orders_items (`id`, `order_id`, `product_id`, `option_id`, `name`, `model`, `sku`, `upc`, `taric`, `quantity`, `price`, `tax`, `tax_class_id`) values ('24', '16', '31', '', 'Paypal testprodukt åäö', '12345', '', '', '', '1', '2.0000', '0.5000', '1');

drop table if exists c_orders_status;
create table c_orders_status (
  `id` int(11) not null auto_increment,
  `is_sale` tinyint(1) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_orders_status (`id`, `is_sale`, `date_updated`, `date_created`) values ('1', '1', '2012-08-20 07:48:46', '2012-08-05 04:24:11');

insert into c_orders_status (`id`, `is_sale`, `date_updated`, `date_created`) values ('2', '1', '2012-08-20 07:49:05', '2012-08-05 04:24:24');

insert into c_orders_status (`id`, `is_sale`, `date_updated`, `date_created`) values ('3', '1', '2012-08-20 07:48:54', '2012-08-05 04:24:42');

insert into c_orders_status (`id`, `is_sale`, `date_updated`, `date_created`) values ('4', '', '2012-08-16 07:09:04', '2012-08-16 07:03:50');

insert into c_orders_status (`id`, `is_sale`, `date_updated`, `date_created`) values ('5', '', '2012-08-16 07:07:02', '2012-08-16 07:07:02');

drop table if exists c_orders_status_info;
create table c_orders_status_info (
  `id` int(11) not null auto_increment,
  `order_status_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(32) not null ,
  `description` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('1', '1', 'en', 'Pending', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('2', '1', 'sv', 'Väntar', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('3', '2', 'en', 'Processing', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('4', '2', 'sv', 'Bearbetas', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('5', '3', 'en', 'Completed', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('6', '3', 'sv', 'Färdigbehandlad', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('7', '5', 'en', 'Cancelled', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('8', '5', 'sv', 'Makulerad', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('9', '4', 'en', 'Awaiting payment', '');

insert into c_orders_status_info (`id`, `order_status_id`, `language_code`, `name`, `description`) values ('10', '4', 'sv', 'Inväntar betalning', '');

drop table if exists c_orders_tax;
create table c_orders_tax (
  `id` int(11) not null auto_increment,
  `order_id` int(11) not null ,
  `tax_rate_id` int(11) not null ,
  `name` varchar(32) not null ,
  `tax` decimal(11,4) not null ,
  PRIMARY KEY (id)
);

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('35', '3', '5', 'SE VAT 25%', '20.0000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('27', '4', '5', 'SE VAT 25%', '45.0000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('52', '5', '5', 'SE VAT 25%', '400.0000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('30', '6', '5', 'SE VAT 25%', '12.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('32', '7', '5', 'SE VAT 25%', '32.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('34', '8', '5', 'SE VAT 25%', '360.0000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('37', '9', '5', 'SE VAT 25%', '100.0000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('55', '17', '5', 'SE VAT 25%', '106.2500');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('44', '12', '5', 'SE VAT 25%', '0.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('46', '13', '5', 'SE VAT 25%', '0.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('47', '14', '5', 'SE VAT 25%', '0.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('49', '15', '5', 'SE VAT 25%', '0.5000');

insert into c_orders_tax (`id`, `order_id`, `tax_rate_id`, `name`, `tax`) values ('51', '16', '5', 'SE VAT 25%', '0.5000');

drop table if exists c_orders_totals;
create table c_orders_totals (
  `id` int(11) not null auto_increment,
  `order_id` int(11) not null ,
  `code` varchar(32) not null ,
  `title` varchar(128) not null ,
  `value` float not null ,
  `tax` float not null ,
  `tax_class_id` int(11) not null ,
  `calculate` tinyint(1) not null ,
  `priority` tinyint(4) not null ,
  PRIMARY KEY (id)
);

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('18', '6', '', 'Cash On Delivery (Text Way)', '40', '', '1', '1', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('9', '3', '', 'Cash On Delivery (Text Way)', '40', '', '', '1', '3');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('7', '3', '', 'Delsumma', '80', '', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('8', '3', '', 'E-post företag (Kosta viktprocent)', '10', '', '', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('17', '6', '', 'Fraktbolag (Viktkostnad)', '10', '', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('16', '6', '', 'Delsumma', '80', '', '', '', '3');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('22', '7', '', 'Delsumma', '80', '20', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('19', '5', '', 'Pålägg', '80', '', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('20', '4', '', 'Frakt', '100', '', '1', '1', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('21', '5', '', 'Subtotal', '1520', '', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('23', '7', '', 'Fraktbolag (Viktkostnad)', '10', '2.5', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('24', '7', '', 'Cash On Delivery (Text Way)', '40', '10', '1', '1', '3');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('25', '8', '', 'Delsumma', '1280', '320', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('26', '8', '', 'Fast pris (Fast pris)', '120', '30', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('27', '8', '', 'Cash On Delivery (Text Way)', '40', '10', '1', '1', '3');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('28', '9', '', 'Delsumma', '240', '60', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('29', '9', '', 'Fast pris (Fast pris)', '120', '30', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('30', '9', '', 'Cash On Delivery (Text Way)', '40', '10', '1', '1', '3');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('39', '17', '', 'Invoice (Paper Invoice)', '25', '6.25', '1', '1', '2');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('38', '17', '', 'Delsumma', '400', '100', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('33', '12', '', 'Delsumma', '2', '0.5', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('34', '13', '', 'Delsumma', '2', '0.5', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('35', '14', '', 'Delsumma', '2', '0.5', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('36', '15', '', 'Delsumma', '2', '0.5', '', '', '1');

insert into c_orders_totals (`id`, `order_id`, `code`, `title`, `value`, `tax`, `tax_class_id`, `calculate`, `priority`) values ('37', '16', '', 'Delsumma', '2', '0.5', '', '', '1');

drop table if exists c_pages;
create table c_pages (
  `id` int(11) not null auto_increment,
  `dock_menu` tinyint(1) not null ,
  `dock_support` tinyint(1) not null ,
  `priority` int(11) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_pages (`id`, `dock_menu`, `dock_support`, `priority`, `date_updated`, `date_created`) values ('1', '1', '', '', '2012-08-09 19:57:46', '2012-08-09 16:54:20');

insert into c_pages (`id`, `dock_menu`, `dock_support`, `priority`, `date_updated`, `date_created`) values ('2', '1', '1', '', '2012-08-09 19:53:56', '2012-08-09 17:27:01');

insert into c_pages (`id`, `dock_menu`, `dock_support`, `priority`, `date_updated`, `date_created`) values ('3', '', '1', '', '2012-08-09 19:53:38', '2012-08-09 19:53:38');

insert into c_pages (`id`, `dock_menu`, `dock_support`, `priority`, `date_updated`, `date_created`) values ('4', '', '1', '', '2012-08-09 19:58:10', '2012-08-09 19:58:10');

insert into c_pages (`id`, `dock_menu`, `dock_support`, `priority`, `date_updated`, `date_created`) values ('5', '', '1', '', '2012-08-09 19:58:37', '2012-08-09 19:58:37');

drop table if exists c_pages_info;
create table c_pages_info (
  `id` int(11) not null auto_increment,
  `page_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `title` varchar(256) not null ,
  `content` text not null ,
  `head_title` varchar(128) not null ,
  `meta_description` varchar(256) not null ,
  `meta_keywords` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('1', '1', 'en', 'Test page', '
	Lorem ipsum dolor sit amet, dionysiade in modo. Spero mihi Tyrum in rei civibus laude clamaverunt donavit erat bene nostrae iam custodio vocem orbem Africus hortamento laetus moritur. Defunctam ut diem obiecti ad per animum est amet constanter approximavit te sed quod non coepit. Inde valuit argentum mense materia puella eius ad suis est cum obiectum est Apollonius. Nuptiarum condono hunc tamen adnuente rediens eam sed eu fides Concordi fabricata ait.
', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('2', '1', 'sv', 'Testsida', '
	Lorem ipsum dolor sit amet, dionysiade in modo. Spero mihi Tyrum in rei civibus laude clamaverunt donavit erat bene nostrae iam custodio vocem orbem Africus hortamento laetus moritur. Defunctam ut diem obiecti ad per animum est amet constanter approximavit te sed quod non coepit. Inde valuit argentum mense materia puella eius ad suis est cum obiectum est Apollonius. Nuptiarum condono hunc tamen adnuente rediens eam sed eu fides Concordi fabricata ait.
', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('3', '2', 'en', 'Conditions', '
	Lorem ipsum dolor sit amet, dionysiade in modo. Spero mihi Tyrum in rei civibus laude clamaverunt donavit erat bene nostrae iam custodio vocem orbem Africus hortamento laetus moritur. Defunctam ut diem obiecti ad per animum est amet constanter approximavit te sed quod non coepit. Inde valuit argentum mense materia puella eius ad suis est cum obiectum est Apollonius. Nuptiarum condono hunc tamen adnuente rediens eam sed eu fides Concordi fabricata ait.
', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('4', '2', 'sv', 'Köpvillkor', '
	Lorem ipsum dolor sit amet, dionysiade in modo. Spero mihi Tyrum in rei civibus laude clamaverunt donavit erat bene nostrae iam custodio vocem orbem Africus hortamento laetus moritur. Defunctam ut diem obiecti ad per animum est amet constanter approximavit te sed quod non coepit. Inde valuit argentum mense materia puella eius ad suis est cum obiectum est Apollonius. Nuptiarum condono hunc tamen adnuente rediens eam sed eu fides Concordi fabricata ait.
', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('5', '3', 'en', 'Shipping & returns', '', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('6', '3', 'sv', 'Frakt & returer', '', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('7', '4', 'en', 'About', '', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('8', '4', 'sv', 'Om oss', '', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('9', '5', 'en', 'Security', '', '', '', '');

insert into c_pages_info (`id`, `page_id`, `language_code`, `title`, `content`, `head_title`, `meta_description`, `meta_keywords`) values ('10', '5', 'sv', 'Säkerhet', '', '', '', '');

drop table if exists c_product_configuration_groups;
create table c_product_configuration_groups (
  `id` int(11) not null auto_increment,
  `function` varchar(32) not null ,
  `required` tinyint(1) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_product_configuration_groups (`id`, `function`, `required`, `date_updated`, `date_created`) values ('7', '', '1', '0000-00-00 00:00:00', '2012-08-14 03:44:39');

drop table if exists c_product_configuration_groups_info;
create table c_product_configuration_groups_info (
  `id` int(11) not null auto_increment,
  `group_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_configuration_groups_info (`id`, `group_id`, `language_code`, `name`) values ('6', '7', 'en', 'Frame');

insert into c_product_configuration_groups_info (`id`, `group_id`, `language_code`, `name`) values ('7', '7', 'sv', 'Ram');

drop table if exists c_product_configuration_values;
create table c_product_configuration_values (
  `id` int(11) not null auto_increment,
  `group_id` int(11) not null ,
  PRIMARY KEY (id)
);

insert into c_product_configuration_values (`id`, `group_id`) values ('1', '5');

insert into c_product_configuration_values (`id`, `group_id`) values ('2', '6');

insert into c_product_configuration_values (`id`, `group_id`) values ('3', '7');

insert into c_product_configuration_values (`id`, `group_id`) values ('4', '7');

drop table if exists c_product_configuration_values_info;
create table c_product_configuration_values_info (
  `id` int(11) not null auto_increment,
  `value_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_configuration_values_info (`id`, `value_id`, `language_code`, `name`) values ('1', '3', 'en', 'Antique');

insert into c_product_configuration_values_info (`id`, `value_id`, `language_code`, `name`) values ('2', '3', 'sv', 'Antik');

insert into c_product_configuration_values_info (`id`, `value_id`, `language_code`, `name`) values ('3', '4', 'en', 'Modern');

insert into c_product_configuration_values_info (`id`, `value_id`, `language_code`, `name`) values ('4', '4', 'sv', 'Modern');

drop table if exists c_product_groups;
create table c_product_groups (
  `id` int(11) not null auto_increment,
  `status` tinyint(4) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_product_groups (`id`, `status`, `date_updated`, `date_created`) values ('1', '', '2012-08-08 09:36:52', '2012-07-23 08:04:31');

insert into c_product_groups (`id`, `status`, `date_updated`, `date_created`) values ('2', '', '2012-08-08 09:37:49', '2012-07-23 08:04:41');

drop table if exists c_product_groups_info;
create table c_product_groups_info (
  `id` int(11) not null auto_increment,
  `product_group_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_groups_info (`id`, `product_group_id`, `language_code`, `name`) values ('1', '1', 'en', 'Gender');

insert into c_product_groups_info (`id`, `product_group_id`, `language_code`, `name`) values ('2', '1', 'sv', 'Kön');

insert into c_product_groups_info (`id`, `product_group_id`, `language_code`, `name`) values ('3', '2', 'en', 'Material');

insert into c_product_groups_info (`id`, `product_group_id`, `language_code`, `name`) values ('4', '2', 'sv', 'Material');

drop table if exists c_product_groups_values;
create table c_product_groups_values (
  `id` int(11) not null auto_increment,
  `product_group_id` int(11) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_product_groups_values (`id`, `product_group_id`, `date_updated`, `date_created`) values ('2', '1', '2012-08-08 09:36:52', '2012-08-08 09:36:40');

insert into c_product_groups_values (`id`, `product_group_id`, `date_updated`, `date_created`) values ('3', '1', '2012-08-08 09:36:52', '2012-08-08 09:36:40');

insert into c_product_groups_values (`id`, `product_group_id`, `date_updated`, `date_created`) values ('4', '1', '2012-08-08 09:36:52', '2012-08-08 09:36:40');

insert into c_product_groups_values (`id`, `product_group_id`, `date_updated`, `date_created`) values ('5', '2', '2012-08-08 09:37:49', '2012-08-08 09:37:49');

insert into c_product_groups_values (`id`, `product_group_id`, `date_updated`, `date_created`) values ('6', '2', '2012-08-08 09:37:49', '2012-08-08 09:37:49');

drop table if exists c_product_groups_values_info;
create table c_product_groups_values_info (
  `id` int(11) not null auto_increment,
  `product_group_value_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('1', '2', 'en', 'Female');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('2', '2', 'sv', 'Kvinna');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('3', '3', 'en', 'Male');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('4', '3', 'sv', 'Man');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('5', '4', 'en', 'Unisex');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('6', '4', 'sv', 'Unisex');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('7', '5', 'en', 'Plastic');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('8', '5', 'sv', 'Plast');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('9', '6', 'en', 'Silicone');

insert into c_product_groups_values_info (`id`, `product_group_value_id`, `language_code`, `name`) values ('10', '6', 'sv', 'Silikon');

drop table if exists c_product_option_groups;
create table c_product_option_groups (
  `id` int(11) not null auto_increment,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_product_option_groups (`id`, `date_updated`, `date_created`) values ('8', '2012-08-03 03:31:55', '2012-07-15 20:41:01');

insert into c_product_option_groups (`id`, `date_updated`, `date_created`) values ('9', '2012-08-06 02:30:30', '2012-07-17 03:23:05');

drop table if exists c_product_option_groups_info;
create table c_product_option_groups_info (
  `id` int(11) not null auto_increment,
  `group_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_option_groups_info (`id`, `group_id`, `language_code`, `name`) values ('8', '8', 'sv', 'Färg');

insert into c_product_option_groups_info (`id`, `group_id`, `language_code`, `name`) values ('7', '8', 'en', 'Color');

insert into c_product_option_groups_info (`id`, `group_id`, `language_code`, `name`) values ('9', '9', 'en', 'Size');

insert into c_product_option_groups_info (`id`, `group_id`, `language_code`, `name`) values ('10', '9', 'sv', 'Storlek');

drop table if exists c_product_option_values;
create table c_product_option_values (
  `id` int(11) not null auto_increment,
  `group_id` int(11) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('1', '6', '2012-07-15 20:36:40', '2012-07-15 20:36:40');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('2', '6', '2012-07-15 20:36:40', '2012-07-15 20:36:40');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('3', '7', '2012-07-15 20:38:15', '2012-07-15 20:38:15');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('4', '8', '2012-08-03 03:31:55', '2012-07-15 20:41:01');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('5', '9', '2012-08-06 02:30:30', '2012-07-17 03:23:05');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('6', '9', '2012-08-06 02:30:30', '2012-07-17 03:23:05');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('7', '9', '2012-08-06 02:30:30', '2012-07-17 03:23:05');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('8', '8', '2012-08-03 03:31:55', '2012-07-17 03:23:39');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('9', '8', '2012-08-03 03:31:55', '2012-07-17 03:23:39');

insert into c_product_option_values (`id`, `group_id`, `date_updated`, `date_created`) values ('10', '8', '2012-08-03 03:31:55', '2012-07-17 03:23:39');

drop table if exists c_product_option_values_info;
create table c_product_option_values_info (
  `id` int(11) not null auto_increment,
  `value_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(64) not null ,
  PRIMARY KEY (id)
);

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('41', '7', 'en', 'L');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('10', '5', 'sv', 'S');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('12', '6', 'sv', 'M');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('14', '7', 'sv', 'L');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('17', '8', 'en', 'Blue');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('21', '10', 'en', 'Yellow');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('31', '4', 'en', 'Red');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('32', '4', 'sv', 'Röd');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('34', '8', 'sv', 'Blå');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('35', '9', 'en', 'Green');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('36', '9', 'sv', 'Grön');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('40', '6', 'en', 'M');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('39', '5', 'en', 'S');

insert into c_product_option_values_info (`id`, `value_id`, `language_code`, `name`) values ('38', '10', 'sv', 'Gul');

drop table if exists c_products;
create table c_products (
  `id` int(11) not null auto_increment,
  `status` tinyint(1) not null ,
  `manufacturer_id` int(11) not null ,
  `supplier_id` int(11) not null ,
  `delivery_status_id` int(11) not null ,
  `sold_out_status_id` int(11) not null ,
  `categories` varchar(64) not null ,
  `product_groups` varchar(32) not null ,
  `model` varchar(64) not null ,
  `sku` varchar(64) not null ,
  `upc` varchar(12) not null ,
  `taric` varchar(16) not null ,
  `quantity` int(11) not null ,
  `weight` decimal(10,4) not null ,
  `weight_class` varchar(2) not null ,
  `dim_x` decimal(10,4) not null ,
  `dim_y` decimal(10,4) not null ,
  `dim_z` decimal(10,4) not null ,
  `dim_class` varchar(2) not null ,
  `purchase_price` decimal(10,4) not null ,
  `price` decimal(10,4) not null ,
  `specials_price` decimal(10,4) not null ,
  `specials_expire` datetime not null ,
  `tax_class_id` int(11) not null ,
  `image` varchar(64) not null ,
  `views` int(11) not null ,
  `purchases` int(11) not null ,
  `date_available` datetime not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id),
  KEY status (status)
);

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('25', '1', '1', '', '2', '2', '9', '', '', '', '', '', '', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '400.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/25-24562_badhanddukar.jpg', '86', '2', '0000-00-00 00:00:00', '2012-08-08 06:12:35', '2012-07-12 17:49:06');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('24', '1', '1', '', '', '', '9', '', '', '', '', '', '100', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/24-53810_fotbollsanka.jpg', '15', '', '0000-00-00 00:00:00', '2012-07-18 03:45:37', '2012-07-12 17:47:15');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('23', '1', '1', '', '', '', '9', '', '', '', '', '', '81', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '400.0000', '0.0000', '2069-12-31 16:00:00', '1', 'products/23-1417_badhandukar.jpg', '26', '5', '0000-00-00 00:00:00', '2012-07-18 03:45:29', '2012-07-12 17:46:19');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('22', '1', '1', '', '', '', '9', '', '', '', '', '', '100', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/22-39090_bla-anka.jpg', '24', '', '0000-00-00 00:00:00', '2012-07-18 03:45:33', '2012-07-12 17:45:27');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('21', '1', '1', '', '', '', '9,16', '', '', '', '', '', '84', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/21-98215_ankfamilj.jpg', '59', '11', '0000-00-00 00:00:00', '2012-08-09 20:06:49', '2012-07-11 21:59:59');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('20', '1', '1', '', '', '', '9', '', '', '', '', '', '99', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '25.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/20-99965_basketboll-anka.jpg', '8', '1', '0000-00-00 00:00:00', '2012-07-18 03:45:31', '2012-07-10 16:54:08');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('26', '1', '1', '', '', '', '9', '', '', '', '', '', '98', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '2069-12-31 16:00:00', '1', 'products/26-37305_cool-anka.jpg', '53', '1', '0000-00-00 00:00:00', '2012-07-18 03:45:35', '2012-07-12 17:50:59');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('19', '1', '1', '1', '', '', '9,1', '2-5', '123456', '', '', '', '13', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '25.0000', '80.0000', '50.0000', '2069-12-31 16:00:00', '1', 'products/19-52467_gummianka.jpg', '161', '2', '0000-00-00 00:00:00', '2012-08-20 18:45:39', '2012-07-10 16:03:57');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('27', '1', '1', '1', '1', '1', '9', '2-5', '', '', '', '', '1', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/27-green-duck-1.jpg', '155', '1', '0000-00-00 00:00:00', '2012-08-08 14:03:29', '2012-07-19 05:17:47');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('28', '1', '1', '1', '', '', '9', '', '', '', '', '', '99', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/28-brollopsankor-1.jpg', '7', '1', '0000-00-00 00:00:00', '2012-07-23 09:22:03', '2012-07-23 09:15:24');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('29', '1', '1', '1', '', '', '9', '2-5', '', '', '', '', '96', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/29-rosa-anka-4.jpg', '79', '5', '0000-00-00 00:00:00', '2012-08-08 14:03:57', '2012-07-23 09:21:22');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('30', '1', '1', '1', '', '', '9', '1', '', '', '', '', '79', '1.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '80.0000', '0.0000', '1969-12-31 16:00:00', '1', 'products/30-prickig-anka-1.jpg', '152', '10', '0000-00-00 00:00:00', '2012-07-23 09:26:24', '2012-07-23 09:23:51');

insert into c_products (`id`, `status`, `manufacturer_id`, `supplier_id`, `delivery_status_id`, `sold_out_status_id`, `categories`, `product_groups`, `model`, `sku`, `upc`, `taric`, `quantity`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `purchase_price`, `price`, `specials_price`, `specials_expire`, `tax_class_id`, `image`, `views`, `purchases`, `date_available`, `date_updated`, `date_created`) values ('31', '1', '1', '1', '', '', '9,1', '', '12345', '', '', '', '86', '0.0000', 'kg', '0.0000', '0.0000', '0.0000', 'cm', '0.0000', '2.0000', '0.0000', '1970-01-01 01:00:00', '1', '', '17', '6', '0000-00-00 00:00:00', '2012-08-20 18:45:39', '2012-08-15 02:21:02');

drop table if exists c_products_configurations;
create table c_products_configurations (
  `id` int(11) not null auto_increment,
  `product_id` int(11) not null ,
  `combination` varchar(64) not null ,
  PRIMARY KEY (id)
);

drop table if exists c_products_images;
create table c_products_images (
  `id` int(11) not null auto_increment,
  `product_id` int(11) not null ,
  `filename` varchar(64) not null ,
  `priority` tinyint(2) not null ,
  PRIMARY KEY (id)
);

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('58', '12', 'products/12-64562_produkt-4.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('57', '11', 'products/11-66275_produkt-3.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('56', '10', 'products/10-26450_produkt-2.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('49', '1', 'products/1_49-produkt-1.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('50', '1', 'products/1_50-produkt-1.jpg', '2');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('52', '3', 'products/3_52-produkt-2.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('53', '3', 'products/3_53-produkt-2.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('54', '3', 'products/3_54-produkt-2.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('55', '9', 'products/9-64037_produkt-1.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('27', '2', 'products/2_27-testproduct.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('60', '19', 'products/19-52467_gummianka.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('59', '13', 'products/13-16816_produkt-5.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('61', '20', 'products/20-99965_basketboll-anka.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('62', '21', 'products/21-98215_ankfamilj.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('63', '22', 'products/22-39090_bla-anka.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('64', '23', 'products/23-1417_badhandukar.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('65', '24', 'products/24-53810_fotbollsanka.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('66', '25', 'products/25-24562_badhanddukar.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('67', '26', 'products/26-37305_cool-anka.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('72', '27', 'products/27-green-duck-1.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('73', '28', 'products/28-brollopsankor-1.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('75', '30', 'products/30-prickig-anka-1.jpg', '');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('84', '29', 'products/29-rosa-anka-3.jpg', '2');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('83', '29', 'products/29-rosa-anka-4.jpg', '1');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('85', '29', 'products/29-rosa-anka-5.jpg', '3');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('86', '29', 'products/29-rosa-anka-6.jpg', '4');

insert into c_products_images (`id`, `product_id`, `filename`, `priority`) values ('87', '29', 'products/29-rosa-anka-7.jpg', '5');

drop table if exists c_products_info;
create table c_products_info (
  `id` int(11) not null auto_increment,
  `product_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(128) not null ,
  `short_description` varchar(256) not null ,
  `description` text not null ,
  `keywords` varchar(255) not null ,
  `head_title` varchar(128) not null ,
  `meta_description` varchar(256) not null ,
  `meta_keywords` varchar(256) not null ,
  `attributes` text not null ,
  PRIMARY KEY (id)
);

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('34', '11', 'sv', 'Produkt 3', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('35', '11', 'en', 'Product 3', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('36', '12', 'sv', 'Produkt 4', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('37', '12', 'en', 'Product 4', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('42', '15', 'sv', 'Plastpåse', '', '', '', '', '', '', 'This: that
Other: that');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('38', '13', 'sv', 'Produkt 5', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('39', '13', 'en', 'Product 5', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('40', '14', 'sv', 'Test', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('41', '14', 'en', 'Test', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('43', '15', 'en', 'Name', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('44', '15', 'da', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('45', '15', 'de', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('46', '9', 'da', '1', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('47', '9', 'de', '2', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('30', '9', 'sv', 'Produkt 1', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('31', '9', 'en', 'Product 1', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('32', '10', 'sv', 'Produkt 2', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('33', '10', 'en', 'Product 2', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('81', '30', 'sv', 'Prickig anka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('80', '30', 'en', 'Polkadot Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('79', '29', 'sv', 'Rosa anka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('62', '21', 'en', 'Duck Family', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('63', '21', 'sv', 'Ankfamilj', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('64', '22', 'en', 'Blue Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('65', '22', 'sv', 'Blå anka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('66', '23', 'en', 'Towel Set', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('67', '23', 'sv', 'Badhandukar', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('68', '24', 'en', 'Football Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('69', '24', 'sv', 'Fotbollsanka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('70', '25', 'en', 'Towel Set', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('71', '25', 'sv', 'Badhanddukar', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('72', '26', 'en', 'Cool Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('73', '26', 'sv', 'Cool anka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('74', '27', 'en', 'Green Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('75', '27', 'sv', 'Green Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('76', '28', 'en', 'Wedding Ducks', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('77', '28', 'sv', 'Bröllopsankor', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('78', '29', 'en', 'Pink Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('48', '18', 'da', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('49', '18', 'de', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('50', '18', 'en', 'Rubber Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('51', '18', 'sv', 'Gummianka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('52', '19', 'da', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('53', '19', 'de', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('54', '19', 'en', 'Rubber Duck', '', '<p>
  Lorem ipsum dolor sit amet, puellae capite introivit filiam sum cum autem Apolloni sed. Stranguillio cum obiectum dixit regem ut sua. Iusto opes mihi quidditas tuo curavit quo alacres ad te sed esse ait. Christus eum est cum autem Apolloni sed. Cum autem Apolloni figitur acquievit sed.</p>
<p>
  Lorem ipsum dolor sit amet, puellae capite introivit filiam sum cum autem Apolloni sed. Stranguillio cum obiectum dixit regem ut sua. Iusto opes mihi quidditas tuo curavit quo alacres ad te sed esse ait. Christus eum est cum autem Apolloni sed. Cum autem Apolloni figitur acquievit sed.</p>', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('55', '19', 'sv', 'Gummianka', '', 'rn	Lorem ipsum dolor sit amet, puellae capite introivit filiam sum cum autem Apolloni sed. Stranguillio cum obiectum dixit regem ut sua. Iusto opes mihi quidditas tuo curavit quo alacres ad te sed esse ait. Christus eum est cum autem Apolloni sed. Cum autem Apolloni figitur acquievit sed.rnrn Lorem ipsum dolor sit amet, puellae capite introivit filiam sum cum autem Apolloni sed. Stranguillio cum obiectum dixit regem ut sua. Iusto opes mihi quidditas tuo curavit quo alacres ad te sed esse ait. Christus eum est cum autem Apolloni sed. Cum autem Apolloni figitur acquievit sed.', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('56', '16', 'da', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('57', '16', 'de', '', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('58', '16', 'en', 'Rubber Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('59', '16', 'sv', 'Gummianka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('60', '20', 'en', 'Basket Ball Duck', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('61', '20', 'sv', 'Basketboll anka', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('82', '31', 'en', 'Paypal Test Product', '', '', '', '', '', '', '');

insert into c_products_info (`id`, `product_id`, `language_code`, `name`, `short_description`, `description`, `keywords`, `head_title`, `meta_description`, `meta_keywords`, `attributes`) values ('83', '31', 'sv', 'Paypal testprodukt åäö', '', '', '', '', '', '', '');

drop table if exists c_products_options;
create table c_products_options (
  `id` int(11) not null auto_increment,
  `product_id` int(11) not null ,
  `combination` varchar(64) not null ,
  `sku` varchar(64) not null ,
  `price_operator` varchar(16) not null ,
  `price` int(11) not null ,
  `weight` int(11) not null ,
  `weight_class` varchar(2) not null ,
  `dim_x` decimal(11,4) not null ,
  `dim_y` decimal(11,4) not null ,
  `dim_z` decimal(11,4) not null ,
  `dim_class` varchar(2) not null ,
  `quantity` int(11) not null ,
  `priority` tinyint(4) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('7', '13', '2-5', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-06-24 01:15:28', '2012-06-24 01:15:28');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('2', '14', '2-7', '', '', '9', '8', 'g', '7.0000', '6.0000', '5.0000', 'cm', '10', '', '2012-06-23 11:27:14', '2012-06-23 10:27:39');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('8', '13', '2-6', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-06-24 01:15:28', '2012-06-24 01:15:28');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('5', '14', '2-8', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-06-23 11:27:14', '2012-06-23 11:27:14');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('9', '13', '2-7', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-06-24 01:15:28', '2012-06-24 01:15:28');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('10', '9', '2-5', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-07-05 22:56:19', '2012-06-24 01:59:39');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('11', '9', '2-6', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '1', '2012-07-05 22:56:19', '2012-06-24 01:59:39');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('12', '9', '2-7', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '2', '2012-07-05 22:56:19', '2012-06-24 01:59:39');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('18', '10', '1-1,2-6', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '1', '2012-06-24 02:19:12', '2012-06-24 02:17:35');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('17', '10', '1-3,2-7', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-06-24 02:19:12', '2012-06-24 02:17:35');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('19', '15', '1-3,2-5', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '99', '', '2012-07-10 21:46:59', '2012-06-26 13:04:59');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('20', '15', '1-3,2-6', '', '', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '60', '1', '2012-07-10 21:46:59', '2012-06-26 13:04:59');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('27', '19', '8-8', '', '=', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '', '2012-08-20 18:45:40', '2012-08-03 03:12:38');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('28', '19', '8-9', '', '=', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '1', '2012-08-20 18:45:40', '2012-08-03 03:12:38');

insert into c_products_options (`id`, `product_id`, `combination`, `sku`, `price_operator`, `price`, `weight`, `weight_class`, `dim_x`, `dim_y`, `dim_z`, `dim_class`, `quantity`, `priority`, `date_updated`, `date_created`) values ('29', '19', '8-4', '', '=', '', '', 'kg', '0.0000', '0.0000', '0.0000', 'mm', '', '2', '2012-08-20 18:45:40', '2012-08-03 03:12:39');

drop table if exists c_seo_links_cache;
create table c_seo_links_cache (
  `uri` varchar(256) not null ,
  `seo_uri` varchar(256) not null ,
  `language_code` varchar(2) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  KEY seo_uri (seo_uri)
);

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('manufacturer.php?manufacturer_id=1', 'sv/acme-corporation-m-1', 'sv', '2012-08-18 06:35:29', '2012-08-13 06:06:43');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=20', 'sv/basketboll-anka-p-20', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:06:43');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=23', 'sv/badhandukar-p-23', 'sv', '2012-08-20 18:18:35', '2012-08-13 06:06:43');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=21', 'sv/ankfamilj-p-21', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:06:43');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=28', 'sv/brollopsankor-p-28', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:06:44');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=30', 'sv/prickig-anka-p-30', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:06:44');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=29', 'sv/rosa-anka-p-29', 'sv', '2012-08-20 18:18:35', '2012-08-13 06:06:44');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=27', 'sv/green-duck-p-27', 'sv', '2012-08-20 18:18:35', '2012-08-13 06:06:44');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=19', 'sv/gummianka-p-19', 'sv', '2012-08-20 18:18:35', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=2', 'sv/kopvillkor-d-2', 'sv', '2012-08-13 06:06:45', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=1', 'sv/testsida-d-1', 'sv', '2012-08-13 06:06:45', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=9', 'sv/gummiankor-c-9', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=15', 'sv/lorem-ipsum-dolor-sit-amet-c-15', 'sv', '2012-08-21 06:38:57', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=16', 'sv/3-e-niva-kategori-c-16', 'sv', '2012-08-20 10:21:47', '2012-08-13 06:06:45');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=17', 'sv/underkategori-2-c-17', 'sv', '2012-08-20 09:05:51', '2012-08-13 06:06:46');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=1', 'sv/kategori-a-c-1', 'sv', '2012-08-20 10:03:31', '2012-08-13 06:06:46');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=26', 'sv/cool-anka-p-26', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:07:03');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=25', 'sv/badhanddukar-p-25', 'sv', '2012-08-20 18:18:35', '2012-08-13 06:07:03');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=24', 'sv/fotbollsanka-p-24', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:07:03');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=22', 'sv/bla-anka-p-22', 'sv', '2012-08-20 17:47:42', '2012-08-13 06:07:04');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=3', 'sv/frakt-returer-d-3', 'sv', '2012-08-13 06:32:52', '2012-08-13 06:32:52');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=4', 'sv/om-oss-d-4', 'sv', '2012-08-13 06:32:52', '2012-08-13 06:32:52');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=5', 'sv/sakerhet-d-5', 'sv', '2012-08-13 06:32:52', '2012-08-13 06:32:52');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=31', 'sv/paypal-testprodukt-aao-p-31', 'sv', '2012-08-20 17:47:42', '2012-08-15 02:21:49');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('manufacturer.php?manufacturer_id=1', 'en/acme-corporation-m-1', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=31', 'en/paypal-test-product-p-31', 'en', '2012-08-15 23:52:29', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=23', 'en/towel-set-p-23', 'en', '2012-08-15 23:52:30', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=21', 'en/duck-family-p-21', 'en', '2012-08-15 23:52:30', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=20', 'en/basket-ball-duck-p-20', 'en', '2012-08-15 23:52:30', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=30', 'en/polkadot-duck-p-30', 'en', '2012-08-15 23:52:29', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=29', 'en/pink-duck-p-29', 'en', '2012-08-15 23:52:29', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=28', 'en/wedding-ducks-p-28', 'en', '2012-08-15 23:52:29', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=19', 'en/rubber-duck-p-19', 'en', '2012-08-15 23:52:30', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=2', 'en/conditions-d-2', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('page.php?page_id=1', 'en/test-page-d-1', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=1', 'en/category-a-c-1', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=9', 'en/rubber-ducks-c-9', 'en', '2012-08-15 23:52:29', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=15', 'en/lorem-ipsum-dolor-sit-amet-c-15', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=16', 'en/3rd-level-category-c-16', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('category.php?category_id=17', 'en/subcategory-2-c-17', 'en', '2012-08-15 23:50:25', '2012-08-15 23:50:25');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=26', 'en/cool-duck-p-26', 'en', '2012-08-15 23:52:30', '2012-08-15 23:52:06');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=27', 'en/green-duck-p-27', 'en', '2012-08-15 23:52:29', '2012-08-15 23:52:29');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=25', 'en/towel-set-p-25', 'en', '2012-08-15 23:52:30', '2012-08-15 23:52:30');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=24', 'en/football-duck-p-24', 'en', '2012-08-15 23:52:30', '2012-08-15 23:52:30');

insert into c_seo_links_cache (`uri`, `seo_uri`, `language_code`, `date_updated`, `date_created`) values ('product.php?product_id=22', 'en/blue-duck-p-22', 'en', '2012-08-15 23:52:42', '2012-08-15 23:52:30');

drop table if exists c_settings;
create table c_settings (
  `id` int(11) not null auto_increment,
  `setting_group_key` varchar(64) not null ,
  `type` enum('global','local') default 'local' not null ,
  `title` varchar(128) not null ,
  `description` varchar(512) not null ,
  `key` varchar(64) not null ,
  `value` varchar(512) not null ,
  `function` varchar(128) not null ,
  `priority` int(11) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id),
  KEY key (key)
);

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('9', 'store_info', 'global', 'Store Name', 'The name of your store.', 'store_name', 'My Store', 'input()', '10', '2012-04-01 19:06:42', '2012-04-01 19:06:42');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('2', 'store_info', 'global', 'Store Language', 'The store language.', 'store_language', 'sv', 'languages()', '30', '2012-04-21 06:54:22', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('3', 'defaults', 'global', 'Default Language', 'The default language selected, if failed to identify.', 'default_language', 'sv', 'languages()', '10', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('5', 'defaults', 'global', 'Default Currency', 'The default currency selected.', 'default_currency', 'SEK', 'currencies()', '11', '2012-03-31 15:06:27', '2012-03-31 15:06:27');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('6', 'store_info', 'global', 'Store Country', 'The country of your store.', 'store_country_id', 'SE', 'countries()', '13', '2012-08-09 19:05:28', '2012-04-01 17:20:47');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('7', 'store_info', 'global', 'Store Zone', 'The zone of your store.', 'store_zone', 'VML', 'zones()', '14', '2012-08-09 19:05:38', '2012-04-01 18:51:49');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('8', 'listings', 'global', 'Display Prices Including Tax', 'Displays all prices in catalog including tax.', 'display_prices_including_tax', 'true', 'toggle()', '', '2012-08-07 01:12:46', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('10', 'store_info', 'global', 'System Weight Class', 'The preselected weight class.', 'store_weight_class', 'kg', 'weight_classes()', '32', '2012-04-06 19:20:21', '2012-04-06 19:20:21');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('11', 'store_info', 'global', 'System Length Class', 'The preselected length class.', 'store_length_class', 'cm', 'length_classes()', '33', '2012-04-15 23:43:54', '2012-04-06 19:20:21');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('12', '', 'local', 'Installed Shipping Modules', '', 'shipping_modules', 'sm_flat_rate;sm_free;sm_weight_table', '', '', '2012-04-13 16:10:57', '2012-04-13 16:10:57');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('13', '', 'local', 'Installed Payment Modules', '', 'payment_modules', 'pm_cod;pm_invoice;pm_paypal', '', '', '2012-04-13 16:10:57', '2012-04-13 16:10:57');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('14', 'listings', 'local', 'Data Table Rows', 'The number of data table rows per page.', 'data_table_rows_per_page', '20', 'input()', '', '2012-04-16 00:01:03', '2012-04-15 06:48:04');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('59', '', 'local', '', '', 'shipping_module_sm_weight_table', 'a:7:{s:6:\"status\";s:7:\"Enabled\";s:4:\"icon\";s:0:\"\";s:10:\"rate_table\";s:15:\"5:8.95;10:15.95\";s:12:\"weight_class\";s:2:\"kg\";s:12:\"tax_class_id\";s:1:\"1\";s:11:\"geo_zone_id\";s:0:\"\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-06 03:38:52');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('62', 'listings', 'local', 'Display Stock Count', 'Show the available amounts of products in stock.', 'display_stock_count', 'true', 'toggle()', '', '2012-08-08 05:54:31', '2012-08-08 05:54:31');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('17', '', 'local', '', '', 'order_total_modules', 'ot_subtotal;ot_payment_fee;ot_shipping_fee', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('19', 'defaults', 'global', 'Default Country', 'The default country selected if not set otherwise.', 'default_country', 'SE', 'countries()', '12', '2012-04-28 11:31:20', '2012-04-28 11:31:20');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('20', 'defaults', 'global', 'Default Zone', 'The default zone selected if not set otherwise.', 'default_zone', '', 'zones()', '13', '2012-08-09 18:59:55', '2012-04-30 15:47:19');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('40', 'store_info', 'global', 'Store Currency', 'The currency of which all prices conform to.', 'store_currency', 'SEK', 'currencies()', '31', '2012-07-02 15:00:01', '2012-07-02 15:00:01');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('27', '', 'local', '', '', 'order_total_module_ot_subtotal', 'a:1:{s:10:\"sort_order\";s:1:\"1\";}', '', '', '0000-00-00 00:00:00', '2012-05-06 20:24:58');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('82', 'store_info', 'global', 'Store Time Zone', 'The store time zone.', 'store_timezone', 'Europe/Stockholm', 'timezones()', '40', '2012-08-17 19:36:30', '2012-08-17 19:36:30');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('68', 'advanced', 'global', 'Jobs Last Run', 'Time when background jobs where last executed.', 'jobs_last_run', '2012-08-21 18:34:56', 'input()', '41', '2012-08-16 08:29:24', '2012-08-11 21:00:34');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('60', 'listings', 'local', 'Cheapest Shipping', 'Display the cheapest shipping cost on product page.', 'display_cheapest_shipping', 'true', 'toggle()', '', '2012-08-06 04:58:08', '2012-08-06 04:58:08');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('58', '', 'local', '', '', 'shipping_module_sm_free', 'a:5:{s:6:\"status\";s:7:\"Enabled\";s:4:\"icon\";s:0:\"\";s:14:\"minimum_amount\";s:1:\"0\";s:9:\"geo_zones\";s:1:\"3\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-06 03:38:04');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('28', '', 'local', '', '', 'order_total_module_ot_payment_fee', 'a:1:{s:10:\"sort_order\";s:2:\"10\";}', '', '', '0000-00-00 00:00:00', '2012-05-06 20:25:32');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('29', '', 'local', '', '', 'order_total_module_ot_shipping_fee', 'a:1:{s:10:\"sort_order\";s:2:\"10\";}', '', '', '0000-00-00 00:00:00', '2012-05-06 20:25:43');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('57', '', 'local', '', '', 'shipping_module_sm_flat_rate', 'a:6:{s:6:\"status\";s:7:\"Enabled\";s:4:\"icon\";s:0:\"\";s:4:\"cost\";s:6:\"120.00\";s:12:\"tax_class_id\";s:1:\"1\";s:12:\"geo_zones_id\";s:0:\"\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-06 02:59:27');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('32', 'store_info', 'global', 'Store Email', 'The store e-mail address.', 'store_email', 'store@tim-international.net', 'input()', '11', '2012-08-09 18:52:00', '2012-05-27 21:26:11');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('33', 'checkout', 'local', 'Password Field', 'Show the password field, letting customers set their own password. Otherwise randomly generated.', 'fields_customer_password', 'false', 'toggle()', '13', '2012-08-07 01:18:33', '2012-05-27 21:34:41');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('34', '', 'local', 'Date Cache Cleared', 'Do not use system cache older than breakpoint.', 'cache_system_breakpoint', '2012-08-21 18:20:48', 'input()', '', '2012-05-30 05:21:51', '2012-05-30 05:21:51');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('35', 'advanced', 'global', 'GZIP Enabled', 'Compresses browser data. Increases the load on the server but decreases the bandwidth.', 'gzip_enabled', 'false', 'toggle()', '60', '2012-08-09 09:10:51', '2012-05-30 22:11:18');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('36', 'advanced', 'global', 'System Cache Enabled', 'Enables the system cache module which caches frequently used data.', 'cache_enabled', 'true', 'toggle()', '50', '2012-08-13 05:21:24', '2012-05-30 22:27:30');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('37', 'default', 'global', 'Error Receipient', 'The e-mail address of which to recceive PHP error reports.', 'errors_receipient', 'debug@tim-international.net', 'input()', '', '2012-06-06 06:40:26', '2012-06-06 06:40:26');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('38', '', 'global', '', '', 'errors_last_reported', '2012-08-21 07:41:32', '', '', '2012-08-15 21:15:43', '2012-06-06 06:40:26');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('39', 'default', 'global', 'Errors Minimum Interval', 'Hours to wait before reporting errors again via e-mail.', 'errors_send_interval', '24', 'int()', '', '2012-06-06 06:43:02', '2012-06-06 06:43:02');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('69', 'advanced', 'local', 'Jobs Interval', 'The amount of minutes between each execution of jobs.', 'jobs_interval', '60', 'int()', '40', '2012-08-13 01:34:33', '2012-08-11 21:00:34');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('43', 'checkout', 'local', 'Register Guests', 'Automatically create accounts for all guests.', 'register_guests', 'false', 'toggle()', '12', '2012-08-20 08:31:22', '2012-07-05 19:29:32');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('61', 'advanced', 'local', 'Database Admin Link', 'The URL to your database manager i.e. phpMyAdmin.', 'database_admin_link', 'http://mysql.tim-international.net', 'input()', '30', '2012-08-08 04:56:57', '2012-08-08 04:56:22');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('45', 'checkout', 'local', 'Enable AJAX Checkout', 'Enables AJAX functionality for checkout.', 'checkout_ajax_enabled', 'true', 'toggle()', '10', '2012-07-09 20:01:47', '2012-07-09 20:01:47');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('46', 'store_info', 'local', 'Postal Address', 'The store postal address.', 'store_postal_address', 'My Store
Street
Postcode City
Country', 'bigtext()', '12', '2012-07-11 17:09:01', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('47', 'store_info', 'local', 'Tax ID', 'The store tax ID or VATIN.', 'store_tax_id', 'SE00000000-000001', 'input()', '16', '2012-07-11 17:13:17', '2012-07-11 17:12:46');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('48', 'store_info', 'local', 'Phone Number', 'The store phone number.', 'store_phone', '+46 8-123 45 67', 'input()', '15', '2012-07-11 17:29:28', '2012-07-11 17:29:28');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('49', '', 'local', 'Custom Box Data', '', 'box_custom_data', 'a:2:{s:5:\"title\";a:2:{s:2:\"en\";s:10:\"Custom Box\";s:2:\"sv\";s:12:\"Anpassad box\";}s:7:\"content\";a:2:{s:2:\"en\";s:24:\"<p>
	HTML content</p>
\";s:2:\"sv\";s:24:\"<p>
	HTML content</p>
\";}}', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('50', 'store_info', 'local', 'Keywords', 'The main keywords of the store. Mainly used for the first page.', 'store_keywords', 'litecart, web shop, e-commerce', 'input()', '21', '2012-08-09 19:06:49', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('51', 'store_info', 'local', 'Short Description', 'A short description of the store mainly used for the front page meta description.', 'store_short_description', 'This is a fancy web shop.', 'mediumtext()', '20', '2012-08-09 19:06:28', '0000-00-00 00:00:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('52', 'advanced', 'global', 'SEO Links Enabled', 'Enabling this requires .htaccess and mod_rewrite rules.', 'seo_links_enabled', 'true', 'toggle()', '10', '2012-07-25 22:01:27', '2012-07-25 22:01:27');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('53', 'checkout', 'local', 'Enable CAPTCHA', 'Lets customers enter a CAPTACHA code before placing orders.', 'checkout_captcha_enabled', 'true', 'toggle()', '11', '2012-08-09 19:32:29', '2012-07-27 19:49:46');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('54', 'checkout', 'local', 'Order Copy Receipients', 'Send order copies to the following e-mail addresses. Separate by semi-colons.', 'email_order_copy', 'shop@tim-international.net', 'mediumtext()', '14', '2012-08-09 18:54:52', '2012-07-27 21:19:46');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('55', '', 'local', '', '', 'order_success_modules', '', '', '', '2012-08-01 05:15:55', '2012-08-01 05:15:55');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('66', 'store_info', 'global', 'Template', '', 'template', 'default', 'templates()', '50', '2012-08-10 12:03:36', '2012-08-10 12:03:36');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('63', '', 'local', '', '', 'payment_module_pm_cod', 'a:4:{s:6:\"status\";s:7:\"Enabled\";s:15:\"order_status_id\";s:1:\"2\";s:11:\"geo_zone_id\";s:0:\"\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-08 14:54:33');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('64', '', 'local', '', '', 'payment_module_pm_invoice', 'a:5:{s:7:\"enabled\";s:4:\"True\";s:3:\"fee\";s:2:\"25\";s:12:\"tax_class_id\";s:1:\"1\";s:11:\"geo_zone_id\";s:0:\"\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-08 14:55:24');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('65', '', 'local', '', '', 'payment_module_pm_paypal', 'a:9:{s:6:\"status\";s:7:\"Enabled\";s:4:\"icon\";s:25:\"images/payment/paypal.png\";s:14:\"merchant_email\";s:26:\"info@tim-international.net\";s:14:\"pdt_auth_token\";s:59:\"uAYcMXL-YKeJNTFauSQtquXWNBPNO9pgCg6l1hD54XmzxhzvnPkQPzFEL7u\";s:7:\"gateway\";s:10:\"Production\";s:24:\"order_status_id_complete\";s:1:\"2\";s:21:\"order_status_id_error\";s:1:\"2\";s:11:\"geo_zone_id\";s:0:\"\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-08 14:56:00');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('70', '', 'local', '', '', 'jobs_modules', 'job_currency_updater;job_error_reporter;job_database_backup', '', '', '2012-08-11 21:05:10', '2012-08-11 21:05:10');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('79', '', 'local', '', '', 'jobs_module_job_currency_updater', 'a:3:{s:6:\"status\";s:7:\"Enabled\";s:16:\"update_frequency\";s:5:\"Daily\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-16 08:43:21');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('72', 'advanced', 'global', 'Clear SEO Links Cache', 'Remove all cached SEO links from database.', 'cache_clear_seo_links', 'false', 'toggle()', '11', '2012-08-13 05:07:26', '2012-08-12 17:01:17');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('73', 'advanced', 'global', 'Clear Thumbnails Cache', 'Remove all cached image thumnbnails from disk.', 'cache_clear_thumbnails', 'false', 'toggle()', '20', '2012-08-12 17:21:18', '2012-08-12 17:01:17');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('80', '', 'local', 'Errors Last Reported', 'Time when errors where last reported by the background job.', 'errors_last_reported', '', '', '', '2012-08-16 08:43:35', '2012-08-16 08:43:35');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('81', '', 'local', '', '', 'jobs_module_job_error_reporter', 'a:4:{s:6:\"status\";s:7:\"Enabled\";s:16:\"email_receipient\";s:27:\"store@tim-international.net\";s:16:\"report_frequency\";s:5:\"Daily\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-16 08:43:35');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('78', '', 'local', 'Currencies Last Updated', 'Time when currencies where last updated by the background job.', 'currencies_last_updated', '2012-08-21 18:16:34', '', '', '2012-08-16 08:43:21', '2012-08-16 08:43:21');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('83', 'advanced', 'global', 'SEO Links Language Prefix', 'Begins the SEO links with the language code i.e. /en/....', 'seo_links_language_prefix', 'true', 'toggle()', '', '2012-08-20 22:48:44', '2012-08-20 22:48:44');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('84', 'advanced', 'global', 'Regional Settings Screen', 'Enables the regional settings screen upon first visit.', 'regional_settings_screen_enabled', 'true', 'toggle()', '', '2012-08-20 22:54:49', '2012-08-20 22:54:49');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('85', 'general', 'global', 'Set Currency by Language', 'Chain select currency when changing language.', 'set_currency_by_language', 'true', 'toggle()', '', '2012-08-20 23:19:52', '2012-08-20 23:19:52');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('86', 'general', 'local', 'Contact Form CAPTCHA', 'Prevents spam by enabling CAPTCHA in the contact form.', 'contact_form_captcha_enabled', 'false', 'toggle()', '', '2012-08-21 00:43:03', '2012-08-21 00:43:03');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('87', '', 'local', 'Databas Backups Last Run', 'Time when database backups where last made by the background job.', 'database_backups_last_run', '', '', '', '2012-08-21 18:20:48', '2012-08-21 18:20:48');

insert into c_settings (`id`, `setting_group_key`, `type`, `title`, `description`, `key`, `value`, `function`, `priority`, `date_updated`, `date_created`) values ('88', '', 'local', '', '', 'jobs_module_job_database_backup', 'a:7:{s:6:\"status\";s:7:\"Enabled\";s:16:\"backup_directory\";s:13:\"data/backups/\";s:15:\"backup_filename\";s:13:\"db-%y%m%d.sql\";s:16:\"backup_frequency\";s:5:\"Daily\";s:11:\"expire_days\";s:2:\"10\";s:13:\"ignore_tables\";s:18:\"lc_seo_links_cache\";s:8:\"priority\";s:1:\"0\";}', '', '', '0000-00-00 00:00:00', '2012-08-21 18:20:48');

drop table if exists c_settings_groups;
create table c_settings_groups (
  `id` int(11) not null auto_increment,
  `key` varchar(64) not null ,
  `name` varchar(64) not null ,
  `description` varchar(256) not null ,
  `priority` int(11) not null ,
  PRIMARY KEY (id)
);

insert into c_settings_groups (`id`, `key`, `name`, `description`, `priority`) values ('2', 'listings', 'Listings', '', '4');

insert into c_settings_groups (`id`, `key`, `name`, `description`, `priority`) values ('3', 'defaults', 'Defaults', 'Store default settings', '3');

insert into c_settings_groups (`id`, `key`, `name`, `description`, `priority`) values ('8', 'advanced', 'Advanced', '', '99');

insert into c_settings_groups (`id`, `key`, `name`, `description`, `priority`) values ('6', 'store_info', 'Store Info', '', '2');

insert into c_settings_groups (`id`, `key`, `name`, `description`, `priority`) values ('7', 'checkout', 'Checkout', '', '5');

drop table if exists c_sold_out_status;
create table c_sold_out_status (
  `id` int(11) not null auto_increment,
  `orderable` tinyint(1) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_sold_out_status (`id`, `orderable`, `date_updated`, `date_created`) values ('1', '', '2012-08-08 05:11:57', '2012-08-08 05:11:57');

insert into c_sold_out_status (`id`, `orderable`, `date_updated`, `date_created`) values ('2', '1', '2012-08-08 06:09:00', '2012-08-08 05:13:09');

insert into c_sold_out_status (`id`, `orderable`, `date_updated`, `date_created`) values ('3', '1', '2012-08-08 05:21:14', '2012-08-08 05:13:38');

drop table if exists c_sold_out_status_info;
create table c_sold_out_status_info (
  `id` int(11) not null auto_increment,
  `sold_out_status_id` int(11) not null ,
  `language_code` varchar(2) not null ,
  `name` varchar(32) not null ,
  `description` varchar(256) not null ,
  PRIMARY KEY (id)
);

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('1', '1', 'en', 'Sold Out', '');

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('2', '1', 'sv', 'Slutsåld', '');

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('3', '2', 'en', 'Pre-Order', '');

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('4', '2', 'sv', 'Förbeställ', '');

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('5', '3', 'en', 'In Stock', '');

insert into c_sold_out_status_info (`id`, `sold_out_status_id`, `language_code`, `name`, `description`) values ('6', '3', 'sv', 'I lager', '');

drop table if exists c_suppliers;
create table c_suppliers (
  `id` int(11) not null auto_increment,
  `name` varchar(64) not null ,
  `description` text not null ,
  `email` varchar(128) not null ,
  `phone` varchar(24) not null ,
  `link` varchar(256) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_suppliers (`id`, `name`, `description`, `email`, `phone`, `link`, `date_updated`, `date_created`) values ('1', 'Lightspeed Supplier', '
', '2', '3', '4', '0000-00-00 00:00:00', '2012-07-15 21:46:18');

drop table if exists c_tax_classes;
create table c_tax_classes (
  `id` int(11) not null auto_increment,
  `name` varchar(64) not null ,
  `description` varchar(64) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_tax_classes (`id`, `name`, `description`, `date_updated`, `date_created`) values ('1', 'Standardprodukter', '', '2012-04-01 16:04:29', '2012-04-01 16:04:29');

insert into c_tax_classes (`id`, `name`, `description`, `date_updated`, `date_created`) values ('2', 'Böcker', '', '2012-04-01 16:04:29', '2012-04-01 16:04:29');

insert into c_tax_classes (`id`, `name`, `description`, `date_updated`, `date_created`) values ('3', 'Livsmedel', '', '2012-07-03 14:23:49', '2012-07-03 14:23:49');

drop table if exists c_tax_rates;
create table c_tax_rates (
  `id` int(11) not null auto_increment,
  `tax_class_id` int(11) not null ,
  `geo_zone_id` int(11) not null ,
  `type` enum('fixed','percent') default 'percent' not null ,
  `name` varchar(64) not null ,
  `description` varchar(128) not null ,
  `rate` decimal(10,4) not null ,
  `customer_type` enum('individuals','companies','both') default 'both' not null ,
  `tax_id_rule` enum('with','without','both') default 'both' not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('1', '1', '1', 'percent', 'SE VAT 25%', 'Individuals in EU', '25.0000', 'individuals', 'both', '2012-08-08 17:39:48', '2012-04-01 16:05:20');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('2', '2', '1', 'percent', 'SE VAT 6%', 'Individuals in EU', '6.0000', 'individuals', 'both', '2012-07-17 04:38:55', '2012-04-01 16:05:20');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('4', '3', '1', 'percent', 'SE VAT 12%', 'Individuals in EU', '12.0000', 'individuals', 'both', '2012-08-08 17:39:33', '2012-07-03 14:25:08');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('5', '1', '2', 'percent', 'SE VAT 25%', '', '25.0000', 'both', 'both', '2012-07-17 04:32:19', '2012-07-17 04:31:40');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('6', '2', '2', 'percent', 'SE VAT 6%', '', '6.0000', 'both', 'both', '2012-07-17 04:32:08', '2012-07-17 04:32:08');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('7', '3', '2', 'percent', 'SE VAT 12%', '', '12.0000', 'both', 'both', '2012-07-17 04:32:55', '2012-07-17 04:32:55');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('8', '1', '1', 'percent', 'SE VAT 25%', 'Companies without tax ID in EU', '25.0000', 'companies', 'without', '2012-08-08 17:39:41', '2012-07-17 04:39:51');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('9', '3', '1', 'percent', 'SE VAT 12%', 'Companies in EU without tax ID', '12.0000', 'companies', 'without', '2012-08-09 12:30:56', '2012-07-17 04:40:33');

insert into c_tax_rates (`id`, `tax_class_id`, `geo_zone_id`, `type`, `name`, `description`, `rate`, `customer_type`, `tax_id_rule`, `date_updated`, `date_created`) values ('10', '2', '1', 'percent', 'SE VAT 6%', 'Companies in EU without tax ID', '6.0000', 'companies', 'without', '2012-07-17 04:41:23', '2012-07-17 04:41:23');

drop table if exists c_translations;
create table c_translations (
  `id` int(11) not null auto_increment,
  `code` varchar(255) not null ,
  `text_en` text not null ,
  `text_sv` text not null ,
  `html` tinyint(1) not null ,
  `pages` text not null ,
  `date_created` datetime not null ,
  `date_updated` datetime not null ,
  `date_accessed` datetime not null ,
  PRIMARY KEY (id),
  UNIQUE code (code)
);

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('1', 'title_catalog', 'Catalog', 'Katalog', '', '\'index.php\',\'includes/library/breadcrumbs.inc.php\',', '2012-07-10 10:38:28', '2012-08-04 04:15:22', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('819', 'job_database_backup:title_status', 'Status', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:29', '2012-08-21 18:20:29', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('820', 'job_database_backup:description_status', 'Enables or disables the module.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:29', '2012-08-21 18:20:29', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('4', 'title_new_products', 'New Products', 'Nya produkter', '', '\'index.php\',\'includes/boxes/new_products.inc.php\',', '2012-07-10 10:38:42', '2012-08-04 04:26:56', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('5', 'title_preview', 'Preview', 'Förhandsgranska', '', '\'index.php\',\'includes/product_listing.inc.php\',', '2012-07-10 10:38:46', '2012-08-04 05:32:10', '2012-07-20 02:44:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('6', 'title_new', 'New', 'Ny', '', '\'index.php\',\'includes/product_listing.inc.php\',\'includes/functions/draw.inc.php\',', '2012-07-10 10:38:47', '2012-08-04 04:26:56', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('7', 'title_add_to_cart', 'Add To Cart', 'Lägg i varukorgen', '', '\'index.php\',\'includes/product_listing.inc.php\',\'product.php\',', '2012-07-10 10:38:47', '2012-08-13 21:39:29', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('8', 'text_long_execution_time', 'We apologize for the inconvenience that the server seems temporary overloaded right now.', 'Vi ber om ursäkt för besväret att servern verkar vara tillfälligt överbelastad just nu.', '', '\'index.php\',\'includes/library/stats.inc.php\',', '2012-07-10 10:38:48', '2012-08-04 04:50:11', '2012-08-21 16:55:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('9', 'text_search_phrase_or_keyword', 'Search phrase or keyword', 'Sökfras eller nyckelord', '', '\'index.php\',\'includes/boxes/search.inc.php\',\'admin/translations.app/search.php\',', '2012-07-10 10:38:48', '2012-08-17 02:57:04', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('10', 'title_search', 'Search', 'Sök', '', '\'index.php\',\'includes/boxes/search.inc.php\',\'admin/translations.app/search.php\',\'admin/customers.app/customers.php\',\'admin/orders.app/orders.php\',', '2012-07-10 10:38:49', '2012-08-04 04:15:22', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('11', 'title_cart', 'Cart', 'Varukorg', '', '\'index.php\',\'includes/boxes/cart.inc.php\',', '2012-07-10 10:38:50', '2012-08-04 04:22:04', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('12', 'text_x_items', '%d item(s)', '%d produkt(er)', '', '\'index.php\',\'includes/boxes/cart.inc.php\',', '2012-07-10 10:38:51', '2012-08-04 04:22:04', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('13', 'title_checkout', 'Checkout', 'Kassa', '', '\'index.php\',\'includes/boxes/cart.inc.php\',\'checkout.php\',\'order_success.php\',', '2012-07-10 10:38:52', '2012-08-13 21:33:47', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('14', 'title_home', 'Home', 'Hem', '', '\'index.php\',\'includes/template/desktop_default.inc.php\',\'includes/boxes/site_menu.inc.php\',', '2012-07-10 10:38:52', '2012-08-04 04:22:04', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('15', 'title_contact_us', 'Contact Us', 'Kontakta oss', '', '\'index.php\',\'includes/template/desktop_default.inc.php\',\'contact_us.php\',\'includes/boxes/site_menu.inc.php\',\'support.php\',', '2012-07-10 10:38:53', '2012-08-04 04:22:04', '2012-08-20 09:17:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('16', 'title_categories', 'Categories', 'Kategorier', '', '\'index.php\',\'includes/boxes/categories.inc.php\',\'category.php\',\'admin/catalog.app/edit_product.php\',\'categories.php\',\'product.php\',', '2012-07-10 10:38:54', '2012-08-04 04:22:04', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('17', 'title_manufacturers', 'Manufacturers', 'Tillverkare', '', '\'index.php\',\'includes/boxes/manufacturers.inc.php\',\'admin/catalog.app/config.inc.php\',\'manufacturer.php\',\'product.php\',\'manufacturers.php\',', '2012-07-10 10:38:54', '2012-08-04 04:22:04', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('18', 'option_select', '-- Select --', '-- Välj --', '', '\'index.php\',\'includes/boxes/manufacturers.inc.php\',', '2012-07-10 10:38:55', '2012-08-04 04:22:04', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('19', 'title_login', 'Login', 'Logga in', '', '\'index.php\',\'includes/boxes/login.inc.php\',', '2012-07-10 10:38:56', '2012-08-04 04:22:04', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('20', 'title_email_address', 'E-mail Address', 'E-postadress', '', '\'index.php\',\'includes/boxes/login.inc.php\',\'contact_us.php\',\'support.php\',', '2012-07-10 10:38:56', '2012-08-13 23:03:41', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('21', 'title_password', 'Password', 'Lösenord', '', '\'index.php\',\'includes/boxes/login.inc.php\',\'includes/checkout/customer.php\',\'admin/customers.app/edit_customer.php\',\'admin/users.app/edit_user.php\',\'create_account.php\',', '2012-07-10 10:38:57', '2012-08-04 04:22:04', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('22', 'title_lost_password', 'Lost Password', 'Glömt lösenord', '', '\'index.php\',\'includes/boxes/login.inc.php\',', '2012-07-10 10:38:57', '2012-08-04 04:24:06', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('23', 'title_links', 'Links', 'Länkar', '', '\'index.php\',\'includes/boxes/links.inc.php\',', '2012-07-10 10:38:58', '2012-08-13 21:57:05', '2012-07-13 16:11:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('24', 'title_information', 'Information', 'Information', '', '\'product.php\',\'product.php\',\'support.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 10:39:46', '2012-08-04 05:30:25', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('25', 'text_no_product_description', 'There is no description for this product yet.', 'Det finns ingen beskrivning för denna produkt ännu.', '', '\'product.php\',\'product.php\',', '2012-07-10 10:39:47', '2012-08-04 05:19:58', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('26', 'title_options', 'Options', 'Alternativ', '', '\'product.php\',\'product.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 10:39:47', '2012-08-04 05:16:37', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('27', 'success_product_added_to_cart', 'Your product was successfully added to the cart.', 'Din produkt har lagts i varukorgen.', '', '\'product.php\',\'includes/library/cart.inc.php\',', '2012-07-10 10:40:47', '2012-08-04 05:19:58', '2012-08-20 08:53:05');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('28', 'title_quantity', 'Quantity', 'Antal', '', '\'checkout.php\',\'includes/checkout/cart.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 10:40:52', '2012-08-13 23:08:17', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('29', 'text_update', 'Update', 'Uppdatera', '', '\'checkout.php\',\'includes/checkout/cart.php\',', '2012-07-10 10:40:53', '2012-08-14 00:32:31', '2012-08-20 08:53:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('30', 'text_remove', 'Remove', 'Ta bort', '', '\'checkout.php\',\'includes/checkout/cart.php\',\'admin/catalog.app/edit_product.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',', '2012-07-10 10:40:53', '2012-08-04 04:24:06', '2012-08-20 20:12:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('31', 'title_customer_information', 'Customer Information', 'Kundinformation', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',', '2012-07-10 10:40:54', '2012-08-04 04:24:06', '2012-08-20 08:53:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('32', 'title_new_customer', 'New Customer', 'Ny kund', '', '\'checkout.php\',\'includes/checkout/customer.php\',', '2012-07-10 10:40:55', '2012-08-04 04:24:06', '2012-08-20 08:53:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('33', 'title_company', 'Company', 'Företag', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:40:55', '2012-08-04 04:16:29', '2012-08-20 08:55:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('34', 'title_tax_id', 'Tax ID', 'Momsregistreringsnr', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'includes/receipt.inc.php\',\'admin/orders.app/edit_order.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/customers.app/edit_customer.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',\'create_account.php\',', '2012-07-10 10:40:56', '2012-08-13 23:05:35', '2012-08-20 08:55:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('35', 'title_firstname', 'First Name', 'Förnamn', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:40:58', '2012-08-04 04:16:29', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('36', 'title_lastname', 'Last Name', 'Efternamn', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:40:59', '2012-08-04 04:16:29', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('37', 'title_email', 'E-mail', 'E-postadress', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:40:59', '2012-08-13 23:03:41', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('38', 'title_phone', 'Phone', 'Telefon', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/customers.app/edit_customer.php\',\'includes/printable_packing_slip.inc.php\',\'create_account.php\',', '2012-07-10 10:41:00', '2012-08-04 04:16:29', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('39', 'title_address1', 'Address 1', 'Adress 1', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:41:01', '2012-08-04 04:16:29', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('40', 'title_address2', 'Address 2', 'Adress 2', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:41:02', '2012-08-04 04:17:36', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('41', 'title_city', 'City', 'Stad', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:41:02', '2012-08-04 04:17:36', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('42', 'title_postcode', 'Postcode', 'Postnummer', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/customers.app/edit_customer.php\',\'create_account.php\',', '2012-07-10 10:41:03', '2012-08-13 23:46:42', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('43', 'title_country', 'Country', 'Land', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/customers.app/edit_customer.php\',\'admin/orders.app/orders.php\',\'select_region.php\',\'create_account.php\',', '2012-07-10 10:41:08', '2012-08-04 04:18:32', '2012-08-21 10:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('44', 'title_select', 'Select', 'Välj', '', '\'checkout.php\',\'includes/functions/form.inc.php\',\'includes/checkout/shipping.php\',\'includes/checkout/payment.php\',\'includes/zones.json.php\',\'includes/options_values.json.php\',\'includes/product_option_values.json.php\',', '2012-07-10 10:41:09', '2012-08-04 04:18:32', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('45', 'title_zone', 'Zone', 'Zon', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'admin/orders.app/edit_order.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/customers.app/edit_customer.php\',\'select_region.php\',\'create_account.php\',', '2012-07-10 10:41:10', '2012-08-04 04:18:32', '2012-08-21 10:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('46', 'title_different_shipping_address', 'Different Shipping Address', 'Separat leveransadress', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'edit_account.php\',\'create_account.php\',', '2012-07-10 10:41:18', '2012-08-04 04:24:06', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('47', 'title_save', 'Save', 'Spara', '', '\'checkout.php\',\'includes/checkout/customer.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/translations.app/search.php\',\'edit_account.php\',\'admin/settings.app/settings.php\',\'admin/orders.app/edit_order.php\',\'admin/modules.app/edit_module.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/translations.app/untranslated.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/currencies.app/edit_currency.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/customers.app/edit_customer.php\',\'select_region.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/countries.app/edit_country.php\',\'admin/tax.app/edit_tax_class.php\',\'admin/pages.app/edit_page.php\',\'admin/translations.app/pages.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/edit_product_option_group.php\',\'admin/users.app/edit_user.php\',\'create_account.php\',', '2012-07-10 10:41:19', '2012-08-04 04:17:36', '2012-08-21 18:20:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('48', 'paypal:title_merchant_email', 'Merchant E-mail', '', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',', '2012-07-10 10:41:20', '2012-08-04 06:07:59', '2012-07-23 14:41:43');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('49', 'paypal:description_merchant_email', 'Your Paypal registered merchant e-mail address.', 'Din registrerade Paypal e-mail', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',', '2012-07-10 10:41:21', '2012-08-13 23:09:58', '2012-08-05 06:59:34');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('50', 'title_geo_zone_limitation', 'Geo Zone Limitation', 'Geografisk zonbegränsning', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/payment/cod.inc.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',\'includes/modules/payment/pm_cod.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-07-10 10:41:21', '2012-08-04 04:24:06', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('51', 'description_geo_zone_limitation', '', 'Om en geografisk zon är vald kommer fraktalternativ endast visas för den zonen.', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',', '2012-07-10 10:41:22', '2012-08-04 05:59:38', '2012-08-05 04:25:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('52', 'title_sort_order', 'Sort Order', 'Sorteringsordning', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/languages.php\',\'admin/languages.app/edit_language.php\',\'admin/modules.app/modules.php\',\'includes/modules/shipping/weight_table.inc.php\',\'includes/modules/payment/invoice.inc.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-10 10:41:23', '2012-08-04 04:26:56', '2012-08-05 07:05:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('53', 'description_sort_order', 'Display this module according to the given sort order value.', 'Visa denna modul enligt det angivna sorteringsordervärdet.', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',', '2012-07-10 10:41:24', '2012-08-13 21:42:25', '2012-08-05 04:25:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('54', 'title_subtotal', 'Subtotal', 'Delsumma', '', '\'checkout.php\',\'includes/modules/order_total/ot_subtotal.inc.php\',', '2012-07-10 10:41:24', '2012-08-04 04:26:56', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('55', 'title_order_confirmation', 'Order Confirmation', 'Orderbekräftelse', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',', '2012-07-10 10:41:25', '2012-08-14 00:34:21', '2012-07-23 11:43:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('56', 'title_product', 'Product', 'Produkt', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:32', '2012-08-04 04:26:56', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('57', 'title_unit_cost', 'Unit Cost', 'Enhetskostnad', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:33', '2012-08-04 04:27:49', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('58', 'title_tax', 'Tax', 'Moms', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'admin/tax.app/config.inc.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:43', '2012-08-04 04:20:58', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('59', 'title_total', 'Total', 'Totalt', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:49', '2012-08-04 04:27:49', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('60', 'title_payment_due', 'Payment Due', 'Att betala', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:55', '2012-08-15 03:27:44', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('61', 'error_insufficient_customer_information', 'Insufficient customer information, please fill out all necessary fields.', '', '', '\'checkout.php\',\'includes/controllers/order.inc.php\',', '2012-07-10 10:41:56', '2012-08-07 01:33:20', '2012-08-20 08:33:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('62', 'title_confirm_order', 'Confirm Order', 'Bekräfta order', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:41:56', '2012-08-04 04:27:49', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('63', 'title_name', 'Name', 'Namn', '', '\'category.php\',\'category.php\',\'admin/countries.app/countries.php\',\'contact_us.php\',\'manufacturer.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/languages.php\',\'admin/languages.app/edit_language.php\',\'admin/modules.app/modules.php\',\'admin/orders.app/order_statuses.php\',\'admin/catalog.app/option_groups.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/manufacturers.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/suppliers.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',\'admin/tax.app/tax_rates.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/geo_zones.app/geo_zones.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/tax.app/tax_classes.php\',\'admin/catalog.app/product_groups.php\',\'search.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/delivery_statuses.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/sold_out_statuses.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/countries.app/edit_country.php\',\'admin/tax.app/edit_tax_class.php\',\'support.php\',\'admin/customers.app/customers.php\',\'admin/catalog.app/product_configuration_groups.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/product_option_groups.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-10 10:43:39', '2012-08-04 05:07:25', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('64', 'title_price', 'Price', 'Pris', '', '\'category.php\',\'category.php\',\'manufacturer.php\',\'admin/catalog.app/edit_product.php\',\'search.php\',', '2012-07-10 10:43:41', '2012-08-04 05:07:25', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('65', 'title_date', 'Date', 'Datum', '', '\'category.php\',\'category.php\',\'order_history.php\',\'manufacturer.php\',\'admin/orders.app/orders.php\',\'search.php\',\'admin/orders.widget/orders.php\',', '2012-07-10 10:43:41', '2012-08-13 21:51:14', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('66', 'title_sort_by', 'Sort By', 'Sorter efter', '', '\'category.php\',\'category.php\',\'manufacturer.php\',\'search.php\',', '2012-07-10 10:43:42', '2012-08-04 05:07:25', '2012-08-21 06:38:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('67', 'success_welcome_back_user', 'Welcome back %firstname %lastname.', 'Välkommen tillbaka %firstname %lastname.', '', '\'includes/checkout/customer.php\',\'includes/library/customer.inc.php\',', '2012-07-10 10:45:03', '2012-08-04 06:02:51', '2012-08-20 08:56:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('68', 'title_set_default', 'Set as Default', 'Ange som standard', '', '\'includes/checkout/customer.php\',\'includes/checkout/customer.php\',\'edit_account.php\',', '2012-07-10 10:45:05', '2012-08-13 21:33:01', '2012-08-18 06:40:26');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('69', 'title_shipping', 'Shipping', 'Frakt', '', '\'includes/checkout/shipping.php\',\'includes/checkout/shipping.php\',\'admin/modules.app/config.inc.php\',', '2012-07-10 10:45:07', '2012-08-04 04:27:49', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('70', 'title_payment', 'Payment', 'Betalning', '', '\'includes/checkout/payment.php\',\'includes/checkout/payment.php\',\'admin/modules.app/config.inc.php\',', '2012-07-10 10:45:08', '2012-08-04 04:27:49', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('71', 'text_go_to_store_front', 'Go to store front', 'Gå till butikens framsida', '', '\'admin/index.php\',\'admin/index.php\',\'includes/template/desktop_admin.inc.php\',\'includes/templates/default/admin.desktop.inc.php\',', '2012-07-10 10:46:17', '2012-08-13 21:31:36', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('72', 'title_option_groups', 'Option Groups', 'Alternativgrupper', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-07-10 10:46:17', '2012-08-13 22:12:52', '2012-08-15 01:12:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('73', 'title_countries', 'Countries', 'Länder', '', '\'admin/index.php\',\'admin/countries.app/config.inc.php\',', '2012-07-10 10:46:18', '2012-08-04 05:19:58', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('74', 'title_currencies', 'Currencies', 'Valutor', '', '\'admin/index.php\',\'admin/currencies.app/config.inc.php\',', '2012-07-10 10:46:20', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('75', 'title_customers', 'Customers', 'Kunder', '', '\'admin/index.php\',\'admin/customers.app/config.inc.php\',', '2012-07-10 10:46:20', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('76', 'title_geo_zones', 'Geo Zones', 'Geografiska zoner', '', '\'admin/index.php\',\'admin/geo_zones.app/config.inc.php\',', '2012-07-10 10:46:26', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('77', 'title_languages', 'Languages', 'Språk', '', '\'admin/index.php\',\'admin/languages.app/config.inc.php\',\'admin/translations.app/csv.php\',', '2012-07-10 10:46:27', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('78', 'title_modules', 'Modules', 'Moduler', '', '\'admin/index.php\',\'admin/modules.app/config.inc.php\',', '2012-07-10 10:46:28', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('79', 'title_orders', 'Orders', 'Ordrar', '', '\'admin/index.php\',\'admin/orders.app/config.inc.php\',\'admin/orders.widget/config.inc.php\',', '2012-07-10 10:46:28', '2012-08-04 04:16:29', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('80', 'title_order_statuses', 'Order Statuses', 'Orderstatus', '', '\'admin/index.php\',\'admin/orders.app/config.inc.php\',', '2012-07-10 10:46:29', '2012-08-04 04:16:29', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('81', 'title_settings', 'Settings', 'Inställningar', '', '\'admin/index.php\',\'admin/settings.app/config.inc.php\',', '2012-07-10 10:46:30', '2012-08-04 06:06:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('82', 'title_tax_classes', 'Tax Classes', 'Momsklasser', '', '\'admin/index.php\',\'admin/tax.app/config.inc.php\',', '2012-07-10 10:46:30', '2012-08-04 06:06:19', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('83', 'title_tax_rates', 'Tax Rates', 'Momssatser', '', '\'admin/index.php\',\'admin/tax.app/config.inc.php\',', '2012-07-10 10:46:32', '2012-08-07 01:33:40', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('84', 'title_translations', 'Translations', 'Översättningar', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-10 10:46:33', '2012-08-14 00:38:54', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('85', 'title_search_translations', 'Search Translations', 'Sök översättningar', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-10 10:46:34', '2012-08-14 00:38:54', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('86', 'title_untranslated', 'Untranslated', 'Saknar översättning', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-10 10:46:34', '2012-08-14 00:38:54', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('87', 'title_translations_by_page', 'Translations by page', 'Översättningar per sida', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-10 10:46:36', '2012-08-14 00:38:54', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('88', 'title_dashboard', 'Dashboard', 'Instrumentbräda', '', '\'admin/index.php\',\'includes/template/desktop_admin.inc.php\',', '2012-07-10 10:46:36', '2012-08-04 04:17:36', '2012-08-09 06:47:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('89', 'title_id', 'ID', 'ID', '', '\'admin/index.php\',\'admin/countries.app/countries.php\',\'order_history.php\',\'admin/languages.app/languages.php\',\'admin/modules.app/modules.php\',\'admin/orders.app/orders.php\',\'admin/orders.app/order_statuses.php\',\'admin/catalog.app/option_groups.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/currencies.app/currencies.php\',\'admin/tax.app/tax_rates.php\',\'admin/geo_zones.app/geo_zones.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/tax.app/tax_classes.php\',\'admin/catalog.app/product_groups.php\',\'admin/customers.app/customers.php\',\'admin/catalog.app/delivery_statuses.php\',\'admin/catalog.app/sold_out_statuses.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/countries.app/edit_country.php\',\'admin/pages.app/pages.php\',\'admin/orders.widget/orders.php\',\'admin/catalog.app/product_configuration_groups.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/product_option_groups.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-10 10:48:08', '2012-08-04 05:07:25', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('90', 'title_zones', 'Zones', 'Zoner', '', '\'admin/index.php\',\'admin/countries.app/countries.php\',\'admin/geo_zones.app/geo_zones.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/countries.app/edit_country.php\',', '2012-07-10 10:48:13', '2012-08-04 04:43:25', '2012-08-21 10:07:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('91', 'title_edit', 'Edit', 'Redigera', '', '\'admin/index.php\',\'admin/countries.app/countries.php\',\'admin/languages.app/languages.php\',\'admin/settings.app/settings.php\',\'admin/orders.app/order_statuses.php\',\'admin/translations.app/pages.php\',\'admin/currencies.app/currencies.php\',\'admin/tax.app/tax_rates.php\',\'admin/geo_zones.app/geo_zones.php\',\'admin/tax.app/tax_classes.php\',\'admin/catalog.app/delivery_statuses.php\',\'admin/catalog.app/sold_out_statuses.php\',\'admin/pages.app/pages.php\',\'admin/users.app/users.php\',', '2012-07-10 10:48:14', '2012-08-04 05:28:22', '2012-08-21 10:07:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('92', 'title_page', 'Page', 'Sida', '', '\'admin/index.php\',\'includes/functions/general.inc.php\',', '2012-07-10 10:48:15', '2012-08-04 04:17:36', '2012-08-21 10:07:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('93', 'title_back_to_index', 'Back To Index', 'Tillbaka till index', '', '\'admin/index.php\',\'admin/index.php\',\'includes/template/desktop_admin.inc.php\',\'includes/templates/default/admin.desktop.inc.php\',', '2012-07-10 10:48:15', '2012-08-04 04:17:36', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('94', 'title_send', 'Send', 'Skicka', '', '\'contact_us.php\',\'contact_us.php\',\'support.php\',', '2012-07-10 10:48:49', '2012-08-04 04:46:36', '2012-08-20 09:17:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('95', 'title_account', 'Account', 'Konto', '', '\'contact_us.php\',\'includes/boxes/account.inc.php\',', '2012-07-10 10:48:50', '2012-08-04 05:30:25', '2012-08-21 06:38:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('96', 'title_order_history', 'Order History', 'Orderhistorik', '', '\'contact_us.php\',\'includes/boxes/account.inc.php\',\'order_history.php\',', '2012-07-10 10:48:51', '2012-08-04 05:30:25', '2012-08-21 06:38:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('97', 'title_change_password', 'Change Password', 'Ändra lösenord', '', '\'contact_us.php\',\'includes/boxes/account.inc.php\',', '2012-07-10 10:48:51', '2012-08-04 05:29:59', '2012-07-10 21:59:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('98', 'title_logout', 'Logout', 'Logga ut', '', '\'contact_us.php\',\'includes/boxes/account.inc.php\',', '2012-07-10 10:48:52', '2012-08-04 05:30:25', '2012-08-21 06:38:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('99', 'title_including_tax', 'Including Tax', 'Inklusive moms', '', '\'checkout.php\',\'includes/checkout/confirmation.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'product.php\',\'includes/checkout/summary.php\',', '2012-07-10 10:49:17', '2012-08-04 04:29:08', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('100', 'paypal:title_finalize_order', 'Finalize Order', 'Avsluta order', '', '\'checkout.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-07-10 10:49:18', '2012-08-04 06:07:59', '2012-08-15 07:40:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('101', 'title_amount', 'Amount', 'Summa', '', '\'order_history.php\',\'order_history.php\',\'admin/orders.app/orders.php\',\'admin/orders.widget/orders.php\',', '2012-07-10 10:50:42', '2012-08-13 22:18:29', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('102', 'title_order_status', 'Order Status', 'Orderstatus', '', '\'order_history.php\',\'order_history.php\',\'admin/orders.app/orders.php\',\'includes/modules/payment/cod.inc.php\',\'includes/modules/payment/paypal.inc.php\',\'admin/orders.app/edit_order.php\',\'includes/modules/payment/pm_cod.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-07-10 10:50:43', '2012-08-04 05:07:25', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('103', 'title_nothing_found', 'Nothing found', 'Ingenting hittades', '', '\'order_history.php\',\'order_history.php\',\'admin/orders.app/orders.php\',\'admin/catalog.app/suppliers.php\',\'admin/customers.app/customers.php\',', '2012-07-10 10:50:43', '2012-08-04 05:14:42', '2012-08-20 08:42:44');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('104', 'title_add_new_category', 'Add New Category', 'Lägg till ny kategori', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 15:25:29', '2012-08-13 21:39:29', '2012-08-21 16:55:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('105', 'title_add_new_product', 'Add New Product', 'Lägg till ny produkt', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',', '2012-07-10 15:25:35', '2012-08-13 21:39:29', '2012-08-21 16:55:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('106', 'title_root', '[Root]', '[Rot]', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 15:25:39', '2012-08-04 05:16:37', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('107', 'title_edit_category', 'Edit Category', 'Redigera kategori', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 15:25:46', '2012-08-13 23:39:23', '2012-08-20 21:55:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('108', 'title_status', 'Status', 'Status', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/currencies.app/edit_currency.php\',\'includes/modules/payment/cod.inc.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',\'includes/modules/payment/pm_cod.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',\'admin/countries.app/edit_country.php\',', '2012-07-10 15:25:51', '2012-08-04 05:16:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('109', 'title_published', 'Published', 'Publicerad', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:25:57', '2012-08-04 05:16:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('110', 'title_parent_category', 'Parent Category', 'Huvudkategori', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 15:25:58', '2012-08-13 21:53:46', '2012-08-20 21:55:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('111', 'option_root', '[Root]', '[Rot]', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 15:26:04', '2012-08-04 04:50:11', '2012-08-20 21:55:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('112', 'title_image', 'Image', 'Bild', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:26:04', '2012-08-13 23:17:21', '2012-08-20 21:06:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('113', 'title_short_description', 'Short Description', 'Kortbeskrivning', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:26:05', '2012-08-04 05:16:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('114', 'title_description', 'Description', 'Beskrivning', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/tax.app/tax_classes.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/tax.app/edit_tax_class.php\',\'admin/tax.app/tax_rates.php\',', '2012-07-10 15:26:05', '2012-08-04 05:16:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('115', 'title_keywords', 'Keywords', 'Nyckelord', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:26:06', '2012-08-14 00:22:31', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('116', 'title_cancel', 'Cancel', 'Avbryt', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/settings.app/settings.php\',\'admin/modules.app/edit_module.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/currencies.app/edit_currency.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/customers.app/edit_customer.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/countries.app/edit_country.php\',\'admin/tax.app/edit_tax_class.php\',\'admin/pages.app/edit_page.php\',\'admin/translations.app/pages.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_product_option_group.php\',\'admin/users.app/edit_user.php\',', '2012-07-10 15:26:10', '2012-08-04 05:16:37', '2012-08-21 18:20:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('117', 'success_changes_saved', 'Changes saved', 'Ändringar sparade', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/translations.app/search.php\',\'edit_account.php\',\'admin/settings.app/settings.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/translations.app/untranslated.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/currencies.app/edit_currency.php\',\'admin/orders.app/edit_order.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/pages.app/edit_page.php\',\'admin/translations.app/pages.php\',\'admin/users.app/edit_user.php\',\'admin/countries.app/edit_country.php\',', '2012-07-10 15:32:44', '2012-08-13 23:40:50', '2012-08-21 10:05:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('118', 'title_empty', 'Empty', 'Tom', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',', '2012-07-10 15:35:40', '2012-08-04 05:26:05', '2012-08-16 11:42:28');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('119', 'title_delete', 'Delete', 'Ta bort', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/currencies.app/edit_currency.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/customers.app/edit_customer.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/countries.app/edit_country.php\',\'admin/tax.app/edit_tax_class.php\',\'admin/pages.app/edit_page.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-10 15:35:48', '2012-08-04 05:16:37', '2012-08-21 16:55:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('120', 'text_are_you_sure', 'Are you sure?', 'Är du säker?', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/languages.app/edit_language.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',\'admin/currencies.app/edit_currency.php\',\'admin/tax.app/edit_tax_rate.php\',\'admin/customers.app/edit_customer.php\',\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/edit_sold_out_status.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/countries.app/edit_country.php\',\'admin/tax.app/edit_tax_class.php\',\'admin/modules.app/edit_module.php\',\'admin/pages.app/edit_page.php\',\'admin/orders.app/edit_order_status.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-10 15:35:49', '2012-08-04 05:16:37', '2012-08-21 18:20:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('121', 'success_post_deleted', 'Post deleted', 'Inlägg raderat', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:35:56', '2012-08-13 21:39:29', '2012-08-20 18:46:44');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('122', 'title_new_image', 'New Image', 'Ny bild', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-10 15:36:55', '2012-08-13 23:17:21', '2012-08-20 21:55:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('123', 'title_edit_product', 'Edit Product', 'Redigera produkt', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:00:58', '2012-08-13 23:39:23', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('124', 'title_manufacturer', 'Manufacturer', 'Tillverkare', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:00:58', '2012-08-04 06:02:51', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('125', 'title_purchase_price', 'Purchase Price', 'Inköpspris', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:00:59', '2012-08-04 06:02:51', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('126', 'title_gross_price', 'Gross Price', 'Bruttopris', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:00', '2012-08-04 06:02:51', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('127', 'title_net_price', 'Net Price', 'Nettopris', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:01', '2012-08-04 06:02:51', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('128', 'title_specials_price', 'Specials Price', 'Erbjudande', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:01', '2012-08-04 06:02:51', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('129', 'title_specials_price_expire_date', 'Specials Price Expire Date', 'Erbjudandets utgångsdatum', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:02', '2012-08-13 21:51:14', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('130', 'title_tax_class', 'Tax Class', 'Momsklass', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/tax.app/tax_rates.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-10 16:01:03', '2012-08-04 05:21:10', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('131', 'title_upload_images', 'Upload Images', 'Ladda upp bilder', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:04', '2012-08-13 23:17:21', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('132', 'title_model', 'Model', 'Modell', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:04', '2012-08-04 05:21:10', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('133', 'title_sku', 'SKU', 'SKU', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'includes/checkout/cart.php\',\'includes/checkout/summary.php\',', '2012-07-10 16:01:05', '2012-08-04 04:29:08', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('134', 'title_upc', 'UPC', 'UPC', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:05', '2012-08-09 18:35:15', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('135', 'title_taric', 'TARIC', 'TARIC', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:06', '2012-08-09 18:35:15', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('136', 'title_weight', 'Weight', 'Vikt', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 16:01:06', '2012-08-04 05:14:42', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('137', 'title_dimensions', 'Dimensions', 'Dimensioner', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:07', '2012-08-15 01:59:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('138', 'description_width_height_length', 'width x height x length', 'bredd x höjd x längd', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:08', '2012-08-15 01:59:57', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('139', 'title_attributes', 'Attributes', 'Attribut', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:08', '2012-08-04 04:58:24', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('140', 'title_option', 'Option', 'Alternativ', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:09', '2012-08-04 04:58:24', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('141', 'title_new_option', 'New Option', 'Nytt alternativ', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:10', '2012-08-04 04:58:24', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('142', 'title_group', 'Group', 'Grupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:11', '2012-08-13 21:43:03', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('143', 'title_value', 'Value', 'Värde', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/settings.app/settings.php\',\'admin/orders.app/edit_order.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-10 16:01:11', '2012-08-13 21:42:25', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('144', 'title_add_combination', 'Add Combination', 'Lägg till kombination', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:12', '2012-08-13 21:39:29', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('145', 'title_add_option', 'Add Option', 'Lägg till alternativ', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:13', '2012-08-13 21:39:29', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('146', 'title_remove', 'Remove', 'Ta bort', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/countries.app/edit_country.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-10 16:01:14', '2012-08-04 04:18:32', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('147', 'error_empty_option_group', 'Error: Empty option group', 'Fel: Tom grupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:14', '2012-08-13 21:43:03', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('148', 'error_empty_option_value', 'Error: Empty option value', 'Fel: Tomt alternativvärde', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:15', '2012-08-13 21:42:25', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('149', 'text_move_up', 'Move up', 'Flytta upp', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:16', '2012-08-04 05:21:10', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('150', 'text_move_down', 'Move down', 'Flytta ned', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:01:16', '2012-08-04 05:21:10', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('151', 'title_product_images', 'Product Images', 'Produktbilder', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:04:05', '2012-08-13 23:17:21', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('152', 'title_date_updated', 'Date Updated', 'Uppdaterades den', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 16:04:06', '2012-08-14 00:32:31', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('153', 'title_date_created', 'Date Created', 'Datum skapad', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 16:04:06', '2012-08-14 00:10:17', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('154', 'error_missing_name', 'You must enter a name.', 'Du måste ange ett namn.', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-10 16:04:18', '2012-08-04 05:12:18', '2012-07-10 16:04:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('155', 'title_add_new_language', 'Add New Language', 'Lägg till nytt språk', '', '\'admin/index.php\',\'admin/languages.app/languages.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:46', '2012-08-13 21:39:11', '2012-08-21 08:22:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('156', 'title_code', 'Code', 'Kod', '', '\'admin/index.php\',\'admin/languages.app/languages.php\',\'admin/languages.app/edit_language.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',\'admin/translations.app/pages.php\',\'admin/currencies.app/currencies.php\',\'admin/countries.app/edit_country.php\',\'admin/catalog.app/edit_category.php\',', '2012-07-10 16:49:46', '2012-08-13 22:00:42', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('157', 'title_edit_language', 'Edit Language', 'Redigera språk', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:51', '2012-08-13 23:39:23', '2012-08-21 08:22:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('158', 'title_enabled', 'Enabled', 'Aktiverad', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',\'admin/currencies.app/edit_currency.php\',\'admin/countries.app/edit_country.php\',', '2012-07-10 16:49:52', '2012-08-04 04:51:10', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('159', 'title_disabled', 'Disabled', 'Inaktiverad', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',\'admin/currencies.app/edit_currency.php\',\'admin/countries.app/edit_country.php\',', '2012-07-10 16:49:53', '2012-08-04 04:51:10', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('160', 'title_charset', 'Charset', 'Teckenuppsättning', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:53', '2012-08-04 05:58:17', '2012-08-21 08:22:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('161', 'title_system_locale', 'System Locale', 'Språk', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:54', '2012-08-13 21:28:39', '2012-08-21 08:22:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('162', 'title_date_format', 'Date Format', 'Datumformat', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:54', '2012-08-13 21:51:14', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('163', 'title_time_format', 'Time Format', 'Tidsformat', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:55', '2012-08-04 05:58:17', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('164', 'title_raw_date_format', 'Raw Date Format', 'Datumformat (rå)', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:56', '2012-08-14 00:27:17', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('165', 'title_raw_time_format', 'Raw Time Format', 'Tidsformat (rå)', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:56', '2012-08-14 00:27:17', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('166', 'title_decimal_point', 'Decimal Point', 'Decimaltecken', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:57', '2012-08-04 06:01:13', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('167', 'title_thousands_sep', 'Thousands Separator', 'Tusendelsseparator', '', '\'admin/index.php\',\'admin/languages.app/edit_language.php\',', '2012-07-10 16:49:57', '2012-08-04 06:01:13', '2012-08-21 08:22:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('168', 'text_shared_by_pages', 'Shared by %d pages', 'Delad av %d sidor', '', '\'admin/index.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',', '2012-07-10 16:50:22', '2012-08-04 05:55:19', '2012-08-20 20:12:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('169', 'text_html_enabled', 'HTML enabled', 'HTML aktiverat', '', '\'admin/index.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',\'admin/translations.app/pages.php\',', '2012-07-10 16:50:23', '2012-08-04 04:17:36', '2012-08-20 20:12:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('170', 'description_no_items_in_cart', 'There are no items in your cart.', 'Det finns inga produkter i varukorgen.', '', '\'includes/checkout/cart.php\',\'includes/checkout/cart.php\',', '2012-07-10 17:02:43', '2012-08-04 05:01:51', '2012-08-18 06:27:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('171', 'error_delete_category_not_empty_products', 'The category could not be deleted because there are products linked to it.', 'Kategorin kunde inte tas bort eftersom det finns produkter kopplade till den.', '', '\'admin/index.php\',\'includes/controllers/category.inc.php\',', '2012-07-10 17:15:44', '2012-08-04 05:29:59', '2012-08-09 20:07:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('172', 'title_edit_account', 'Edit Account', 'Ändra konto', '', '\'index.php\',\'includes/boxes/account.inc.php\',\'edit_account.php\',', '2012-07-10 22:16:24', '2012-08-13 23:33:31', '2012-08-21 06:38:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('173', 'title_new_password', 'New Password', 'Nytt lösenord', '', '\'edit_account.php\',\'edit_account.php\',\'admin/customers.app/edit_customer.php\',', '2012-07-10 22:17:13', '2012-08-04 04:50:11', '2012-08-15 07:13:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('174', 'title_confirm_password', 'Confirm Password', 'Bekräfta lösenord', '', '\'edit_account.php\',\'edit_account.php\',\'includes/checkout/customer.php\',\'admin/users.app/edit_user.php\',\'create_account.php\',', '2012-07-10 22:17:13', '2012-08-04 04:50:11', '2012-08-20 08:55:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('175', 'error_missing_confirmed_password', 'You must confirm your password.', 'Du måste bekräfta ditt lösenord.', '', '\'edit_account.php\',\'edit_account.php\',', '2012-07-10 22:22:19', '2012-08-04 05:29:59', '2012-07-10 22:24:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('176', 'error_passwords_missmatch', 'The passwords did not match.', 'Lösenorden matchar inte.', '', '\'edit_account.php\',\'edit_account.php\',\'admin/users.app/edit_user.php\',', '2012-07-10 22:24:23', '2012-08-04 05:29:59', '2012-08-17 05:20:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('177', 'warning_session_hijacking_attempt_blocked', '', '', '', '\'edit_account.php\',\'includes/library/customer.inc.php\',', '2012-07-10 22:25:06', '2012-08-04 06:07:19', '2012-08-21 06:39:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('178', 'title_order_copy', 'Order Copy', 'Orderkopia', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',', '2012-07-10 22:27:36', '2012-08-13 23:09:32', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('179', 'title_order', 'Order', 'Order', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',', '2012-07-10 22:27:37', '2012-08-13 22:19:25', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('180', 'title_datum', 'Date', 'Datum', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',', '2012-07-10 22:27:38', '2012-08-13 21:51:14', '2012-08-15 05:00:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('181', 'title_payment_address', 'Payment Address', 'Betaladress', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:38', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('182', 'title_shipping_address', 'Shipping Address', 'Leveransadress', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:39', '2012-08-04 04:18:32', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('183', 'title_payment_option', 'Payment Option', 'Betalningsalternativ', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:40', '2012-08-04 05:14:42', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('184', 'title_transaction_number', 'Transaction Number', 'Transaktionsnummer', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:40', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('185', 'title_shipping_option', 'Shipping Option', 'Fraktalternativ', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:41', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('186', 'title_order_items', 'Order Items', 'Orderobjekt', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',', '2012-07-10 22:27:42', '2012-08-15 03:26:56', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('187', 'title_qty', 'Qty', 'Antal', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_product.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:42', '2012-08-15 03:25:46', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('188', 'title_item', 'Item', 'Produkt', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-10 22:27:43', '2012-08-04 04:20:58', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('189', 'title_unit_price', 'Unit Price', 'Enhetspris', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'admin/orders.app/edit_order.php\',', '2012-07-10 22:27:44', '2012-08-04 04:20:58', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('190', 'title_sum', 'Sum', 'Summa', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',', '2012-07-10 22:27:44', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('191', 'title_grand_total', 'Grand Total', 'Totalsumma', '', '\'order_process.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',', '2012-07-10 22:27:45', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('192', 'title_details', 'Details', 'Detaljer', '', '\'product.php\',\'product.php\',', '2012-07-11 12:25:07', '2012-08-04 05:29:59', '2012-07-11 12:25:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('193', 'title_shipping_modules', 'Shipping Modules', 'Fraktmoduler', '', '\'admin/index.php\',\'admin/modules.app/modules.php\',\'admin/modules.app/config.inc.php\',', '2012-07-11 12:27:08', '2012-08-04 04:45:27', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('194', 'title_payment_modules', 'Payment Modules', 'Betalmoduler', '', '\'admin/index.php\',\'admin/modules.app/modules.php\',\'admin/modules.app/config.inc.php\',', '2012-07-11 12:27:10', '2012-08-04 04:45:27', '2012-08-15 03:05:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('195', 'title_order_total_modules', 'Order Total Modules', 'Order total-moduler', '', '\'admin/index.php\',\'admin/modules.app/modules.php\',\'admin/modules.app/config.inc.php\',', '2012-07-11 12:27:10', '2012-08-13 22:19:25', '2012-08-15 02:30:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('196', 'settings_group:customer_info', 'Customer Info', 'Kundinformation', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-11 12:27:13', '2012-08-04 04:37:45', '2012-08-09 13:51:58');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('197', 'settings_group:defaults', 'Defaults', 'Standardvärden', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-11 12:27:14', '2012-08-13 21:42:25', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('198', 'settings_group_title:general', 'General', 'Allmänt', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:14', '2012-08-13 23:16:49', '2012-08-09 09:19:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('199', 'settings_group:listings', 'Listings', 'Produktlistning', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-11 12:27:15', '2012-08-13 23:06:56', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('200', 'settings_group:microsoft_translator', 'Microsoft Translator', 'Microsofts översättare', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:16', '2012-08-04 04:37:45', '2012-08-09 08:58:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('201', 'title_key', 'Key', 'Nyckel', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:17', '2012-08-04 04:37:45', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('202', 'settings_key_title:ajax_checkout_enabled', 'Enable AJAX Checkout', 'AJAX-utcheckning', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:17', '2012-08-13 23:08:47', '2012-07-12 13:16:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('203', 'settings_key_title:cache_enabled', 'Cache Enabled', 'Cache aktiverad', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:18', '2012-08-13 22:43:04', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('204', 'settings_key_title:gzip_enabled', 'GZIP Enabled', 'GZIP aktiverad', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:20', '2012-08-04 04:37:45', '2012-08-21 07:50:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('205', 'settings_key_title:register_guests', 'Register Guests', 'Registrera gäster', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:21', '2012-08-13 23:09:58', '2012-08-20 08:31:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('206', 'settings_key_title:store_country_id', 'Store Country', 'Land', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:21', '2012-08-13 21:31:36', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('207', 'settings_key_title:store_email', 'Store Email', 'E-postadress', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:22', '2012-08-13 23:03:41', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('208', 'settings_key_title:store_link', 'Store Link', 'Butikslänk', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:23', '2012-08-13 21:57:05', '2012-08-14 08:40:49');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('209', 'settings_key_title:store_name', 'Store Name', 'Butiksnamn', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:23', '2012-08-04 04:47:43', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('210', 'settings_key_title:store_zone_id', 'Store Zone', 'Butikszon', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:24', '2012-08-04 04:47:43', '2012-08-09 18:57:44');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('211', 'settings_key_title:system_currency', 'System Currency', 'Valuta', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:25', '2012-08-13 21:28:39', '2012-08-17 04:29:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('212', 'settings_key_title:system_language', 'System Language', 'Språk', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:25', '2012-08-13 21:28:39', '2012-08-20 06:38:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('213', 'settings_key_title:system_length_class', 'System Length Class', 'Längdklass', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:26', '2012-08-15 01:59:57', '2012-08-17 04:29:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('214', 'settings_key_title:system_weight_class', 'System Weight Class', 'Viktklass', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 12:27:27', '2012-08-13 23:52:34', '2012-08-17 04:29:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('215', 'title_add_new_order', 'Add New Order', 'Lägg till ny order', '', '\'admin/index.php\',\'admin/orders.app/orders.php\',', '2012-07-11 12:27:29', '2012-08-13 23:24:49', '2012-08-14 00:10:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('216', 'title_customer_name', 'Customer Name', 'Namn', '', '\'admin/index.php\',\'admin/orders.app/orders.php\',\'admin/customers.app/customers.php\',\'admin/orders.widget/orders.php\',', '2012-07-11 12:27:30', '2012-08-13 23:44:24', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('217', 'settings_key_title:store_postal_address', 'Postal Address', 'Postadress', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:08:46', '2012-08-13 21:31:25', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('218', 'settings_key_description:store_postal_address', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:08:51', '2012-08-13 21:31:25', '2012-08-13 23:04:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('219', 'settings_key_title:store_tax_id', 'Tax ID', 'Momsregistreringsnr', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:12:56', '2012-08-13 23:05:35', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('220', 'settings_key_description:store_tax_id', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:13:00', '2012-08-13 22:14:09', '2012-08-13 23:05:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('221', 'title_address', 'Address', 'Adress', '', '\'printable_receipt.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',\'includes/printable_packing_slip.inc.php\',', '2012-07-11 17:18:22', '2012-08-04 05:04:16', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('222', 'title_tax_registration_id', 'Tax Registration ID', '', '', '\'printable_receipt.php\',\'includes/receipt.inc.php\',\'includes/printable_order_copy.inc.php\',', '2012-07-11 17:25:16', '2012-08-13 22:14:09', '2012-08-15 06:37:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('223', 'settings_group:store_info', 'Store Info', 'Butiksinformation', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-11 17:30:01', '2012-08-13 21:31:25', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('224', 'settings_group:general', 'General', 'Allmänt', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-11 17:31:17', '2012-08-13 23:16:49', '2012-08-20 06:58:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('225', 'settings_group_title:store_info', 'Store Info', 'Butiksinformation', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:31:18', '2012-08-13 21:31:25', '2012-08-09 09:20:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('226', 'settings_key_title:store_phone', 'Phone Number', 'Telefonnr', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-11 17:31:18', '2012-08-13 23:04:25', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('227', 'text_please_select_a_payment_option', '', 'Välj ett betalningsalternativ.', '', '\'checkout.php\',\'includes/controllers/order.inc.php\',', '2012-07-11 19:45:52', '2012-08-04 06:06:19', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('228', 'title_customer_info', 'Customer Info', 'Kundinformation', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',', '2012-07-11 21:01:00', '2012-08-13 23:44:24', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('229', 'text_insert_before', 'Insert before', 'Infoga före', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-11 21:01:01', '2012-08-16 05:12:02', '2012-08-21 16:55:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('230', 'title', '', '', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',', '2012-07-11 21:01:03', '2012-08-16 05:12:02', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('231', 'title_insert_', 'Insert', 'Infoga', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',', '2012-07-11 21:01:04', '2012-08-16 05:12:02', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('232', 'title_uncompleted', 'Uncompleted', 'Ofullständig', '', '\'admin/index.php\',\'admin/orders.app/orders.php\',', '2012-07-11 21:33:52', '2012-08-15 03:02:57', '2012-08-20 08:42:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('233', 'title_add_new_order_status', 'Add New Order Status', 'Lägg till ny orderstatus', '', '\'admin/index.php\',\'admin/orders.app/order_statuses.php\',', '2012-07-11 21:39:11', '2012-08-14 00:04:12', '2012-08-14 00:12:03');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('772', 'job_error_reporter:description_status', 'Enables or disables the module.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('235', 'error_login_incorrect', 'Wrong e-mail and password combination or the account does not exist.', 'Felaktig e-post och lösenordskombination eller så existerar inte kontot.', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:48:19', '2012-08-13 23:03:41', '2012-07-30 03:56:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('236', 'error_missing_login_credentials', 'You must provide both e-mail and password.', 'Du måste ange både e-post och lösenord.', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:49:57', '2012-08-13 23:03:40', '2012-07-30 04:06:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('237', 'email_body_password_reset', 'We have set a new password for your account.

Login: %email
Password: %password

%store_link', 'Vi har satt ett nytt lösenord för ditt konto.

Inlogg: %email
Lösenord: %password

%store_link', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:50:37', '2012-08-13 21:31:25', '2012-07-30 03:55:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('238', 'email_subject_new_password', 'New Password', 'Nytt lösenord', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:50:38', '2012-08-04 06:07:19', '2012-07-30 03:55:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('239', 'success_password_reset', 'A new password has been sent to your e-mail address.', '', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:50:39', '2012-07-12 12:50:39', '2012-07-30 03:55:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('240', 'error_missing_email', 'To reset your password you must provide an e-mail address.', 'För att återställa lösenord måste du ange en e-postadress.', '', '\'index.php\',\'includes/library/customer.inc.php\',', '2012-07-12 12:51:14', '2012-08-13 23:10:46', '2012-08-01 05:55:16');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('241', 'settings_group_title:defaults', 'Defaults', 'Standardvärden', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 21:42:25', '2012-08-09 09:18:34');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('242', 'settings_key_title:default_country', 'Default Country', 'Land', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 21:32:17', '2012-08-20 07:04:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('243', 'settings_key_title:default_currency', 'Default Currency', 'Valuta', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 21:33:01', '2012-08-20 07:04:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('244', 'settings_key_title:default_language', 'Default Language', 'Språk', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 21:32:51', '2012-08-20 07:04:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('245', 'settings_key_title:default_zone', 'Default Zone', 'Zon', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 21:32:51', '2012-08-20 07:04:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('246', 'settings_key_title:display_prices_including_tax', 'Display Prices Including Tax', 'Visa priser inklusive moms', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:28', '2012-08-13 22:14:09', '2012-08-20 07:04:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('247', 'settings_key_description:display_prices_including_tax', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-12 13:16:31', '2012-08-13 22:14:09', '2012-08-13 23:07:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('248', 'title_edit_module', 'Edit Module', 'Redigera modul', '', '\'admin/index.php\',\'admin/modules.app/edit_module.php\',', '2012-07-12 13:31:27', '2012-08-13 23:39:23', '2012-08-21 18:20:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('249', 'title_uninstall', 'Uninstall', 'Avinstallera', '', '\'admin/index.php\',\'admin/modules.app/edit_module.php\',', '2012-07-12 13:31:27', '2012-08-04 04:51:10', '2012-08-21 18:20:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('780', 'title_packing_slip', 'Packing Slip', '', '', '\'admin/orders.app/printable_packing_slip.php\',\'includes/printable_packing_slip.inc.php\',', '2012-08-16 08:24:40', '2012-08-16 08:24:40', '2012-08-16 08:28:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('779', 'job_error_reporter:description_email_receipient', 'The e-mail address where reports will be sent.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:40:17', '2012-08-16 06:40:17', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('778', 'job_error_reporter:title_email_receipient', 'E-mail Receipient', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:40:17', '2012-08-16 06:40:17', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('777', 'job_error_reporter:description_priority', 'Process this module in the given priority order.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('264', 'title_free_shipping', 'Free Shipping', 'Gratis frakt', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/free.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-07-12 14:57:28', '2012-08-04 05:26:05', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('265', 'title_free', 'Free', 'Gratis', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/free.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-07-12 14:57:28', '2012-08-04 05:26:05', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('768', 'error_sending_email_for_unknown_reason', 'The e-mail could not be sent for an unknown reason', '', '', '\'support.php\',', '2012-08-16 06:04:49', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('767', 'success_your_email_was_sent', 'Your e-mail has successfully been sent', '', '', '\'support.php\',', '2012-08-16 06:04:49', '0000-00-00 00:00:00', '2012-08-18 07:51:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('765', 'title_days', 'Days', 'Dagar', '', '\'admin/index.php\',\'admin/sales.widget/sales.php\',', '2012-08-16 03:50:15', '2012-08-16 05:08:32', '2012-08-16 04:30:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('766', 'title_s_days', '%s days', '%s dagar', '', '\'admin/index.php\',\'admin/sales.widget/sales.php\',', '2012-08-16 03:54:10', '2012-08-16 05:08:32', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('764', 'title_sales', 'Sales', 'Försäljning', '', '\'admin/index.php\',\'admin/sales.widget/config.inc.php\',', '2012-08-16 03:30:28', '2012-08-16 05:08:32', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('275', 'text_net_price_tooltip', 'The net price field only helps you calculate price. All prices input to database are always excluding tax.', 'Nettofältet hjälper dig beräkna pris. Alla priser sparade i databasen är alltid exkl. moms.', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-12 18:04:27', '2012-08-13 23:11:44', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('276', 'title_order_completed', 'Your order was completed successfully!!', 'Din beställning har slutförts!', '', '\'order_success.php\',\'order_success.php\',', '2012-07-12 22:25:45', '2012-08-13 22:19:25', '2012-08-20 08:33:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('277', 'description_order_completed', 'Thank you for shopping in our store. We will process your order shortly.', 'Tack för att du handlar i vår butik. Vi kommer behandla din order inom kort.', '', '\'order_success.php\',\'order_success.php\',', '2012-07-12 22:25:45', '2012-08-13 22:19:25', '2012-08-20 08:33:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('278', 'title_boxes', 'Boxes', 'Boxar', '', '\'admin/index.php\',\'admin/boxes.app/config.inc.php\',', '2012-07-13 17:15:15', '2012-08-04 06:05:09', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('279', 'title_custom_box', 'Custom Box', 'Custom Box', '', '\'admin/index.php\',', '2012-07-13 17:15:17', '2012-08-04 06:05:09', '2012-08-09 12:57:25');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('280', 'title_edit_custom_box', 'Edit Custom Box', 'Redigera Custom Box', '', '\'admin/index.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/users.app/edit_user.php\',', '2012-07-13 17:15:27', '2012-08-13 23:39:23', '2012-08-20 10:44:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('281', 'title_title', 'Title', 'Titel', '', '\'admin/index.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/pages.app/pages.php\',\'admin/pages.app/edit_page.php\',\'admin/catalog.app/edit_product_configuration_group.php\',', '2012-07-13 17:15:28', '2012-08-04 04:52:47', '2012-08-20 10:44:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('282', 'title_content', 'Content', 'Innehåll', '', '\'admin/index.php\',\'admin/boxes.app/edit_custom_box.php\',\'admin/pages.app/edit_page.php\',', '2012-07-13 17:15:28', '2012-08-04 04:52:47', '2012-08-20 10:44:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('283', 'title_choose', 'Choose', 'Välja', '', '\'admin/index.php\',\'admin/translations.app/pages.php\',', '2012-07-13 18:54:40', '2012-08-04 05:38:39', '2012-08-16 06:37:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('284', 'text_edit_all_on_page', 'Edit all on page', 'Redigera allt på sidan', '', '\'admin/index.php\',\'admin/translations.app/pages.php\',', '2012-07-13 18:57:24', '2012-08-13 23:39:23', '2012-08-14 00:40:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('285', 'title_csv_import_export', 'CSV Import/Export', 'Importera / Exportera', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-13 19:59:03', '2012-08-14 00:46:31', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('286', 'title_export_languages_to_csv', 'Export Languages to CSV', 'Exportera språk till CSV', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-07-13 19:59:15', '2012-08-14 00:46:31', '2012-07-13 20:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('287', 'title_language', 'Language', 'Spårk', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',\'select_region.php\',\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-07-13 19:59:16', '2012-08-04 05:01:51', '2012-08-21 06:44:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('288', 'title_left', 'Left', 'Vänster', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-07-13 19:59:16', '2012-08-04 05:27:49', '2012-07-13 21:16:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('289', 'title_right', 'Right', 'Höger', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-07-13 19:59:17', '2012-08-04 05:27:49', '2012-07-13 21:16:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('290', 'title_export', 'Export', 'Exportera', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-07-13 19:59:18', '2012-08-14 00:46:17', '2012-08-20 20:12:16');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('291', 'title_csv_file', 'CSV File', 'CSV-fil', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-07-13 20:04:49', '2012-08-14 00:46:31', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('292', 'title_import', 'Import', 'Importera', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-07-13 20:04:50', '2012-08-04 05:18:14', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('293', 'title_import_translations_to_csv', 'Import Translations From CSV', 'Importera översättningar från CSV', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-07-13 20:06:46', '2012-08-14 00:46:31', '2012-08-14 00:41:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('294', 'title_export_translations_to_csv', 'Export Translations To CSV', 'Exportera översättningar till CSV', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-07-13 20:06:47', '2012-08-14 00:46:31', '2012-08-14 00:41:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('295', 'title_example', 'Example', 'Exempel', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-07-13 20:10:03', '2012-08-04 05:10:29', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('296', 'title_add_new_option_group', 'Add New Option Group', 'Lägg till ny grupp', '', '\'admin/index.php\',\'admin/catalog.app/option_groups.php\',', '2012-07-15 18:20:18', '2012-08-14 00:04:12', '2012-08-13 23:27:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('297', 'title_values', 'Values', 'Värden', '', '\'admin/index.php\',\'admin/catalog.app/option_groups.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/product_groups.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/catalog.app/product_configuration_groups.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/product_option_groups.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-15 18:20:18', '2012-08-13 21:42:25', '2012-08-20 10:17:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('298', 'title_new_group', 'Create New Group', 'Skapa en ny grupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',', '2012-07-15 18:20:22', '2012-08-14 00:10:17', '2012-07-15 19:19:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('299', 'title_add_group', 'Add Group Value', 'Lägg till gruppvärde', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-15 19:05:12', '2012-08-14 00:04:12', '2012-08-16 07:49:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('300', 'title_edit_group', 'Edit Group', 'Redigera grupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',', '2012-07-15 19:24:17', '2012-08-13 23:39:23', '2012-08-13 23:27:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('301', 'title_products', 'Products', 'Produkter', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/product_groups.php\',\'admin/catalog.app/edit_product_group.php\',\'admin/catalog.app/edit_product_configuration_group.php\',\'admin/catalog.app/edit_product_option_group.php\',', '2012-07-15 19:28:25', '2012-08-04 04:52:47', '2012-08-16 07:49:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('302', 'title_add_new_manufacturer', 'Add New Manufacturer', 'Lägg till ny tillverkare', '', '\'admin/index.php\',\'admin/catalog.app/manufacturers.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-15 20:46:52', '2012-08-14 00:04:12', '2012-08-20 18:46:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('303', 'title_edit_manufacturer', 'Edit Manufacturer', 'Redigera tillverkare', '', '\'admin/index.php\',\'admin/catalog.app/edit_manufacturer.php\',', '2012-07-15 20:46:55', '2012-08-13 23:39:23', '2012-08-20 18:46:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('304', 'title_link', 'Link', 'Länk', '', '\'admin/index.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/catalog.app/edit_supplier.php\',', '2012-07-15 20:46:55', '2012-08-13 21:57:05', '2012-08-20 18:46:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('305', 'title_suppliers', 'Suppliers', 'Leverantörer', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-07-15 21:36:24', '2012-08-04 05:07:25', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('306', 'title_add_new_supplier', 'Add New Supplier', 'Lägg till ny leverantör', '', '\'admin/index.php\',\'admin/catalog.app/suppliers.php\',\'admin/catalog.app/edit_supplier.php\',', '2012-07-15 21:36:30', '2012-08-14 00:04:12', '2012-08-20 18:46:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('307', 'title_edit_supplier', 'Edit Supplier', 'Redigera leverantör', '', '\'admin/index.php\',\'admin/catalog.app/edit_supplier.php\',', '2012-07-15 21:48:01', '2012-08-13 23:39:23', '2012-08-16 07:49:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('308', 'title_supplier', 'Supplier', 'Leverantör', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-15 21:51:03', '2012-08-04 06:06:56', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('309', 'title_my_account', 'My Account', 'Mitt konto', '', '\'edit_account.php\',\'edit_account.php\',\'order_history.php\',', '2012-07-15 23:32:21', '2012-08-04 05:14:42', '2012-08-15 07:13:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('310', 'title_print', 'Print', 'Skriv ut', '', '\'order_history.php\',\'order_history.php\',\'includes/template/printable_default.inc.php\',', '2012-07-15 23:45:38', '2012-08-04 04:46:36', '2012-08-15 03:18:05');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('311', 'title_stock_qty', 'In Stock', 'I lager', '', '\'product.php\',\'product.php\',', '2012-07-17 01:16:29', '2012-08-04 05:27:49', '2012-07-17 01:17:14');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('312', 'title_stock_status', 'Stock Status', 'Lagerstatus', '', '\'product.php\',\'product.php\',', '2012-07-17 01:20:00', '2012-08-13 23:38:44', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('313', 'title_in_stock', 'In Stock', 'I lager', '', '\'product.php\',\'product.php\',', '2012-07-17 01:21:51', '2012-08-04 05:30:25', '2012-08-08 06:21:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('314', 'title_sold_out', 'Sold Out', 'Slutsåld', '', '\'product.php\',\'product.php\',', '2012-07-17 01:24:38', '2012-08-09 18:49:05', '2012-08-15 02:36:28');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('315', 'title_add_new_currency', 'Add New Currency', 'Lägg till ny valuta', '', '\'admin/index.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-17 03:23:49', '2012-08-14 00:04:12', '2012-08-20 07:16:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('316', 'title_prefix', 'Prefix', 'Prefix', '', '\'admin/index.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-17 03:23:50', '2012-08-04 04:46:36', '2012-08-20 07:16:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('317', 'title_suffix', 'Suffix', 'Suffix', '', '\'admin/index.php\',\'admin/currencies.app/currencies.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-17 03:23:50', '2012-08-04 04:51:10', '2012-08-20 07:16:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('318', 'title_edit_currency', 'Edit Currency', 'Redigera valuta', '', '\'admin/index.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-17 03:23:55', '2012-08-13 23:39:23', '2012-08-16 07:51:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('319', 'title_decimals', 'Decimals', 'Decimaler', '', '\'admin/index.php\',\'admin/currencies.app/edit_currency.php\',', '2012-07-17 03:23:56', '2012-08-13 22:27:29', '2012-08-16 07:51:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('320', 'title_add_new_tax_rate', 'Add New Tax Rate', 'Lägg till ny momssats', '', '\'admin/index.php\',\'admin/tax.app/tax_rates.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:24:55', '2012-08-14 00:04:12', '2012-08-16 07:47:53');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('321', 'title_geo_zone', 'Geo Zone', 'Geografisk zon', '', '\'admin/index.php\',\'admin/tax.app/tax_rates.php\',\'admin/tax.app/edit_tax_rate.php\',\'includes/modules/payment/cod.inc.php\',', '2012-07-17 03:24:56', '2012-08-04 05:24:24', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('322', 'title_type', 'Type', 'Typ', '', '\'admin/index.php\',\'admin/tax.app/tax_rates.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-17 03:24:57', '2012-08-04 05:24:24', '2012-08-16 07:47:53');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('323', 'title_rate', 'Rate', 'Sats', '', '\'admin/index.php\',\'admin/tax.app/tax_rates.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:24:57', '2012-08-13 23:54:35', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('324', 'title_edit_tax_rate', 'Edit Tax Rate', 'Redigera momssats', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:55', '2012-08-13 23:54:35', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('325', 'title_customer_type', 'Customer Type', 'Kundtyp', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:56', '2012-08-13 23:44:24', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('326', 'title_individuals', 'Individuals', 'Privatpersoner', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:57', '2012-08-04 04:55:46', '2012-07-17 10:16:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('327', 'title_companies', 'Companies', 'Företag', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:57', '2012-08-04 04:55:46', '2012-07-17 10:16:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('328', 'text_both_of_the_above', 'Both of the above.', 'Båda ovan.', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:58', '2012-08-04 04:55:46', '2012-07-17 10:16:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('329', 'title_rule', 'Rule', 'Regel', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:59', '2012-08-04 04:55:46', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('330', 'description_customer_with_tax_id', 'Applies to customers with a tax ID.', '', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:54:59', '2012-08-13 23:44:24', '2012-07-17 10:16:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('331', 'description_customers_without_tax_id', 'Applies to customers without a tax ID.', '', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 03:55:00', '2012-08-13 23:44:24', '2012-07-17 10:16:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('332', 'title_add_new_geo_zone', 'Add New Geo Zone', 'Lägg till ny geografisk zon', '', '\'admin/index.php\',\'admin/geo_zones.app/geo_zones.php\',', '2012-07-17 04:33:38', '2012-08-14 00:04:12', '2012-08-21 10:05:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('333', 'title_all_zones', 'All Zones', 'Alla zoner', '', '\'admin/index.php\',\'includes/controllers/geo_zone.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/countries.app/edit_country.php\',\'includes/controllers/geo_zone.inc.php\',', '2012-07-17 04:33:41', '2012-08-04 05:37:37', '2012-08-21 10:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('334', 'title_new_geo_zone', 'Create New Geo Zone', 'Skapa till ny geografisk zon', '', '\'admin/index.php\',\'admin/geo_zones.app/edit_geo_zone.php\',', '2012-07-17 04:33:42', '2012-08-14 00:10:17', '2012-08-13 22:58:28');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('335', 'title_add', 'Add', 'Lägg till', '', '\'admin/index.php\',\'admin/geo_zones.app/edit_geo_zone.php\',\'admin/countries.app/edit_country.php\',', '2012-07-17 04:33:43', '2012-08-14 00:04:12', '2012-08-21 10:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('336', 'text_duty_free_or_no_country_zone_set', 'Duty free or no country/zone set.', '', '', '\'product.php\',\'product.php\',', '2012-07-17 05:14:03', '2012-08-04 06:07:19', '2012-08-01 06:08:45');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('337', 'title_add_new_tax_class', 'Add New Tax Class', 'Lägg till ny momsklass', '', '\'admin/index.php\',\'admin/tax.app/tax_classes.php\',\'admin/tax.app/edit_tax_class.php\',', '2012-07-17 10:12:07', '2012-08-14 00:04:12', '2012-08-20 10:05:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('338', 'title_tax_classs', 'Tax Classes', 'Momsklasser', '', '\'admin/index.php\',\'admin/tax.app/tax_classes.php\',', '2012-07-17 10:12:08', '2012-08-13 22:14:09', '2012-08-20 10:05:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('339', 'text_tax_rate_rule_individuals', 'Applies to individuals', 'Gäller privatpersoner', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 10:18:57', '2012-08-13 23:54:35', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('340', 'text_tax_rate_rule_companies', 'Applies to companies', 'Gäller företag', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 10:18:58', '2012-08-13 23:54:35', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('341', 'text_tax_rate_rule_both_of_the_above', 'Applies to both of above', 'Gäller båda ovan', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 10:18:59', '2012-08-13 23:54:35', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('342', 'text_tax_rate_rule_customers_with_tax_id', 'Applies to customers with a tax ID', 'Gäller kunder med momsregistreringsnr', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 10:19:00', '2012-08-14 00:06:13', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('343', 'text_tax_rate_rule_customers_without_tax_id', 'Applies to customers without a tax ID', 'Gäller kunder utan momsregistreringsnr', '', '\'admin/index.php\',\'admin/tax.app/edit_tax_rate.php\',', '2012-07-17 10:19:02', '2012-08-14 00:06:13', '2012-08-16 07:47:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('344', 'title_admin_panel', 'Admin Panel', 'Adminpanel', '', '\'admin/index.php\',\'admin/index.php\',', '2012-07-18 03:10:14', '2012-08-04 04:15:22', '2012-08-21 18:20:49');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('431', 'title_scan_files', 'Scan Files', 'Skanna filer', '', '\'admin/index.php\',\'admin/translations.app/config.inc.php\',', '2012-07-22 06:09:45', '2012-08-04 04:15:22', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('432', 'title_scan_files_for_translations', 'Scan Files for Translations', 'Skanna filer för översättningar', '', '\'admin/index.php\',\'admin/translations.app/scan.php\',', '2012-07-22 06:10:39', '2012-08-14 00:38:54', '2012-07-22 06:15:28');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('346', 'warning_ie_browser', 'You are using an old web browser. For your best shopping experience upgrade your web browser or use %s.', 'Du använder en gammal webbläsare. För bästa shoppingupplevelse, uppgradera din webbläsare eller använd %s.', '', '\'index.php\',\'includes/library/document.inc.php\',', '2012-07-18 23:27:38', '2012-08-04 05:07:25', '2012-08-15 05:35:11');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('347', 'title_order_success', 'Order Success', 'Order slutförd', '', '\'order_success.php\',\'order_success.php\',\'admin/modules.app/config.inc.php\',', '2012-07-20 03:46:12', '2012-08-15 07:10:16', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('348', 'description_click_printable_copy', 'Click here for a printable copy.', 'Klicka här för en utskriftsbar kopia.', '', '\'order_success.php\',\'order_success.php\',', '2012-07-20 03:56:30', '2012-08-15 03:02:04', '2012-08-20 08:33:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('349', 'error_email_already_registered', 'The e-mail address already exists in our customer database.', 'E-postadressen existerar redan i vår kunddatabas.', '', '', '2012-07-22 05:00:48', '2012-08-13 23:44:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('350', 'error_email_missing', 'You must enter an e-mail address.', 'Du måste ange en e-postadress.', '', '', '2012-07-22 05:00:48', '2012-08-13 23:03:40', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('351', 'error_missing_firstname', 'You must enter a first name.', 'Du måste ange ett förnamn.', '', '', '2012-07-22 05:00:48', '2012-08-04 05:12:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('352', 'error_missing_lastname', 'You must enter a last name.', 'Du måste ange ett efternamn.', '', '', '2012-07-22 05:00:48', '2012-08-04 05:12:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('353', 'error_missing_address1', 'You must enter an address.', 'Du måste ange en adress.', '', '', '2012-07-22 05:00:48', '2012-08-04 05:12:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('354', 'error_missing_city', 'You must enter a city.', 'Du måste ange en stad.', '', '', '2012-07-22 05:00:48', '2012-08-04 05:12:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('355', 'error_missing_postcode', 'You must enter a postcode.', 'Du måste ange en postnummer.', '', '', '2012-07-22 05:00:48', '2012-08-13 23:46:42', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('771', 'job_error_reporter:title_status', 'Status', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('358', 'title_logged_out', 'Logged Out', 'Utloggad', '', '', '2012-07-22 05:01:38', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('359', 'description_logged_out', 'You are now logged out.', 'Du har nu loggats ut.', '', '', '2012-07-22 05:01:38', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('360', 'text_no_products_for_manufacturer', 'There are currently no products by this manufacturer in stock.', 'Det finns för närvarande inga produkter från denna tillverkare i lager.', '', '', '2012-07-22 05:01:38', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('361', 'title_excluding_tax', 'Excluding Tax', 'Exklusive moms', '', '', '2012-07-22 05:01:39', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('362', 'title_search_results_for_s', 'Search Results for &quot;%s&quot;', 'Sökresultat för &quot;%s&quot;', '', '\'search.php\',', '2012-07-22 05:01:39', '2012-08-04 04:43:25', '2012-08-09 01:31:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('363', 'text_no_products_found_for_search_string', 'No products found for search string.', 'Inga produkter hittades för söksträngen.', '', '\'search.php\',', '2012-07-22 05:01:39', '2012-08-04 05:36:39', '2012-08-08 05:49:33');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('364', 'error_delete_category_not_empty_subcategories', 'The category could not be deleted because there are subcategories linked to it.', 'Kategorin kunde inte tas bort eftersom det finns underkategorier kopplade till den.', '', '', '2012-07-22 05:09:46', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('365', 'error_delete_manufacturer_not_empty_products', 'The manufacturer could not be deleted because there are products linked to it.', 'Tillverkaren kunde inte tas bort eftersom det finns produkter kopplade till den.', '', '', '2012-07-22 05:09:46', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('366', 'text_please_select_a_shipping_option', '', 'Välj ett leveransalternativ.', '', '', '2012-07-22 05:09:46', '2012-08-04 06:11:43', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('367', 'error_delete_supplier_not_empty_products', 'The supplier could not be deleted because there are products linked to it.', 'Leverantören kunde inte tas bort eftersom det finns produkter kopplade till den.', '', '', '2012-07-22 05:09:46', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('368', 'error_invalid_shipping_option', 'Cannot set an invalid shipping option.', 'Kan inte ställa in ett ogiltigt leveransalternativ.', '', '', '2012-07-22 05:09:46', '2012-08-04 05:36:39', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('369', 'error_invalid_payment_option', 'Cannot set an invalid payment option.', 'Kan inte ställa in ett ogiltigt betalningsalternativ.', '', '', '2012-07-22 05:09:46', '2012-08-04 05:45:59', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('370', 'error_receipient_domain', 'Invalid recipient domain: %domain', 'Ogiltig mottagardomän: %domain', '', '', '2012-07-22 05:09:46', '2012-08-04 05:47:21', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('371', 'error_email_invalid', 'Invalid e-mail addess.', 'Felaktig e-postadress.', '', '', '2012-07-22 05:09:46', '2012-08-13 23:03:40', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('372', 'error_message_empty', 'You must enter a message.', 'Du måste skriva ett meddelande.', '', '', '2012-07-22 05:09:46', '2012-08-04 06:09:04', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('373', 'error_message_tags', 'HTML tags are not allowed in the message.', 'HTML-taggar är inte tillåtna i meddelandet.', '', '', '2012-07-22 05:09:46', '2012-08-04 06:09:53', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('374', 'error_captcha_invalid', 'Invalid CAPTCHA-code given.', 'Felaktig CAPTCHA-kod angiven.', '', '', '2012-07-22 05:09:46', '2012-08-13 21:54:48', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('375', 'success_email_sent', 'You e-mail has been sent.', 'Ditt e-postmeddelande har skickats.', '', '', '2012-07-22 05:09:46', '2012-08-13 23:03:40', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('376', 'error_sendmail_failed', 'Failed sending e-mail', 'Misslyckades att skicka e-postmeddelandet.', '', '', '2012-07-22 05:09:46', '2012-08-13 23:03:40', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('377', 'error_missing_product_option', '', '', '', '\'includes/library/cart.inc.php\',', '2012-07-22 05:09:47', '2012-08-04 06:07:19', '2012-08-05 05:06:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('378', 'title_reset', 'Reset', 'Återställ', '', '', '2012-07-22 05:09:47', '2012-08-13 23:10:46', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('379', 'title_index', 'Index', 'Index', '', '', '2012-07-22 05:09:47', '2012-08-04 06:10:57', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('380', 'description_including_tax', 'Including tax', 'Inklusive moms', '', '', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('381', 'description_excluding_tax', 'Excluding tax', 'Exklusive moms', '', '', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('382', 'text_cart_contains_x_items', 'Your cart contains %d items', 'Din varukorg innehåller %d produkter', '', '', '2012-07-22 05:09:47', '2012-08-04 06:11:43', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('383', 'title_clear', 'Clear', 'Rensa', '', '', '2012-07-22 05:09:47', '2012-08-13 23:11:02', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('384', 'checkout.php/error_email_already_registered', '', '', '', '\'includes/checkout/customer.php\',', '2012-07-22 05:09:47', '2012-08-04 06:07:59', '2012-08-20 08:33:11');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('385', 'checkout.php/error_email_missing', 'You must enter your e-mail address.', 'Du måste ange din e-postadress.', '', '', '2012-07-22 05:09:47', '2012-08-13 23:03:40', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('386', 'checkout.php/error_missing_password', 'You must enter a password.', 'Du måste ange ett lösenord.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('387', 'checkout.php/error_missing_confirmed_password', 'You must confirm your password.', 'Du måste bekräfta ditt lösenord.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('388', 'checkout.php/error_passwords_missmatch', 'The passwords did not match.', 'Lösenorden matchar inte.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('389', 'checkout.php/error_missing_firstname', 'You must enter a first name.', 'Du måste ange ett förnamn.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('390', 'checkout.php/error_missing_lastname', 'You must enter a last name.', 'Du måste ange ett efternamn.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('391', 'checkout.php/error_missing_address1', 'You must enter an address.', 'Du måste ange en adress', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('392', 'checkout.php/error_missing_city', 'You must enter a city.', 'Du måste ange en stad.', '', '', '2012-07-22 05:09:47', '2012-08-04 05:54:24', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('393', 'checkout.php/error_missing_postcode', 'You must enter a postcode.', 'Du måste ange ett postnummer.', '', '', '2012-07-22 05:09:47', '2012-08-13 23:46:42', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('394', 'checkout.php/error_missing_country', '', '', '', '', '2012-07-22 05:09:47', '2012-08-09 18:42:03', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('395', 'checkout.php/error_missing_zone', '', '', '', '', '2012-07-22 05:09:47', '2012-08-09 18:42:03', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('396', 'email_subject_account_created', 'Welcome %customer_firstname %customer_lastname to %store_name!

Your account has been created. You can now make purchases in our online store and keep track of history.rnrnLogin using your e-mail address %customer_email and password %customer_password.rnrn%store_namernrn%store_link', 'Välkommen %customer_firstname %customer_lastname till %store_name!

Ditt konto är skapat. Du kan nu genomföra köp i vårwebbutik och samordna orderhistorik.

Logga in med din e-postadress %customer_email och lösenord %customer_password.

%store_name

%store_link', '', '', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('397', 'email_subject_customer_account_created', 'Customer Account Created', 'Kundkonto skapat', '', '', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('398', 'success_account_has_been_created', 'A customer account has been created that will let you keep track of orders.', 'Ett kundkonto har skapats som kommer att låta dig hålla koll på beställningar.', '', '', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('762', 'title_captcha', 'CAPTCHA', 'CAPTCHA', '', '\'support.php\',\'support.php\',', '2012-08-15 22:56:14', '2012-08-16 05:08:32', '2012-08-20 09:17:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('763', 'error_must_enter_email', 'You must enter a valid e-mail address', 'Du måste ange en giltig e-postadress', '', '\'support.php\',\'support.php\',', '2012-08-15 22:59:59', '2012-08-16 05:08:32', '2012-08-15 22:59:59');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('400', 'title_including_tax_sprintf', 'Including Tax (%s)', 'Inklusive moms (%s)', '', '', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('401', 'title_tax_sprintf', 'Tax (%s)', 'Moms (%s)', '', '', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('402', 'title_flat_rate', 'Flat Rate', 'Fast pris', '', '\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-07-22 05:09:47', '2012-08-13 23:54:35', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('403', 'title_edit_customer', 'Edit Customer Profile', 'Redigera kundprofil', '', '\'admin/customers.app/edit_customer.php\',', '2012-07-22 05:09:47', '2012-08-13 23:44:24', '2012-08-13 23:45:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('404', 'title_create_new_customer_profile', 'Create New Customer Profile', 'Skapa ny kund', '', '\'admin/customers.app/edit_customer.php\',', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '2012-08-13 23:42:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('405', 'title_add_new_customer', 'Add New Customer', 'Lägg till ny kund', '', '\'admin/customers.app/customers.php\',', '2012-07-22 05:09:47', '2012-08-14 00:04:12', '2012-08-20 10:17:11');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('406', 'title_registered', 'Registered', 'Registrerad', '', '\'admin/customers.app/customers.php\',', '2012-07-22 05:09:47', '2012-08-13 23:09:58', '2012-08-11 16:05:53');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('407', 'text_no_entries_in_database', 'There are no entries in the database.', 'Det finns inga inlägg i databasen.', '', '\'admin/orders.app/order_statuses.php\',\'admin/catalog.app/delivery_statuses.php\',\'admin/catalog.app/sold_out_statuses.php\',\'admin/settings.app/settings.php\',\'admin/pages.app/pages.php\',', '2012-07-22 05:09:47', '2012-08-14 00:04:12', '2012-08-20 06:58:25');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('408', 'error_missing_geo_zone', '', 'Du måste välja en geografisk zon.', '', '', '2012-07-22 05:09:47', '2012-08-09 18:42:03', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('409', 'error_missing_tax_class', '', 'Du måste välja en momsklass.', '', '', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('410', 'error_missing_rate', 'You must enter a rate.', 'Du måste ange en sats.', '', '', '2012-07-22 05:09:47', '2012-08-13 23:54:35', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('411', 'title_create_new_tax_rate', 'Create New Tax Rate', 'Skapa ny momssats', '', '\'admin/tax.app/edit_tax_rate.php\',', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '2012-08-14 00:02:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('412', 'title_edit_tax_class', 'Edit Tax Class', 'Redigera momsklass', '', '\'admin/tax.app/edit_tax_class.php\',', '2012-07-22 05:09:47', '2012-08-13 23:39:23', '2012-08-16 07:47:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('413', 'title_create_new_tax_class', 'Create New Tax Class', 'Skapa en ny momsklass', '', '', '2012-07-22 05:09:47', '2012-08-14 00:10:17', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('414', 'error_missing_code', 'You must enter a code.', 'Du måste ange en kod.', '', '', '2012-07-22 05:09:47', '2012-08-13 21:54:48', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('415', 'title_edit_country', 'Edit Country', 'Redigera land', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-13 23:39:23', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('416', 'title_add_new_country', 'Add New Country', 'Lägg till nytt land', '', '\'admin/countries.app/countries.php\',\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-14 00:04:12', '2012-08-21 10:07:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('417', 'title_domestic_name', 'Domestic Name', 'Inhemskt namn', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-13 23:48:04', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('418', 'title_address_format', 'Address Format', 'Adressformat', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-04 05:51:05', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('419', 'title_syntax', 'Syntax', 'Syntax', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-13 22:05:17', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('420', 'title_postcode_required', 'Postcode Required', 'Postnummer obligatoriskt', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-13 23:47:36', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('421', 'title_yes', 'Yes', 'Ja', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-04 05:51:05', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('422', 'title_no', 'No', 'Nej', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-04 06:14:49', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('423', 'title_currency_code', 'Currency Code', 'Valutakod', '', '\'admin/countries.app/edit_country.php\',\'admin/languages.app/edit_language.php\',', '2012-07-22 05:09:47', '2012-08-13 21:54:48', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('424', 'title_phone_country_code', 'Phone Country Code', 'Landsnummer', '', '\'admin/countries.app/edit_country.php\',', '2012-07-22 05:09:47', '2012-08-04 06:16:14', '2012-08-21 10:02:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('425', 'text_no_entries_found_in_database', 'No entries found in database', 'Inga uppgifter finns i databasen', '', '\'admin/translations.app/search.php\',', '2012-07-22 05:09:47', '2012-08-13 23:11:44', '2012-08-17 02:57:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('426', 'success_translations_imported', 'Translations successfully imported.', 'Översättningar har importerats.', '', '\'admin/translations.app/csv.php\',', '2012-07-22 05:09:47', '2012-08-14 00:38:54', '2012-08-08 04:08:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('427', 'title_edit_order_status', 'Edit Order Status', 'Redigera orderstatus', '', '\'admin/orders.app/edit_order_status.php\',', '2012-07-22 05:09:47', '2012-08-13 23:39:23', '2012-08-20 07:49:03');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('428', 'title_create_new_order_status', 'Create New Order Status', 'Skapa ny orderstatus', '', '\'admin/orders.app/order_statuses.php\',\'admin/orders.app/edit_order_status.php\',', '2012-07-22 05:09:47', '2012-08-14 00:15:40', '2012-08-20 08:13:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('429', 'title_edit_geo_zone', 'Edit Geo Zone', 'Redigera geografisk zon', '', '\'admin/geo_zones.app/edit_geo_zone.php\',', '2012-07-22 05:09:47', '2012-08-13 23:39:23', '2012-08-21 10:05:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('430', 'error_name_missing', 'You must enter a name.', 'Du måste ange ett namn.', '', '', '2012-07-22 05:09:48', '2012-08-04 06:16:50', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('439', 'title_add_new_product_group', 'Add New Product Group', 'Lägg till ny produktgrupp', '', '\'admin/index.php\',\'admin/catalog.app/product_groups.php\',\'admin/catalog.app/edit_product_group.php\',', '2012-07-23 08:01:07', '2012-08-14 00:04:12', '2012-08-13 23:22:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('437', 'text_found_d_new_translations', 'Found %d new translations', 'Hittade %d nya översättningar', '', '\'admin/translations.app/scan.php\',', '2012-07-22 09:27:22', '2012-08-14 00:38:54', '2012-08-16 06:04:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('436', 'text_found_d_translations', 'Found %d translations in %d files', 'Hittade %d nya översättningar i %d filer', '', '\'admin/translations.app/scan.php\',', '2012-07-22 09:27:22', '2012-08-14 00:38:54', '2012-08-16 06:04:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('438', 'title_product_groups', 'Product Groups', 'Produktgrupper', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-07-23 07:36:49', '2012-08-13 21:43:03', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('435', 'title_scan_files_for_new_translations', 'Scan Files for New Translations', 'Skanna filer för nya översättningar', '', '\'admin/index.php\',\'admin/translations.app/scan.php\',', '2012-07-22 06:16:30', '2012-08-14 00:38:54', '2012-08-16 06:04:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('440', 'title_edit_product_group', 'Edit Product Group', 'Redigera produktgrupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_product_group.php\',', '2012-07-23 08:03:13', '2012-08-13 23:39:23', '2012-08-16 07:48:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('441', 'title_add_image', 'Add Image', 'Lägg till bild', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-23 10:04:43', '2012-08-14 00:04:12', '2012-07-23 10:10:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('442', 'text_add', 'Add', 'Lägg till', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-07-23 10:13:17', '2012-08-14 00:04:12', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('443', 'title_order_summary', 'Order Summary', 'Ordersammanfattning', '', '\'checkout.php\',\'includes/checkout/summary.php\',', '2012-07-23 11:47:05', '2012-08-13 22:19:25', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('444', 'title_merchant_email', 'Merchant E-mail', '', '', '\'admin/index.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-07-23 14:45:53', '2012-08-04 05:59:38', '2012-08-14 08:23:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('445', 'title_gateway', 'Gateway', '', '', '\'admin/index.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-07-23 14:45:53', '2012-08-04 05:59:38', '2012-08-14 08:23:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('761', 'title_message', 'Message', 'Meddelande', '', '\'support.php\',\'support.php\',', '2012-08-15 22:56:14', '2012-08-16 05:08:32', '2012-08-20 09:17:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('447', 'title_head_title', 'Head Title', 'Sidtitel', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/pages.app/edit_page.php\',', '2012-07-25 14:32:53', '2012-08-14 00:22:58', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('448', 'title_meta_description', 'Meta Description', 'Metabeskrivning', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/pages.app/edit_page.php\',', '2012-07-25 14:32:53', '2012-08-04 05:18:14', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('449', 'title_meta_keywords', 'Meta Keywords', 'Metanyckelord', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_manufacturer.php\',\'admin/pages.app/edit_page.php\',', '2012-07-25 14:32:53', '2012-08-14 00:22:31', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('450', 'error_invalid_captcha', 'Invalid CAPTCHA given', 'Felaktig CAPTCHA angiven', '', '\'order_process.php\',\'order_process.php\',\'support.php\',', '2012-07-27 20:13:07', '2012-08-13 21:35:58', '2012-08-18 07:47:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('451', 'title_redirecting', 'Redirecting', 'Omdirigering', '', '\'order_process.php\',\'includes/classes/payment.inc.php\',', '2012-07-27 20:55:21', '2012-08-15 07:04:05', '2012-08-15 07:40:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('452', 'email_order_copy', 'Order Copy', 'Orderkopia', '', '\'order_process.php\',\'includes/controllers/order.inc.php\',', '2012-07-29 22:12:26', '2012-08-13 23:09:32', '2012-08-20 08:33:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('760', 'title_subject', 'Subject', 'Ämne', '', '\'support.php\',\'support.php\',', '2012-08-15 22:53:20', '2012-08-16 05:08:32', '2012-08-20 09:17:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('454', 'settings_group:checkout', 'Checkout', 'Kassa', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-07-30 06:01:53', '2012-08-13 21:33:47', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('455', 'settings_key_title:email_order_copy', 'Order Copy Recipients', 'Orderkopia', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:01:53', '2012-08-13 23:09:32', '2012-08-20 08:31:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('456', 'settings_key_title:seo_urls_enabled', 'SEO URLs Enabled', 'SEO-länkar aktiverade', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:01:53', '2012-08-13 23:14:07', '2012-08-12 17:21:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('457', 'settings_group_title:checkout', 'Checkout', 'Kassa', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:02:01', '2012-08-13 21:33:34', '2012-08-09 09:18:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('458', 'settings_key_title:checkout_ajax_enabled', 'Enable AJAX Checkout', 'AJAX-utcheckning', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:02:01', '2012-08-13 23:08:47', '2012-08-20 08:31:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('459', 'settings_key_title:checkout_captcha_enabled', 'Enable CAPTCHA', 'CAPTCHA', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:02:01', '2012-08-13 21:35:58', '2012-08-20 08:31:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('460', 'settings_key_title:store_keywords', 'Keywords', 'Nyckelord', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:02:05', '2012-08-14 00:22:31', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('461', 'settings_key_title:store_short_description', 'Short Description', 'Kortbeskrivning', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-07-30 06:02:05', '2012-08-13 21:31:25', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('462', 'title_calculate', 'Calculate', 'Beräkna', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',', '2012-07-31 01:58:26', '2012-08-04 05:19:58', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('463', 'title_select_region', 'Select Region', 'Välj region', '', '\'select_region.php\',\'select_region.php\',', '2012-08-01 01:19:54', '2012-08-04 04:51:10', '2012-08-01 02:52:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('464', 'title_currency', 'Currency', 'Valuta', '', '\'select_region.php\',\'select_region.php\',', '2012-08-01 01:58:44', '2012-08-04 05:04:16', '2012-08-21 06:44:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('465', 'title_order_success_modules', 'Order Success Modules', 'Moduler för orderslutförande', '', '\'admin/index.php\',\'admin/modules.app/modules.php\',\'admin/modules.app/config.inc.php\',', '2012-08-01 05:18:11', '2012-08-15 07:10:16', '2012-08-15 02:31:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('466', 'adwords:title_conversion_id', 'Conversion ID', 'Omvandlings ID', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-04 05:01:51', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('467', 'adwords:description_conversion_id', 'Your Google AdWords conversion ID.', 'Ditt Google AdWords omvandlings ID.', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-04 05:01:51', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('468', 'adwords:title_conversion_format', 'Conversion Format', 'Konverteringsformat', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-04 06:05:09', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('469', 'adwords:title_conversion_color', 'Conversion Color', 'Konverteringsfärg', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-04 06:05:09', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('470', 'adwords:description_conversion_label', 'Google Analytics conversion label.', 'Google Analytics konverteringsfärg.', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-04 06:05:09', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('471', 'title_priority', 'Priority', 'Prioritet', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',\'includes/modules/payment/cod.inc.php\',\'admin/modules.app/modules.php\',\'includes/modules/payment/paypal.inc.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',\'admin/currencies.app/currencies.php\',\'admin/languages.app/languages.php\',\'includes/modules/payment/pm_cod.inc.php\',\'includes/modules/payment/pm_invoice.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',\'admin/languages.app/edit_language.php\',\'admin/pages.app/edit_page.php\',\'admin/catalog.app/edit_category.php\',\'admin/currencies.app/edit_currency.php\',', '2012-08-01 05:20:32', '2012-08-14 00:32:09', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('472', 'description_module_priority', 'Process this module in the given priority order.', 'Behandla denna modul i den givna prioritetsordning.', '', '\'admin/index.php\',\'includes/modules/order_success/os_google_adwords.inc.php\',', '2012-08-01 05:20:32', '2012-08-14 00:32:09', '2012-08-09 20:10:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('473', 'title_regional_settings', 'Regional Settings', 'Regionala inställningar', '', '\'select_region.php\',\'select_region.php\',', '2012-08-01 05:44:44', '2012-08-04 05:01:51', '2012-08-21 06:44:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('474', 'settings_group_title:customer_info', 'Customer Info', 'Kundinformation', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-01 06:40:14', '2012-08-13 23:44:24', '2012-08-09 09:11:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('475', 'settings_key_title:fields_customer_password', 'Password Field', 'Lösenord', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-01 06:40:14', '2012-08-13 23:44:24', '2012-08-20 08:31:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('759', 'pm_paypal:error_transaction_not_verified', 'Error: Payment transaction could not be verified by Paypal.', '', '', '\'order_process.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 02:59:23', '2012-08-16 05:08:32', '2012-08-15 02:59:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('758', 'pm_paypal:description', 'Secure and simple money transactions made by Paypal.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 02:35:49', '2012-08-16 05:08:32', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('748', 'paypal:error_transaction_not_verified', 'Error: Payment transaction could not be verified by Paypal.', '', '', '\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 02:07:27', '2012-08-16 05:08:32', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('769', 'success_translated_deleted', 'Translation was successfully deleted', '', '', '\'admin/index.php\',\'admin/translations.app/search.php\',\'admin/translations.app/untranslated.php\',', '2012-08-16 06:25:41', '2012-08-16 06:25:41', '2012-08-16 06:38:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('776', 'job_error_reporter:title_priority', 'Priority', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('753', 'error_must_enter_model', 'You must enter a model', 'Du måste ange modell', '', '\'admin/catalog.app/edit_product.php\',', '2012-08-15 02:07:28', '2012-08-16 05:08:32', '2012-08-15 02:20:43');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('754', 'error_must_select_category', '', 'Du måste ange en kategori', '', '\'admin/catalog.app/edit_product.php\',', '2012-08-15 02:07:28', '2012-08-16 05:08:32', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('755', 'title_create_new_product_option_group', 'Create New Option Group', 'Skapa ny alternativgrupp', '', '\'admin/catalog.app/product_option_groups.php\',', '2012-08-15 02:07:28', '2012-08-16 05:08:32', '2012-08-20 10:17:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('756', 'title_edit_product_option_group', 'Edit Option Group', 'Redigera alternativgrupp', '', '\'admin/catalog.app/edit_product_option_group.php\',', '2012-08-15 02:07:28', '2012-08-16 05:08:32', '2012-08-16 07:49:05');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('483', 'text_select_product_options', 'Please select product options', '', '', '\'product.php\',\'includes/library/cart.inc.php\',', '2012-08-05 05:08:47', '2012-08-05 05:08:47', '2012-08-05 05:08:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('484', 'title_icon', 'Icon', 'Ikon', '', '\'admin/index.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',\'includes/modules/shipping/sm_free.inc.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-06 02:31:50', '2012-08-07 01:33:20', '2012-08-14 08:23:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('781', 'settings_key_title:template', 'Template', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-16 23:19:53', '2012-08-16 23:19:53', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('486', 'sm_weight_table:title_rate_table', 'Rate Table', '', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 03:38:20', '2012-08-13 23:54:35', '2012-08-13 23:53:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('487', 'sm_weight_table:description_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (I.e. 5:8.95;10:15.95;..)', '', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 03:38:20', '2012-08-13 23:54:35', '2012-08-13 23:53:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('488', 'sm_weight_table:title_weight_class', 'Weight Class', 'Viktklass', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 03:38:20', '2012-08-13 23:52:19', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('489', 'sm_weight_table:description_weight_class', 'The weight class for the rate table.', '', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 03:38:20', '2012-08-13 23:54:35', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('490', 'sm_weight_table:title_tax_class', 'Tax Class', 'Momsklass', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 03:38:20', '2012-08-13 23:52:19', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('492', 'sm_weight_table:title', 'Mail Company', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 04:05:51', '2012-08-13 23:52:19', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('493', 'sm_weight_table:title_option_name_1', 'Cost by Weight', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-06 04:05:51', '2012-08-13 23:52:19', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('494', 'title_chepest_shipping', 'Cheapest Shipping', '', '', '\'product.php\',\'product.php\',', '2012-08-06 04:18:05', '2012-08-06 04:18:05', '2012-08-06 04:25:43');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('495', 'title_cost', 'Cost', 'Kostnad', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-06 04:25:34', '2012-08-09 18:37:25', '2012-08-14 08:23:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('496', 'sm_flat_rate:description_title_cost', 'The shipping cost excluding tax.', '', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-06 04:25:34', '2012-08-13 23:54:35', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('497', 'sm_flat_rate:title_tax_class', 'Tax Class', 'Momsklass', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-06 04:25:34', '2012-08-13 23:54:35', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('498', 'shipping:description_tax_class', 'The tax class for the shipping cost.', '', '', '\'includes/checkout/shipping.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-06 04:25:34', '2012-08-13 22:05:17', '2012-08-14 08:23:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('499', 'text_chepest_shipping_from_d', 'Cheapest shipping from %d', '', '', '\'product.php\',\'product.php\',', '2012-08-06 04:27:10', '2012-08-06 04:27:10', '2012-08-06 04:27:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('500', 'text_chepest_shipping_from_s', 'Cheapest shipping from %s', '', '', '\'product.php\',\'product.php\',', '2012-08-06 04:27:31', '2012-08-06 04:27:31', '2012-08-06 04:27:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('501', 'text_cheapest_shipping_to_country_from_price', 'Cheapest shipping to %country from %price', '', '', '\'product.php\',\'product.php\',', '2012-08-06 04:37:34', '2012-08-06 04:37:34', '2012-08-06 04:37:34');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('502', 'text_cheapest_shipping_from_price', 'Cheapest shipping from %price', '', '', '\'product.php\',\'product.php\',', '2012-08-06 04:38:55', '2012-08-12 16:52:31', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('503', 'settings_key_title:display_cheapest_shipping', 'Cheapest Shipping', 'Billigaste frakt', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 00:30:52', '2012-08-13 23:07:19', '2012-08-20 07:04:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('504', 'settings_key_description:fields_customer_password', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:10:19', '2012-08-13 23:44:24', '2012-08-20 06:51:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('505', 'settings_key_description:email_order_copy', 'Send order copies to the following e-mail addresses.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:11:10', '2012-08-07 01:33:20', '2012-08-20 06:52:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('506', 'settings_key_description:seo_urls_enabled', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:12:02', '2012-08-13 23:14:07', '2012-08-07 01:12:02');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('773', 'job_error_reporter:title_email_', 'E-mail Receipient', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-16 06:39:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('774', 'job_error_reporter:description_report_frequency', 'How often the reports should be sent.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('775', 'job_error_reporter:title_report_frequency', 'Report Frequency', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_error_reporter.inc.php\',', '2012-08-16 06:39:23', '2012-08-16 06:39:23', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('510', 'settings_group_title:listings', 'Listings', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:12:22', '2012-08-09 18:44:48', '2012-08-09 09:18:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('511', 'settings_key_title:rows_per_page', 'Number of Rows Per Page', 'Antal rader per sida i datatabell', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:12:22', '2012-08-13 23:08:17', '2012-08-14 08:40:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('512', 'settings_key_description:default_zone', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-07 01:12:50', '2012-08-07 01:33:40', '2012-08-20 06:46:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('513', 'title_delivery_statuses', 'Delivery Statuses', 'Leveransstatus', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-08 04:22:12', '2012-08-13 23:38:44', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('514', 'title_add_new_delivery_status', 'Add New Delivery Status', 'Lägg till ny leveransstatus', '', '\'admin/index.php\',\'admin/catalog.app/delivery_statuses.php\',', '2012-08-08 04:23:06', '2012-08-14 00:04:12', '2012-08-13 23:37:16');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('515', 'title_edit_delivery_status', 'Edit Delivery Status', 'Redigera leveransstatus', '', '\'admin/index.php\',\'admin/catalog.app/edit_delivery_status.php\',', '2012-08-08 04:28:03', '2012-08-13 23:39:23', '2012-08-16 07:49:26');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('516', 'title_delivery_status', 'Delivery Status', 'Leveransstatus', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'product.php\',', '2012-08-08 04:36:34', '2012-08-13 23:38:44', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('517', 'title_sold_out_statuses', 'Sold Out Statuses', 'Status för slutsåld', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-08 04:49:24', '2012-08-13 23:38:44', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('518', 'title_sold_out_status', 'Sold Out Status', 'Status för slutsåld', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-08 04:49:24', '2012-08-13 23:39:59', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('519', 'title_add_new_sold_out_status', 'Add New Sold Out Status', 'Lägg till ny status', '', '\'admin/index.php\',\'admin/catalog.app/sold_out_statuses.php\',', '2012-08-08 04:53:01', '2012-08-14 00:04:12', '2012-08-13 23:38:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('520', 'title_edit_sold_out_status', 'Edit Sold Out Status', 'Redigera status', '', '\'admin/index.php\',\'admin/catalog.app/edit_sold_out_status.php\',', '2012-08-08 04:53:32', '2012-08-13 23:39:23', '2012-08-16 07:51:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('521', 'settings_key_title:phpmyadmin_link', 'phpMyAdmin Link', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-08 04:56:32', '2012-08-08 04:56:32', '2012-08-08 04:56:58');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('523', 'settings_key_title:database_admin_link', 'Database Admin Link', 'Databashanterare', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-08 05:06:18', '2012-08-13 23:11:44', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('524', 'title_database_manager', 'Database Manager', 'Databashanterare', '', '\'admin/index.php\',\'includes/template/desktop_admin.inc.php\',\'includes/templates/default/admin.desktop.inc.php\',', '2012-08-08 05:08:01', '2012-08-13 23:11:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('525', 'text_product_is_orderable', 'Products are orderable', 'Produkter är beställningsbara', '', '\'admin/index.php\',\'admin/catalog.app/edit_sold_out_status.php\',', '2012-08-08 05:19:49', '2012-08-09 18:48:34', '2012-08-16 07:51:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('526', 'text_d_pieces', '%d pieces', '', '', '\'product.php\',\'product.php\',', '2012-08-08 06:24:20', '2012-08-12 16:52:31', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('527', 'text_product_sold_out', 'The product has sold out.', '', '', '\'product.php\',\'includes/library/cart.inc.php\',', '2012-08-08 06:35:43', '2012-08-08 06:35:43', '2012-08-08 06:41:47');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('528', 'text_not_enough_products_in_stock', 'There are not enough products in stock.', '', '', '\'includes/checkout/cart.php\',\'includes/library/cart.inc.php\',', '2012-08-08 06:43:08', '2012-08-08 06:43:08', '2012-08-15 02:36:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('529', 'title_minimum_cart_amount', 'Minimum Cart Amount', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-08 11:03:36', '2012-08-12 16:52:31', '2012-08-14 08:23:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('530', 'sm_free:description_minimum_cart_amount', 'Enable free shipping for orders above the given subtotal amount (excluding tax).', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-08 11:03:36', '2012-08-13 22:05:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('531', 'title_bestsellers', 'Bestsellers', 'Bästsäljare', '', '\'index.php\',\'includes/boxes/bestsellers.inc.php\',', '2012-08-08 11:09:06', '2012-08-09 18:37:02', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('532', 'title_similar_products', 'Similar Products', '', '', '\'product.php\',\'includes/boxes/similar_products.inc.php\',', '2012-08-08 14:10:06', '2012-08-08 14:10:06', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('533', 'pm_invoice:title_enabled', 'Enabled', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-14 08:23:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('534', 'pm_invoice:title_icon', 'Enable this module', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('535', 'pm_invoice:title_payment_fee', 'Payment Fee', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('536', 'pm_invoice:description_payment_fee', 'Adds a payment fee to the order.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('537', 'pm_invoice:title_tax_class', 'Tax Class', 'Momsklass', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-13 22:05:17', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('538', 'pm_invoice:description_tax_class', 'The tax class for the shipping cost.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-13 22:05:17', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('539', 'pm_invoice:title_geo_zone', 'Geo Zone', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('540', 'pm_invoice:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-08 14:54:37', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('541', 'pm_invoice:description_priority', 'Displays this module by the given priority order value.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-08 14:54:37', '2012-08-14 00:32:09', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('542', 'pm_paypal:description_merchant_email', 'Your Paypal registered merchant e-mail address.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-08 14:55:29', '2012-08-08 14:55:29', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('543', 'pm_paypal:pdt_auth_token', 'PDT Auth Token', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-08 14:55:29', '2012-08-08 14:55:29', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('544', 'pm_paypal:description_gateway', 'Select your Paypal payment gateway.', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-08 14:55:29', '2012-08-08 14:55:29', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('545', 'title_also_purchased_products', 'Also Purchased Products', '', '', '\'product.php\',\'includes/boxes/also_purchased_products.inc.php\',', '2012-08-08 14:59:29', '2012-08-08 14:59:29', '2012-08-20 18:18:35');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('546', 'pm_paypal:description_pdt_auth_token', 'Your Paypal PDT authorization token (see your Paypal account).', '', '', '\'admin/index.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-08 17:30:00', '2012-08-08 17:30:00', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('547', 'settings_key_title:display_stock_count', 'Display Stock Count', 'Visa lagerantal', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-08 17:32:51', '2012-08-13 23:08:17', '2012-08-20 07:04:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('548', 'title_categories_and_products', 'Categories &amp; Products', '', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-09 08:22:51', '2012-08-09 08:22:51', '2012-08-09 08:23:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('549', 'settings_key_description:store_zone_id', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 08:57:25', '2012-08-13 21:31:25', '2012-08-09 18:57:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('550', 'settings_key_description:store_keywords', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 08:57:46', '2012-08-13 21:31:25', '2012-08-13 23:04:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('551', 'settings_group:advanced', 'Advanced', 'Avancerat', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',\'admin/settings.app/config.inc.php\',', '2012-08-09 09:06:45', '2012-08-09 18:35:15', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('552', 'settings_group_title:advanced', 'Advanced', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:06:50', '2012-08-09 18:44:48', '2012-08-09 09:19:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('553', 'settings_key_description:gzip_enabled', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:10:48', '2012-08-09 18:44:48', '2012-08-13 23:10:25');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('554', 'settings_key_title:box_custom_data', 'Custom Box Data', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:35', '2012-08-09 18:48:34', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('555', 'settings_key_title:date_cache_cleared', 'Date Cache Cleared', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-13 23:11:02', '2012-08-09 13:52:14');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('556', 'settings_key_title:errors_last_reported', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 18:48:34', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('557', 'settings_key_title:order_success_modules', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('558', 'settings_key_title:order_success_module_os_google_adwords', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 18:44:48', '2012-08-09 09:20:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('559', 'settings_key_title:order_total_modules', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('560', 'settings_key_title:order_total_module_ot_payment_fee', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('561', 'settings_key_title:order_total_module_ot_shipping_fee', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('562', 'settings_key_title:order_total_module_ot_subtotal', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('563', 'settings_key_title:payment_modules', 'Installed Payment Modules', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 16:52:15', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('564', 'settings_key_title:payment_module_pm_cod', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 09:20:36', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('565', 'settings_key_title:payment_module_pm_invoice', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 09:20:36', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('566', 'settings_key_title:payment_module_pm_paypal', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 09:20:36', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('567', 'settings_key_title:shipping_modules', 'Installed Shipping Modules', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 09:20:36', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('568', 'settings_key_title:shipping_module_sm_flat_rate', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-13 23:54:35', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('569', 'settings_key_title:shipping_module_sm_free', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-09 09:20:36', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('570', 'settings_key_title:shipping_module_sm_weight_table', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 09:20:36', '2012-08-13 23:52:19', '2012-08-09 13:52:14');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('571', 'text_select_a_product_option', '', 'Välj ett produktalternativ', '', '\'includes/library/cart.inc.php\',', '2012-08-09 10:17:29', '2012-08-09 18:42:03', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('572', 'text_not_enough_products_option_in_stock', '', '', '', '\'includes/library/cart.inc.php\',', '2012-08-09 10:17:29', '2012-08-09 18:42:03', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('582', 'title_add_new_page', 'Add New Page', 'Lägg till ny sida', '', '\'admin/index.php\',\'admin/pages.app/pages.php\',', '2012-08-09 16:52:32', '2012-08-14 00:04:12', '2012-08-14 00:19:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('573', 'title_create_new_sold_out_status', 'Create New Sold Out Status', 'Skapa ny status', '', '\'admin/catalog.app/edit_sold_out_status.php\',\'admin/catalog.app/sold_out_statuses.php\',', '2012-08-09 10:17:33', '2012-08-14 00:10:17', '2012-08-20 10:16:53');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('574', 'title_new_product_group', 'Create New Product Group', 'Skapa ny produktgrupp', '', '\'admin/catalog.app/edit_product_group.php\',', '2012-08-09 10:17:33', '2012-08-14 00:10:17', '2012-08-13 23:24:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('575', 'title_create_new_delivery_status', 'Create New Delivery Status', 'Skapa ny leveransstatus', '', '\'admin/catalog.app/edit_delivery_status.php\',\'admin/catalog.app/delivery_statuses.php\',', '2012-08-09 10:17:33', '2012-08-14 00:10:17', '2012-08-20 10:16:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('576', 'success_products_imported', 'Products successfully imported.', 'Importen av produkter lyckades.', '', '\'admin/catalog.app/csv.php\',', '2012-08-09 10:17:34', '2012-08-09 18:38:35', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('577', 'title_import_products_to_csv', 'Import Products From CSV', 'Importera produkter från CSV-fil', '', '\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-08-09 10:17:34', '2012-08-14 00:46:31', '2012-08-11 19:52:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('578', 'title_export_products_to_csv', 'Export Products To CSV', 'Exportera produkter till CSV', '', '\'admin/catalog.app/csv.php\',\'admin/catalog.app/csv_products.php\',', '2012-08-09 10:17:34', '2012-08-14 00:46:31', '2012-08-20 18:46:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('579', 'title_order_total', 'Order Total', 'Ordertotal', '', '\'admin/index.php\',\'admin/modules.app/config.inc.php\',', '2012-08-09 12:11:20', '2012-08-13 22:06:44', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('580', 'title_pages', 'Pages', 'Sidor', '', '\'admin/index.php\',\'admin/pages.app/config.inc.php\',', '2012-08-09 16:42:47', '2012-08-09 18:35:15', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('770', 'modules:description_tax_class', 'The tax class for the shipping cost.', '', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-16 06:38:35', '2012-08-16 06:38:35', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('583', 'title_edit_page', 'Edit Page', 'Redigera sida', '', '\'admin/index.php\',\'admin/pages.app/edit_page.php\',', '2012-08-09 16:53:30', '2012-08-13 23:39:23', '2012-08-16 07:51:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('584', 'title_dock', 'Dock', 'Docka', '', '\'admin/index.php\',\'admin/pages.app/edit_page.php\',', '2012-08-09 17:16:59', '2012-08-14 00:21:58', '2012-08-16 07:51:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('585', 'text_dock_in_site_menu', 'Dock in site menu', 'Docka i huvudmenyn', '', '\'admin/index.php\',\'admin/pages.app/edit_page.php\',', '2012-08-09 17:16:59', '2012-08-14 00:21:58', '2012-08-09 18:10:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('586', 'settings_key_description:store_email', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:51:52', '2012-08-13 21:31:25', '2012-08-13 23:04:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('587', 'settings_key_description:store_name', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:52:04', '2012-08-13 21:31:25', '2012-08-13 23:04:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('588', 'settings_key_description:display_cheapest_shipping', 'Display the cheapest shipping cost on product page.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:53:43', '2012-08-09 18:53:43', '2012-08-13 23:07:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('589', 'settings_key_description:display_stock_count', 'Show the available amounts of products in stock.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:54:06', '2012-08-09 18:54:06', '2012-08-13 23:07:37');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('590', 'settings_key_description:store_country_id', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:57:38', '2012-08-13 21:31:25', '2012-08-13 23:04:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('591', 'settings_key_title:store_zone', 'Store Zone', 'Zon', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:58:48', '2012-08-13 21:31:25', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('592', 'settings_key_description:store_zone', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 18:58:51', '2012-08-13 21:31:25', '2012-08-13 23:04:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('593', 'settings_key_description:store_short_description', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 19:05:51', '2012-08-13 21:31:25', '2012-08-13 23:04:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('594', 'settings_key_description:checkout_captcha_enabled', 'Lets customers enter a CAPTACHA code before placing orders.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-09 19:32:26', '2012-08-13 23:44:24', '2012-08-13 23:08:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('595', 'title_support', 'Support', 'Kundservice', '', '\'page.php\',\'includes/boxes/site_menu.inc.php\',\'admin/pages.app/pages.php\',', '2012-08-09 19:46:39', '2012-08-16 01:00:36', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('596', 'text_dock_in_dock_menu', 'Dock in site menu', 'Docka i huvudmenyn', '', '\'admin/index.php\',\'admin/pages.app/edit_page.php\',', '2012-08-09 19:47:00', '2012-08-14 00:21:58', '2012-08-16 07:51:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('597', 'text_dock_in_support_page', 'Dock in support page', 'Docka i sidan kundtjänst', '', '\'admin/index.php\',\'admin/pages.app/edit_page.php\',', '2012-08-09 19:47:45', '2012-08-16 01:00:53', '2012-08-16 07:51:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('598', 'title_menu', 'Menu', 'Huvudmeny', '', '\'admin/index.php\',\'admin/pages.app/pages.php\',', '2012-08-09 19:55:38', '2012-08-14 00:23:18', '2012-08-20 10:05:14');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('599', 'os_tradedoubler:title_status', 'Status', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-13 23:38:44', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('600', 'os_tradedoubler:description_status', 'Enables or disables the module.', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-13 23:38:44', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('601', 'os_tradedoubler:title_organization_id', 'Organization ID', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('602', 'os_tradedoubler:description_orgnization_id', 'Your Organization ID provided by Tradedoubler.', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('603', 'os_tradedoubler:title_event_id', 'Event ID', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('604', 'os_tradedoubler:description_event_id', 'Your Event ID provided by Tradedoubler.', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('605', 'os_tradedoubler:title_checksum_code', 'Checksum Code', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('606', 'os_tradedoubler:description_checksum_code', 'Your Checksum Code provided by Tradedoubler, used for calculating the checksum.', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-09 20:10:08', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('607', 'os_tradedoubler:title_priority', 'Priority', 'Prioritet', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-14 00:32:09', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('608', 'os_tradedoubler:description_priority', 'Process this module in the given priority order.', '', '', '\'admin/index.php\',\'includes/modules/order_success/os_tradedoubler.inc.php\',', '2012-08-09 20:10:08', '2012-08-14 00:32:09', '2012-08-09 20:11:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('609', 'title_specials', 'Specials', 'Kampanjer', '', '\'index.php\',\'includes/boxes/specials.inc.php\',', '2012-08-10 16:02:41', '2012-08-16 05:12:02', '2012-08-21 08:25:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('610', 'title_date_registered', 'Date Registered', 'Registrerad', '', '\'admin/index.php\',\'admin/customers.app/customers.php\',', '2012-08-11 16:06:52', '2012-08-13 23:09:58', '2012-08-20 10:17:11');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('611', 'title_products_CSV', 'Products CSV', 'Importera / Exportera', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-11 19:52:26', '2012-08-14 00:46:31', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('612', 'title_csv_import_export_products', 'CSV Import/Export Products', 'CSV importera / exportera produkter', '', '\'admin/index.php\',\'admin/catalog.app/csv_products.php\',', '2012-08-11 19:52:31', '2012-08-14 00:46:31', '2012-08-20 18:46:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('613', 'title_import_update_products_to_csv', 'Import/Update Products From CSV', 'Importera / Uppdatera produkter från CSV', '', '\'admin/index.php\',\'admin/catalog.app/csv_products.php\',', '2012-08-11 19:55:22', '2012-08-14 00:46:31', '2012-08-20 18:46:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('614', 'title_background_jobs', 'Background Jobs', 'Bakgrundsjobb', '', '\'admin/index.php\',\'admin/modules.app/config.inc.php\',', '2012-08-11 21:04:08', '2012-08-14 00:31:06', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('615', 'title_job_modules', 'Job Modules', 'Jobbmoduler', '', '\'admin/index.php\',\'admin/modules.app/modules.php\',', '2012-08-11 21:04:12', '2012-08-14 00:31:26', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('616', 'job_currency_updater:title_status', 'Status', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-16 05:12:02', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('617', 'job_currency_updater:description_status', 'Enables or disables the module.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-16 05:12:02', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('618', 'job_currency_updater:title_update_frequency', 'Update Frequency', 'Uppdateringsfrekvens', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-14 00:32:31', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('619', 'job_currency_updater:description_update_frequency', 'How often the currency values should be updated.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-16 05:12:02', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('620', 'job_currency_updater:title_priority', 'Priority', 'Prioritet', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-14 00:32:31', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('621', 'job_currency_updater:description_priority', 'Process this module in the given priority order.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_currency_updater.inc.php\',', '2012-08-11 21:09:47', '2012-08-16 05:12:02', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('622', 'settings_key_title:template_admin', 'Admin Template', 'Adminmall', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:11', '2012-08-13 21:27:42', '2012-08-16 23:12:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('623', 'settings_key_title:template_catalog', 'Catalog Template', 'Katalogmall', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:11', '2012-08-13 21:27:42', '2012-08-16 23:12:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('624', 'settings_key_title:jobs_interval', 'Jobs Interval', 'Jobbintervall', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:13', '2012-08-13 23:13:20', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('625', 'settings_key_title:jobs_last_run', 'Jobs Last Run', 'Jobb senast utfört', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:13', '2012-08-13 23:13:20', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('626', 'settings_key_description:jobs_interval', 'The amount of minutes between each execution of jobs.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:29', '2012-08-13 23:13:20', '2012-08-13 23:10:27');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('627', 'settings_key_description:jobs_last_run', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-11 21:27:34', '2012-08-13 23:13:20', '2012-08-16 08:29:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('628', 'error_missing_title', 'You must enter a title', 'Du måste ange en titel', '', '\'admin/pages.app/edit_page.php\',', '2012-08-12 16:45:59', '2012-08-13 05:01:11', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('629', 'title_create_new_pages', 'Create New Page', 'Skapa ny sida', '', '\'admin/pages.app/edit_page.php\',', '2012-08-12 16:45:59', '2012-08-14 00:10:17', '2012-08-14 00:23:04');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('630', 'settings_key_title:cache_clear_seo_links', 'Clear SEO Links Cache', 'Töm SEO URL-cache', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-12 17:18:18', '2012-08-13 23:14:07', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('631', 'settings_key_title:cache_clear_thumbnails', 'Clear Thumbnails Cache', 'Töm cache för miniatyrbilder', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-12 17:18:18', '2012-08-13 23:17:21', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('632', 'settings_key_description:cache_clear_seo_links', 'Remove all cached SEO links from database.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-12 17:18:34', '2012-08-13 23:14:07', '2012-08-13 23:10:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('633', 'settings_key_description:cache_clear_thumbnails', 'Remove all cached image thumnbnails from disk.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-12 17:21:15', '2012-08-13 23:11:02', '2012-08-13 23:10:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('634', 'settings_key_title:seo_links_enabled', 'SEO Links Enabled', 'SEO-länkar', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-12 17:24:55', '2012-08-13 23:14:07', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('635', 'title_enable', 'Enable', 'Aktivera', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',\'admin/countries.app/countries.php\',\'admin/currencies.app/currencies.php\',\'admin/languages.app/languages.php\',\'admin/catalog.app/manufacturers.php\',', '2012-08-13 02:48:51', '2012-08-13 05:01:11', '2012-08-21 16:55:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('636', 'title_disable', 'Disable', 'Inaktivera', '', '\'admin/index.php\',\'admin/catalog.app/catalog.php\',\'admin/countries.app/countries.php\',\'admin/currencies.app/currencies.php\',\'admin/languages.app/languages.php\',\'admin/catalog.app/manufacturers.php\',', '2012-08-13 02:48:51', '2012-08-13 05:01:11', '2012-08-21 16:55:23');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('637', 'settings_key_description:cache_enabled', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 05:21:22', '2012-08-13 22:43:04', '2012-08-13 23:10:22');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('672', 'title_page_parse_time', 'Page Parse Time', 'Exekveringstid', '', '\'admin/index.php\',\'includes/library/stats.inc.php\',', '2012-08-14 00:58:48', '2012-08-16 05:10:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('757', 'pm_paypal:title_card_payment', 'Card Payment', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 02:35:49', '2012-08-16 05:08:32', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('638', 'error_must_enter_code', 'You must enter a code.', '', '', '\'admin/catalog.app/edit_category.php\',', '2012-08-13 22:28:08', '0000-00-00 00:00:00', '2012-08-20 21:06:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('639', 'error_must_enter_name', 'You must enter a name.', '', '', '\'admin/catalog.app/edit_category.php\',\'support.php\',', '2012-08-13 22:28:08', '0000-00-00 00:00:00', '2012-08-15 22:59:59');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('640', 'settings_key_description:rows_per_page', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 22:37:52', '2012-08-13 22:37:52', '2012-08-13 23:07:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('641', 'settings_key_description:system_currency', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:01:11', '2012-08-13 23:01:11', '2012-08-14 01:38:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('642', 'settings_key_description:system_language', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:01:51', '2012-08-13 23:01:51', '2012-08-13 23:02:49');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('643', 'settings_key_description:system_length_class', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:02:48', '2012-08-13 23:02:48', '2012-08-13 23:02:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('644', 'settings_key_description:system_weight_class', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:02:49', '2012-08-13 23:52:19', '2012-08-13 23:03:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('645', 'settings_key_description:template_admin', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:02:51', '2012-08-13 23:02:51', '2012-08-13 23:02:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('646', 'settings_key_description:template_catalog', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:02:57', '2012-08-14 00:23:49', '2012-08-13 23:03:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('647', 'settings_key_description:store_link', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:03:17', '2012-08-14 00:23:49', '2012-08-13 23:04:11');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('648', 'settings_key_description:store_phone', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:04:22', '2012-08-14 00:23:49', '2012-08-13 23:04:31');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('649', 'settings_key_description:default_country', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:05:46', '2012-08-14 00:23:49', '2012-08-13 23:06:34');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('650', 'settings_key_description:default_currency', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:05:47', '2012-08-14 00:23:49', '2012-08-13 23:06:33');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('651', 'settings_key_description:default_language', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:05:49', '2012-08-14 00:23:49', '2012-08-13 23:06:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('652', 'settings_key_description:checkout_ajax_enabled', 'Enables AJAX functionality for checkout.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:08:25', '2012-08-14 00:44:18', '2012-08-13 23:08:38');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('653', 'settings_key_description:register_guests', 'Create customer accounts for all guests.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:08:55', '2012-08-14 00:44:18', '2012-08-20 08:31:19');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('654', 'settings_key_description:database_admin_link', 'The URL to your database manager i.e. phpMyAdmin.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:10:24', '2012-08-14 00:44:18', '2012-08-13 23:10:24');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('655', 'settings_key_description:seo_links_enabled', 'Enabling this requires .htaccess and mod_rewrite rules.', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-13 23:10:30', '2012-08-14 00:44:18', '2012-08-13 23:13:58');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('656', 'title_general', 'General', 'Allmänt', '', '\'admin/index.php\',\'admin/catalog.app/edit_category.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-13 23:16:13', '2012-08-14 00:44:18', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('657', 'title_create_new_product_group', 'Create New Product Group', 'Skapa ny produktgrupp', '', '\'admin/index.php\',\'admin/catalog.app/product_groups.php\',', '2012-08-13 23:22:59', '2012-08-14 00:44:18', '2012-08-20 10:17:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('658', 'title_create_new_option_group', 'Create New Option Group', 'Skapa ny alternativgrupp', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',\'admin/catalog.app/option_groups.php\',', '2012-08-13 23:27:51', '2012-08-14 00:44:18', '2012-08-15 01:12:55');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('659', 'title_edit_option_group', 'Edit Option Group', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_option_group.php\',', '2012-08-13 23:30:04', '2012-08-14 00:44:18', '2012-08-13 23:31:21');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('660', 'title_add_new_customer_profile', 'Add New Customer Profile', 'Lägg till ny kund', '', '\'admin/index.php\',\'admin/customers.app/edit_customer.php\',', '2012-08-13 23:43:22', '2012-08-14 00:44:18', '2012-08-13 23:45:01');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('661', 'sm_weight_table:title_weight_rate_table', 'Weight Rate Table', 'Portotabell', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-13 23:54:11', '2012-08-14 00:44:18', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('662', 'sm_weight_table:description_weight_rate_table', 'Ascending rate table of the shipping cost. The format must be weight:cost;weight:cost;.. (I.e. 5:8.95;10:15.95;..)', '', '', '\'admin/index.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-13 23:54:11', '2012-08-14 00:44:18', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('663', 'title_create_new_order', 'Create New Order', 'Skapa ny order', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',\'admin/orders.app/orders.php\',', '2012-08-14 00:09:40', '2012-08-14 00:44:18', '2012-08-20 08:42:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('664', 'title_edit_order', 'Edit Order', 'Redigera order', '', '\'admin/index.php\',\'admin/orders.app/edit_order.php\',', '2012-08-14 00:10:52', '2012-08-16 05:12:02', '2012-08-16 07:47:41');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('665', 'title_create_new_page', 'Create New Page', 'Skapa ny sida', '', '\'admin/index.php\',\'admin/pages.app/pages.php\',', '2012-08-14 00:20:07', '2012-08-14 00:44:18', '2012-08-20 10:05:14');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('666', 'error_must_select_geo_zone', '', '', '', '\'admin/tax.app/edit_tax_rate.php\',', '2012-08-14 00:40:20', '2012-08-14 00:44:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('667', 'error_must_select_tax_class', '', '', '', '\'admin/tax.app/edit_tax_rate.php\',', '2012-08-14 00:40:20', '2012-08-14 00:44:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('668', 'error_must_enter_rate', 'You must enter a rate', '', '', '\'admin/tax.app/edit_tax_rate.php\',', '2012-08-14 00:40:20', '2012-08-14 00:44:18', '0000-00-00 00:00:00');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('669', 'title_csv_import_export_translations', 'CSV Import/Export Translations', 'CSV importera / exportera översättningar', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-08-14 00:41:51', '2012-08-14 00:46:31', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('670', 'title_import_to_csv', 'Import From CSV', 'Importera från CSV', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-08-14 00:41:51', '2012-08-14 00:46:31', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('671', 'title_export_to_csv', 'Export To CSV', 'Exportera till CSV', '', '\'admin/index.php\',\'admin/translations.app/csv.php\',', '2012-08-14 00:41:51', '2012-08-14 00:46:31', '2012-08-20 20:12:15');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('673', 'title_memory_limit', 'Memory Limit', 'Minnesgräns', '', '\'admin/index.php\',\'includes/library/stats.inc.php\',', '2012-08-14 00:58:48', '2012-08-16 05:10:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('674', 'title_memory_peak', 'Memory Peak', 'Minnestopp', '', '\'admin/index.php\',\'includes/library/stats.inc.php\',', '2012-08-14 00:58:48', '2012-08-16 05:10:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('675', 'title_database_queries', 'Database Queries', 'Databasfrågor', '', '\'admin/index.php\',\'includes/library/stats.inc.php\',', '2012-08-14 00:58:48', '2012-08-16 05:10:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('676', 'title_database_parse_time', 'Database Parse Time', 'Databas', '', '\'admin/index.php\',\'includes/library/stats.inc.php\',', '2012-08-14 00:58:48', '2012-08-16 05:10:44', '2012-08-21 18:20:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('677', 'title_data', 'Data', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('678', 'title_configurations', 'Configurations', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-21 16:55:29');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('679', 'title_function', 'Value', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_product_configuration_group.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-16 07:49:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('680', 'title_price_adjust', 'Price Adjust', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('681', 'title_required', 'Required', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',\'admin/catalog.app/edit_product_configuration_group.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-21 16:55:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('682', 'title_add_configuration_value', 'Add Configuration Value', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-14 04:07:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('683', 'title_add_configuration', 'Add Configuration', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-14 04:07:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('684', 'error_empty_configuration_group', 'Error: Empty configuration group', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-14 04:07:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('685', 'error_empty_configuration_value', 'Error: Empty configuration value', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product.php\',', '2012-08-14 02:27:37', '2012-08-14 02:27:37', '2012-08-14 04:07:13');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('686', 'title_product_configuration_groups', 'Product Configuration Groups', 'Produktkonfigurationsgrupper', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-14 03:05:05', '2012-08-16 05:10:44', '2012-08-20 10:43:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('687', 'title_create_new_configuration_group', 'Create New Configuration Group', '', '', '\'admin/index.php\',\'admin/catalog.app/product_configuration_groups.php\',\'admin/catalog.app/edit_product_configuration_group.php\',', '2012-08-14 03:06:17', '2012-08-14 03:06:17', '2012-08-14 03:32:54');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('688', 'title_configuration_groups', 'Configuration Groups', '', '', '\'admin/index.php\',\'admin/catalog.app/product_configuration_groups.php\',', '2012-08-14 03:06:17', '2012-08-14 03:06:17', '2012-08-14 03:06:17');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('689', 'title_create_new_product_configuration_group', 'Create New Product Configuration Group', '', '', '\'admin/index.php\',\'admin/catalog.app/product_configuration_groups.php\',', '2012-08-14 03:11:59', '2012-08-14 03:11:59', '2012-08-20 10:17:07');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('690', 'title_edit_configuration_group', 'Edit Configuration Group', '', '', '\'admin/index.php\',\'admin/catalog.app/edit_product_configuration_group.php\',', '2012-08-14 03:48:11', '2012-08-14 03:48:11', '2012-08-16 07:49:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('691', 'title_statistics', 'Statistics', 'Statistik', '', '\'admin/index.php\',\'admin/stats.widget/config.inc.php\',', '2012-08-14 21:12:17', '2012-08-16 05:10:44', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('692', 'title_total_sales', 'Total Sales', 'Total försäljning', '', '\'admin/index.php\',\'admin/stats.widget/stats.php\',\'admin/reports.app/monthly_sales.php\',', '2012-08-14 21:13:53', '2012-08-16 05:10:44', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('693', 'title_average_order_amount', 'Average Order Amount', 'Genomsnittligt ordervärde', '', '\'admin/index.php\',\'admin/stats.widget/stats.php\',', '2012-08-14 21:21:25', '2012-08-16 05:10:44', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('694', 'title_number_of_customers', 'Number of Customers', 'Antal kunder', '', '\'admin/index.php\',\'admin/stats.widget/stats.php\',', '2012-08-14 21:27:19', '2012-08-16 05:10:44', '2012-08-21 18:16:32');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('695', 'title_product_option_groups', 'Product Option Groups', '', '', '\'admin/index.php\',\'admin/catalog.app/config.inc.php\',', '2012-08-15 01:39:58', '2012-08-16 05:12:02', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('696', 'sm_flat_rate:title_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('697', 'sm_flat_rate:description_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('698', 'sm_flat_rate:title_icon', 'Icon', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('699', 'sm_flat_rate:description_icon', 'Web path of the icon to be displayed.', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('700', 'sm_flat_rate:title_cost', 'Cost', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('701', 'sm_flat_rate:description_tax_class', 'The tax class for the shipping cost.', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:09');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('702', 'sm_flat_rate:title_geo_zone_limitation', 'Geo Zone Limitation', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('703', 'sm_flat_rate:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('704', 'sm_flat_rate:title_priority', 'Priority', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('705', 'sm_flat_rate:description_priority', 'Process this module by the given priority value.', '', '', '\'product.php\',\'includes/modules/shipping/sm_flat_rate.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('706', 'sm_free:title_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('707', 'sm_free:description_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('708', 'sm_free:title_icon', 'Icon', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('709', 'sm_free:description_icon', 'Web path of the icon to be displayed.', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('710', 'sm_free:title_minimum_cart_amount', 'Minimum Cart Amount', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('711', 'sm_free:title_geo_zone_limitation', 'Geo Zone Limitation', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('712', 'sm_free:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('713', 'sm_free:title_priority', 'Priority', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('714', 'sm_free:description_priority', 'Process this module by the given priority value.', '', '', '\'product.php\',\'includes/modules/shipping/sm_free.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('715', 'sm_weight_table:title_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('716', 'sm_weight_table:description_status', 'Status', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('717', 'sm_weight_table:title_icon', 'Icon', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('718', 'sm_weight_table:description_icon', 'Web path of the icon to be displayed.', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('719', 'sm_weight_table:title_geo_zone_limitation', 'Geo Zone Limitation', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('720', 'sm_weight_table:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('721', 'sm_weight_table:title_priority', 'Priority', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('722', 'sm_weight_table:description_priority', 'Process this module by the given priority value.', '', '', '\'product.php\',\'includes/modules/shipping/sm_weight_table.inc.php\',', '2012-08-15 01:43:17', '2012-08-15 01:43:17', '2012-08-21 18:18:10');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('723', 'pm_cod:title_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('724', 'pm_cod:description_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('725', 'pm_cod:title_icon', 'Icon', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('726', 'pm_cod:description_icon', 'Web path of the icon to be displayed.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('727', 'pm_invoice:title_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('728', 'pm_invoice:description_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('729', 'pm_invoice:description_icon', 'Web path of the icon to be displayed.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_invoice.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('730', 'pm_paypal:title_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('731', 'pm_paypal:description_status', 'Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('732', 'pm_paypal:title_icon', 'Icon', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('733', 'pm_paypal:description_icon', 'Web path of the icon to be displayed.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('734', 'pm_paypal:title_merchant_email', 'Merchant E-mail', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('735', 'pm_paypal:title_gateway', 'Gateway', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('736', 'pm_paypal:title_order_status', 'Order Status', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('737', 'pm_paypal:description_order_status', 'Give orders made with this payment method the following order status.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('738', 'pm_paypal:title_geo_zone_limitation', 'Geo Zone Limitation', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('739', 'pm_paypal:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('740', 'pm_paypal:title_priority', 'Priority', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('741', 'pm_paypal:description_priority', 'Process this module in the given priority order.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_paypal.inc.php\',', '2012-08-15 01:44:46', '2012-08-15 01:44:46', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('742', 'ot_subtotal:title_priority', 'Priority', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_subtotal.inc.php\',', '2012-08-15 01:52:16', '2012-08-16 05:08:32', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('743', 'ot_subtotal:description_priority', 'Process this module by the given priority value.', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_subtotal.inc.php\',', '2012-08-15 01:52:16', '2012-08-15 01:52:16', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('744', 'ot_payment_fee:title_priority', 'Priority', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_payment_fee.inc.php\',', '2012-08-15 01:52:16', '2012-08-15 01:52:16', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('745', 'ot_payment_fee:description_priority', 'Process this module by the given priority value.', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_payment_fee.inc.php\',', '2012-08-15 01:52:16', '2012-08-15 01:52:16', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('746', 'ot_shipping_fee:title_priority', 'Priority', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_shipping_fee.inc.php\',', '2012-08-15 01:52:16', '2012-08-15 01:52:16', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('747', 'ot_shipping_fee:description_priority', 'Process this module by the given priority value.', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_shipping_fee.inc.php\',', '2012-08-15 01:52:16', '2012-08-15 01:52:16', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('782', 'modules:description_order_status', 'Give orders made with this payment method the following order status.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('783', 'modules:description_geo_zone', 'Limit this module to the selected geo zone. Otherwise leave blank.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('784', 'modules:description_priority', 'Process this module in the given priority order.', '', '', '\'checkout.php\',\'includes/modules/payment/pm_cod.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('785', 'ot_subtotal:title_subtotal', 'Subtotal', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_subtotal.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('786', 'ot_payment_fee:title_payment_fee', 'Payment Fee', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_payment_fee.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('787', 'ot_shipping_fee:title_shipping_fee', 'Shipping Fee', '', '', '\'checkout.php\',\'includes/modules/order_total/ot_shipping_fee.inc.php\',', '2012-08-17 03:14:11', '2012-08-17 03:14:11', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('788', 'title_incl_tax', 'Incl. Tax', '', '', '\'checkout.php\',\'includes/checkout/summary.php\',', '2012-08-17 04:06:21', '2012-08-17 04:06:21', '2012-08-20 08:53:08');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('789', 'warning_admin_folder_not_protected', 'Warning: Your admin folder is not .htaccess protected', '', '', '\'admin/index.php\',\'admin/index.php\',', '2012-08-17 04:28:56', '2012-08-17 04:28:56', '2012-08-20 07:05:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('790', 'title_users', 'Users', '', '', '\'admin/index.php\',\'admin/users.app/config.inc.php\',\'admin/reports.app/monthly_sales.php\',', '2012-08-17 04:50:58', '2012-08-17 04:50:58', '2012-08-21 18:20:51');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('791', 'title_create_new_user', 'Create New User', '', '', '\'admin/index.php\',\'admin/users.app/users.php\',\'admin/users.app/edit_user.php\',\'admin/reports.app/monthly_sales.php\',', '2012-08-17 04:51:44', '2012-08-17 04:51:44', '2012-08-20 07:24:52');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('792', 'title_user', 'User', '', '', '\'admin/index.php\',\'admin/users.app/users.php\',', '2012-08-17 04:51:44', '2012-08-17 04:51:44', '2012-08-20 07:16:53');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('793', 'title_username', 'Username', '', '', '\'admin/index.php\',\'admin/users.app/edit_user.php\',', '2012-08-17 05:12:12', '2012-08-17 05:12:12', '2012-08-20 07:07:16');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('794', 'title_edit_user', 'Edit User', '', '', '\'admin/index.php\',\'admin/users.app/edit_user.php\',', '2012-08-17 05:16:04', '2012-08-17 05:16:04', '2012-08-17 05:46:30');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('795', 'error_must_enter_confirmed_password', 'You must confirm the password', '', '', '\'admin/index.php\',\'admin/users.app/edit_user.php\',', '2012-08-17 05:18:55', '2012-08-17 05:18:55', '2012-08-17 05:38:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('796', 'error_must_enter_password', 'You must enter a password', '', '', '\'admin/index.php\',\'admin/users.app/edit_user.php\',', '2012-08-17 05:38:06', '2012-08-17 05:38:06', '2012-08-17 05:38:06');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('797', 'error_must_enter_username', 'You must enter a username', '', '', '\'admin/index.php\',\'admin/users.app/edit_user.php\',', '2012-08-17 05:41:48', '2012-08-17 05:41:48', '2012-08-17 05:41:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('798', 'settings_key_title:store_timezone', 'StoreTime Zone', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-19 21:32:47', '2012-08-19 21:32:47', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('799', 'settings_key_title:store_currency', 'Store Currency', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 06:41:34', '2012-08-20 06:41:34', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('800', 'settings_key_title:store_language', 'Store Language', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 06:41:34', '2012-08-20 06:41:34', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('801', 'settings_key_title:store_length_class', 'System Length Class', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 06:41:34', '2012-08-20 06:41:34', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('802', 'settings_key_title:store_weight_class', 'System Weight Class', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 06:41:34', '2012-08-20 06:41:34', '2012-08-21 07:41:39');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('803', 'settings_key_title:data_table_rows_per_page', 'Data Table Rows', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 06:47:50', '2012-08-20 06:47:50', '2012-08-20 07:04:46');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('804', 'settings_key_title:cache_system_breakpoint', 'Date Cache Cleared', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 07:00:23', '2012-08-20 07:00:23', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('805', 'settings_key_title:currencies_last_updated', 'Currencies Last Updated', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 07:00:23', '2012-08-20 07:00:23', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('806', 'settings_key_title:jobs_modules', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 07:00:23', '2012-08-20 07:00:23', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('807', 'settings_key_title:jobs_module_job_currency_updater', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 07:00:23', '2012-08-20 07:00:23', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('808', 'settings_key_title:jobs_module_job_error_reporter', '', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-20 07:00:23', '2012-08-20 07:00:23', '2012-08-20 07:04:36');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('809', 'title_reports', 'Reports', '', '', '\'admin/index.php\',\'admin/reports.app/config.inc.php\',', '2012-08-20 07:16:53', '2012-08-20 07:16:53', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('810', 'title_month', 'Month', '', '', '\'admin/index.php\',\'admin/reports.app/monthly_sales.php\',', '2012-08-20 07:24:52', '2012-08-20 07:24:52', '2012-08-20 07:51:26');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('811', 'title_monthly_sales', 'Monthly Sales', '', '', '\'admin/index.php\',\'admin/reports.app/monthly_sales.php\',\'admin/reports.app/config.inc.php\',', '2012-08-20 07:27:21', '2012-08-20 07:27:21', '2012-08-21 18:20:50');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('812', 'title_total_tax', 'Total Tax', '', '', '\'admin/index.php\',\'admin/reports.app/monthly_sales.php\',', '2012-08-20 07:27:21', '2012-08-20 07:27:21', '2012-08-20 07:51:26');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('813', 'text_is_sale', 'Is sale', '', '', '\'admin/index.php\',\'admin/orders.app/edit_order_status.php\',', '2012-08-20 07:48:14', '2012-08-20 07:48:14', '2012-08-20 07:49:03');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('814', 'title_guest', 'Guest', '', '', '\'admin/index.php\',\'admin/orders.app/orders.php\',', '2012-08-20 08:36:27', '2012-08-20 08:36:27', '2012-08-20 08:42:48');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('815', 'text_new_customers_click_here', 'New customers click here', '', '', '\'index.php\',\'includes/boxes/login.inc.php\',', '2012-08-20 08:51:39', '2012-08-20 08:51:39', '2012-08-21 08:25:20');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('816', 'title_create_account', 'Create Account', '', '', '\'create_account.php\',\'create_account.php\',', '2012-08-20 08:51:48', '2012-08-20 08:51:48', '2012-08-20 08:55:12');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('817', 'text_logged_in_as_s', 'Logged in as %s', '', '', '\'index.php\',\'includes/boxes/account.inc.php\',', '2012-08-20 08:58:26', '2012-08-20 08:58:26', '2012-08-21 06:38:57');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('818', 'settings_key_title:seo_links_language_prefix', 'SEO Links Language Prefix', '', '', '\'admin/index.php\',\'admin/settings.app/settings.php\',', '2012-08-21 07:50:18', '2012-08-21 07:50:18', '2012-08-21 07:50:18');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('821', 'job_database_backup:title_backup_directory', 'Backup Directory', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:29', '2012-08-21 18:20:29', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('822', 'job_database_backup:description_backup_directory', 'Path to the directory where the backups are stored with trailing slash. Relative to the system base path.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:29', '2012-08-21 18:20:29', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('823', 'job_database_backup:title_backup_filename', 'Backup Filename', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('824', 'job_database_backup:description_backup_filename', 'The strftime() supported filename of the backup.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('825', 'job_database_backup:title_backup_frequency', 'Backup Frequency', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('826', 'job_database_backup:description_backup_frequency', 'How often the database should be backed up.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('827', 'job_database_backup:title_expire_days', 'Expire Days', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('828', 'job_database_backup:description_expire_days', 'Delete backups older than the given amount of days.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('829', 'job_database_backup:title_ignore_tables', 'Ignore Tables', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('830', 'job_database_backup:description_ignore_tables', 'A coma separated list of tables NOT to backup content data from.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('831', 'job_database_backup:title_priority', 'Priority', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

insert into c_translations (`id`, `code`, `text_en`, `text_sv`, `html`, `pages`, `date_created`, `date_updated`, `date_accessed`) values ('832', 'job_database_backup:description_priority', 'Process this module in the given priority order.', '', '', '\'admin/index.php\',\'includes/modules/jobs/job_database_backup.inc.php\',', '2012-08-21 18:20:30', '2012-08-21 18:20:30', '2012-08-21 18:34:56');

drop table if exists c_zones;
create table c_zones (
  `id` int(11) not null auto_increment,
  `country_code` varchar(4) not null ,
  `code` varchar(8) not null ,
  `name` varchar(64) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_zones (`id`, `country_code`, `code`, `name`, `date_updated`, `date_created`) values ('1', 'SE', 'VML', 'Västmanland', '2012-07-03 17:00:36', '0000-00-00 00:00:00');

insert into c_zones (`id`, `country_code`, `code`, `name`, `date_updated`, `date_created`) values ('2', 'SE', 'U', 'Uppland', '2012-07-03 17:00:36', '0000-00-00 00:00:00');

insert into c_zones (`id`, `country_code`, `code`, `name`, `date_updated`, `date_created`) values ('3', 'SE', 'H', 'Halland', '2012-07-03 17:00:36', '2012-07-03 17:00:36');

drop table if exists c_zones_to_geo_zones;
create table c_zones_to_geo_zones (
  `id` int(11) not null auto_increment,
  `geo_zone_id` int(11) not null ,
  `country_code` varchar(2) not null ,
  `zone_code` varchar(8) not null ,
  `date_updated` datetime not null ,
  `date_created` datetime not null ,
  PRIMARY KEY (id)
);

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('6', '2', 'SE', '', '2012-07-17 04:34:34', '2012-07-03 15:54:56');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('21', '3', 'SE', '', '2012-08-07 01:16:02', '2012-08-07 01:16:02');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('11', '1', 'BE', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('42', '1', 'AT', '', '2012-08-21 10:05:02', '2012-08-21 10:05:02');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('12', '1', 'DK', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('13', '1', 'FI', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('14', '1', 'FR', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('15', '1', 'DE', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('16', '1', 'NL', '', '2012-08-21 10:05:02', '2012-07-03 19:57:48');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('41', '1', 'SI', '', '2012-08-21 10:05:02', '2012-08-21 10:04:44');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('23', '1', 'CY', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('22', '1', 'BG', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('24', '1', 'CZ', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('25', '1', 'EE', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('26', '1', 'GR', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('27', '1', 'HU', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('28', '1', 'IE', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('29', '1', 'IT', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('30', '1', 'LV', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('31', '1', 'LT', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('32', '1', 'LU', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('33', '1', 'MT', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('34', '1', 'PL', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('35', '1', 'PT', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('36', '1', 'RO', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('37', '1', 'SK', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('38', '1', 'ES', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

insert into c_zones_to_geo_zones (`id`, `geo_zone_id`, `country_code`, `zone_code`, `date_updated`, `date_created`) values ('39', '1', 'GB', '', '2012-08-21 10:05:02', '2012-08-21 09:59:19');

drop table if exists hb_Webcams;
create table hb_Webcams (
  `ID` int(11) not null auto_increment,
  `Name` varchar(24) not null ,
  `cfgCopyright` tinyint(1) not null ,
  `cfgRating` varchar(16) not null ,
  `LastUpdate` datetime not null ,
  PRIMARY KEY (ID)
);

insert into hb_Webcams (`ID`, `Name`, `cfgCopyright`, `cfgRating`, `LastUpdate`) values ('1', 'Purple', '1', '', '0000-00-00 00:00:00');

drop table if exists lc_addresses;
create table lc_addresses (
  `id` int(11) not null auto_increment,
  `customer_id` int(11) not null ,
  `company` varchar(64) not null ,
  `name` varchar(64) not null ,
  `address1` varchar(64) not null ,
  `address2` varchar(64) not null ,
  `city` varchar(32) not null ,
  `postcode` varchar(8) not null ,
  `country_id` int(11) not null ,
  `zone_id` int(11) not null ,
  PRIMARY KEY (id)
);

drop table if exists miniblog;
create table miniblog (
  `post_id` int(20) not null auto_increment,
  `post_slug` varchar(255) not null ,
  `post_title` varchar(255) not null ,
  `post_content` longtext not null ,
  `date` int(20) default '0' not null ,
  `published` int(1) default '0' not null ,
  PRIMARY KEY (post_id)
);

insert into miniblog (`post_id`, `post_slug`, `post_title`, `post_content`, `date`, `published`) values ('1', 'welcome-to-miniblog', 'Welcome to miniblog!', '<p>Welcome to your new installation of miniblog. To remove or edit this post, add new posts and change options login to your admin panel.</p>', '1330530508', '1');

insert into miniblog (`post_id`, `post_slug`, `post_title`, `post_content`, `date`, `published`) values ('2', 'security-with-openbasedir', 'Security with open_basedir', 'Most web hosting services suffer from the same issue. When a customer gets a domain infected, it spreads to all his domains on the web hosting account.

This is usually because all folders and files are by the same ownership.

Using php open_basedir you can isolate domains from eachother by preventing file access in a the neighbour web root. This sometimes require creating a custom php.ini since it might not be enabled through htaccess php_flag.

If your web host don\'t support changing your open_basedir setting, or a custom php.ini. Move to one that does. Having all my domains infected from an open source vulnerability, I discovered the power of open_basedir.', '1330809783', '1');

drop table if exists miniblog_config;
create table miniblog_config (
  `config_name` varchar(255) not null ,
  `config_value` varchar(255) not null ,
  `config_explain` longtext not null ,
  `test` tinyint(1) not null 
);

insert into miniblog_config (`config_name`, `config_value`, `config_explain`, `test`) values ('posts-per-page', '5', 'Posts displayed each page', '');

insert into miniblog_config (`config_name`, `config_value`, `config_explain`, `test`) values ('date-format', 'F d, Y', 'Date format as per the PHP date function <a href=\"http://www.php.net/date\">here</a>', '');

insert into miniblog_config (`config_name`, `config_value`, `config_explain`, `test`) values ('password', '5f4dcc3b5aa765d61d8327deb882cf99', 'Admin password', '');

insert into miniblog_config (`config_name`, `config_value`, `config_explain`, `test`) values ('miniblog-filename', 'index.php', 'Name of the file which miniblog.php is included into', '');

insert into miniblog_config (`config_name`, `config_value`, `config_explain`, `test`) values ('use-modrewrite', '1', 'Use modrewrite for post URLs - use 1 for yes, 0 for no.', '');

