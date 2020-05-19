<?php
/**
 * 本破解程序由VIP资源网提供
 * VIP资源网 www.vip-zyw.com
 *
 */
pdo_query("CREATE TABLE IF NOT EXISTS `deam_deamx_food_coupon_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL,
  `coupon_uniacid` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `card_id` varchar(100) NOT NULL,
  `activity_bg_color` varchar(100) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `gift_num` int(11) NOT NULL,
  `max_partic_times_act` int(11) NOT NULL,
  `max_partic_times_one_day` int(11) NOT NULL,
  `min_amt` int(11) NOT NULL,
  `to_user` varchar(100) NOT NULL,
  `createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deam_deamx_food_coupon_activity','id')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','uniacid')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','coupon_uniacid')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `coupon_uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','activity_id')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `activity_id` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','card_id')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `card_id` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','activity_bg_color')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `activity_bg_color` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','begin_time')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `begin_time` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','end_time')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `end_time` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','gift_num')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `gift_num` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','max_partic_times_act')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `max_partic_times_act` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','max_partic_times_one_day')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `max_partic_times_one_day` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','min_amt')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `min_amt` int(11) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','to_user')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `to_user` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deam_deamx_food_coupon_activity','createtime')) {pdo_query("ALTER TABLE ".tablename('deam_deamx_food_coupon_activity')." ADD   `createtime` int(11) NOT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_amore_food` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `hid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cai_name` varchar(64) NOT NULL DEFAULT '',
  `img` varchar(128) NOT NULL DEFAULT '',
  `pricetype` tinyint(1) NOT NULL DEFAULT '0',
  `price` int(10) unsigned NOT NULL DEFAULT '0',
  `flavor` varchar(500) NOT NULL DEFAULT '',
  `brief` varchar(255) NOT NULL DEFAULT '',
  `miaoshu` text NOT NULL,
  `print_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `discount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `stock` smallint(5) unsigned NOT NULL DEFAULT '0',
  `unit` varchar(18) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `canhe` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('amore_food','id')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD 
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('amore_food','sid')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `sid` int(10) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','hid')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `hid` bigint(20) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','cid')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `cid` bigint(20) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','cai_name')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `cai_name` varchar(64) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food','img')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `img` varchar(128) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food','pricetype')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `pricetype` tinyint(1) NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','price')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `price` int(10) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','flavor')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `flavor` varchar(500) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food','brief')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `brief` varchar(255) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food','miaoshu')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `miaoshu` text NOT NULL");}
if(!pdo_fieldexists('amore_food','print_id')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `print_id` bigint(20) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','discount')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `discount` smallint(5) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','stock')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `stock` smallint(5) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food','unit')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `unit` varchar(18) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food','status')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `status` tinyint(1) NOT NULL DEFAULT '1'");}
if(!pdo_fieldexists('amore_food','canhe')) {pdo_query("ALTER TABLE ".tablename('amore_food')." ADD   `canhe` int(10) NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_amore_food_cate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sid` int(10) unsigned NOT NULL DEFAULT '0',
  `hid` bigint(20) unsigned NOT NULL DEFAULT '0',
  `cate_name` varchar(32) NOT NULL DEFAULT '',
  `sort` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('amore_food_cate','id')) {pdo_query("ALTER TABLE ".tablename('amore_food_cate')." ADD 
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('amore_food_cate','sid')) {pdo_query("ALTER TABLE ".tablename('amore_food_cate')." ADD   `sid` int(10) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food_cate','hid')) {pdo_query("ALTER TABLE ".tablename('amore_food_cate')." ADD   `hid` bigint(20) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food_cate','cate_name')) {pdo_query("ALTER TABLE ".tablename('amore_food_cate')." ADD   `cate_name` varchar(32) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food_cate','sort')) {pdo_query("ALTER TABLE ".tablename('amore_food_cate')." ADD   `sort` smallint(4) unsigned NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_amore_food_spec` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cai_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `spec` varchar(64) NOT NULL DEFAULT '',
  `price` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('amore_food_spec','id')) {pdo_query("ALTER TABLE ".tablename('amore_food_spec')." ADD 
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('amore_food_spec','cai_id')) {pdo_query("ALTER TABLE ".tablename('amore_food_spec')." ADD   `cai_id` bigint(20) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food_spec','spec')) {pdo_query("ALTER TABLE ".tablename('amore_food_spec')." ADD   `spec` varchar(64) NOT NULL DEFAULT ''");}
if(!pdo_fieldexists('amore_food_spec','price')) {pdo_query("ALTER TABLE ".tablename('amore_food_spec')." ADD   `price` int(10) unsigned NOT NULL DEFAULT '0'");}
if(!pdo_fieldexists('amore_food_spec','sort')) {pdo_query("ALTER TABLE ".tablename('amore_food_spec')." ADD   `sort` int(10) unsigned NOT NULL DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `realname` varchar(100) DEFAULT NULL,
  `sex` tinyint(3) DEFAULT NULL,
  `telphone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `address_road` varchar(255) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `number` varchar(255) DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `is_default` tinyint(3) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_address','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_address','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `openid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','member_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `member_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','realname')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `realname` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','sex')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `sex` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','telphone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `telphone` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','address')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `address` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','address_road')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `address_road` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','latitude')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `latitude` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','longitude')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `longitude` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','number')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `number` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','tag')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `tag` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','is_default')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `is_default` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_address','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_address')." ADD   `createtime` int(11) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_adv` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `sortid` int(10) DEFAULT NULL,
  `adv_title` varchar(255) DEFAULT NULL,
  `adv_img` varchar(255) DEFAULT NULL,
  `adv_url` varchar(255) DEFAULT NULL,
  `adv_isshow` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_adv','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_adv','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_adv','sortid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `sortid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_adv','adv_title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `adv_title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_adv','adv_img')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `adv_img` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_adv','adv_url')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `adv_url` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_adv','adv_isshow')) {pdo_query("ALTER TABLE ".tablename('deamx_food_adv')." ADD   `adv_isshow` tinyint(1) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_class` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `store_id` int(10) DEFAULT NULL,
  `sortid` int(10) DEFAULT NULL,
  `classname` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `createtime` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_class','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_class','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_class','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `store_id` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_class','sortid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `sortid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_class','classname')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `classname` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_class','enabled')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `enabled` tinyint(1) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_class','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_class')." ADD   `createtime` int(10) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `coupon_uniacid` int(10) DEFAULT NULL,
  `card_id` varchar(255) DEFAULT NULL,
  `type` tinyint(3) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `code_type` tinyint(3) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `sub_title` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `notice` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `quantity` int(10) DEFAULT NULL,
  `use_custom_code` tinyint(3) DEFAULT NULL,
  `bind_openid` tinyint(3) DEFAULT NULL,
  `can_share` tinyint(3) DEFAULT NULL,
  `can_give_friend` tinyint(3) DEFAULT NULL,
  `get_limit` int(10) DEFAULT NULL,
  `service_phone` varchar(20) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `is_display` int(10) DEFAULT NULL,
  `is_selfconsume` int(10) DEFAULT NULL,
  `promotion_url_name` varchar(255) DEFAULT NULL,
  `promotion_url` varchar(255) DEFAULT NULL,
  `promotion_url_sub_title` varchar(255) DEFAULT NULL,
  `source` int(10) DEFAULT NULL,
  `date_info` varchar(255) DEFAULT NULL,
  `extra` varchar(1000) DEFAULT NULL,
  `dosage` int(10) unsigned DEFAULT '0',
  `least_cost` int(11) DEFAULT '0',
  `reduce_cost` int(11) DEFAULT '0',
  `can_use_with_other_discount` tinyint(3) NOT NULL DEFAULT '1',
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_coupon','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_coupon','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','coupon_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `coupon_uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','card_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `card_id` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `type` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','logo_url')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `logo_url` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','code_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `code_type` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','brand_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `brand_name` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','sub_title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `sub_title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `color` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','notice')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `notice` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','description')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `description` varchar(1000) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','quantity')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `quantity` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','use_custom_code')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `use_custom_code` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','bind_openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `bind_openid` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','can_share')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `can_share` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','can_give_friend')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `can_give_friend` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','get_limit')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `get_limit` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','service_phone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `service_phone` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `status` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','is_display')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `is_display` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','is_selfconsume')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `is_selfconsume` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','promotion_url_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `promotion_url_name` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','promotion_url')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `promotion_url` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','promotion_url_sub_title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `promotion_url_sub_title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','source')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `source` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','date_info')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `date_info` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','extra')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `extra` varchar(1000) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon','dosage')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `dosage` int(10) unsigned DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon','least_cost')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `least_cost` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon','reduce_cost')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `reduce_cost` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon','can_use_with_other_discount')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `can_use_with_other_discount` tinyint(3) NOT NULL DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_coupon','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon')." ADD   `createtime` int(11) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_coupon_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL,
  `coupon_uniacid` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `card_id` varchar(100) NOT NULL,
  `activity_bg_color` varchar(100) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `gift_num` int(11) NOT NULL,
  `max_partic_times_act` int(11) NOT NULL,
  `max_partic_times_one_day` int(11) NOT NULL,
  `min_amt` int(11) NOT NULL,
  `to_user` varchar(100) NOT NULL,
  `createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_coupon_activity','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_coupon_activity','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','coupon_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `coupon_uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','activity_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `activity_id` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','card_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `card_id` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','activity_bg_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `activity_bg_color` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','begin_time')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `begin_time` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','end_time')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `end_time` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','gift_num')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `gift_num` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','max_partic_times_act')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `max_partic_times_act` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','max_partic_times_one_day')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `max_partic_times_one_day` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','min_amt')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `min_amt` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','to_user')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `to_user` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_activity','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_activity')." ADD   `createtime` int(11) NOT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_coupon_record` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) unsigned NOT NULL,
  `acid` int(10) unsigned NOT NULL,
  `card_id` varchar(50) NOT NULL,
  `openid` varchar(50) NOT NULL,
  `unionid` varchar(100) DEFAULT NULL,
  `friend_openid` varchar(50) NOT NULL,
  `givebyfriend` tinyint(3) unsigned NOT NULL,
  `code` varchar(50) NOT NULL,
  `hash` varchar(32) NOT NULL,
  `addtime` int(10) unsigned NOT NULL,
  `usetime` int(10) unsigned NOT NULL,
  `order_id` int(10) DEFAULT '0',
  `starttime` int(11) DEFAULT NULL,
  `endtime` int(11) DEFAULT NULL,
  `status` tinyint(3) NOT NULL,
  `clerk_name` varchar(15) NOT NULL,
  `clerk_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `clerk_type` tinyint(3) unsigned NOT NULL,
  `couponid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `grantmodule` varchar(255) NOT NULL,
  `remark` varchar(255) NOT NULL,
  `rule_id` int(10) DEFAULT '0',
  `source_orderid` int(10) DEFAULT '0',
  `granttype` tinyint(4) NOT NULL COMMENT '获取卡券的方式：1 兑换，2 扫码，3派发',
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`,`acid`),
  KEY `card_id` (`card_id`),
  KEY `hash` (`hash`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_coupon_record','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD 
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_coupon_record','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `uniacid` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','acid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `acid` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','card_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `card_id` varchar(50) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `openid` varchar(50) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','unionid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `unionid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','friend_openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `friend_openid` varchar(50) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','givebyfriend')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `givebyfriend` tinyint(3) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','code')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `code` varchar(50) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','hash')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `hash` varchar(32) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','addtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `addtime` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','usetime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `usetime` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','order_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `order_id` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon_record','starttime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `starttime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','endtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `endtime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `status` tinyint(3) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','clerk_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `clerk_name` varchar(15) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','clerk_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `clerk_id` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `store_id` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','clerk_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `clerk_type` tinyint(3) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','couponid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `couponid` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','uid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `uid` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','grantmodule')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `grantmodule` varchar(255) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','remark')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `remark` varchar(255) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_record','rule_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `rule_id` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon_record','source_orderid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `source_orderid` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_coupon_record','granttype')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   `granttype` tinyint(4) NOT NULL COMMENT '获取卡券的方式：1 兑换，2 扫码，3派发'");}
if(!pdo_fieldexists('deamx_food_coupon_record','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   PRIMARY KEY (`id`)");}
if(!pdo_fieldexists('deamx_food_coupon_record','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   KEY `uniacid` (`uniacid`,`acid`)");}
if(!pdo_fieldexists('deamx_food_coupon_record','card_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_record')." ADD   KEY `card_id` (`card_id`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_coupon_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `starttime` int(11) DEFAULT NULL,
  `endtime` int(11) DEFAULT NULL,
  `reduce_cost` decimal(10,2) DEFAULT NULL,
  `repeat_send` tinyint(3) DEFAULT NULL,
  `limit_send` int(11) DEFAULT NULL,
  `coupon_info` text,
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_coupon_rules','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_coupon_rules','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `status` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','starttime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `starttime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','endtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `endtime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','reduce_cost')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `reduce_cost` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','repeat_send')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `repeat_send` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','limit_send')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `limit_send` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_coupon_rules','coupon_info')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `coupon_info` text");}
if(!pdo_fieldexists('deamx_food_coupon_rules','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_coupon_rules')." ADD   `createtime` int(11) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_credits_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `uniacid` int(11) NOT NULL,
  `credittype` varchar(10) NOT NULL,
  `num` decimal(10,2) NOT NULL,
  `operator` int(10) unsigned NOT NULL,
  `module` varchar(30) NOT NULL,
  `clerk_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `remark` varchar(200) NOT NULL,
  `clerk_type` tinyint(3) unsigned NOT NULL,
  `real_uniacid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uniacid` (`uniacid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_credits_record','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_credits_record','uid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `uid` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','credittype')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `credittype` varchar(10) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','num')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `num` decimal(10,2) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','operator')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `operator` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','module')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `module` varchar(30) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','clerk_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `clerk_id` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `store_id` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `createtime` int(10) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','remark')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `remark` varchar(200) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','clerk_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `clerk_type` tinyint(3) unsigned NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','real_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   `real_uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_credits_record','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   PRIMARY KEY (`id`)");}
if(!pdo_fieldexists('deamx_food_credits_record','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_credits_record')." ADD   KEY `uniacid` (`uniacid`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_deliver_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(200) NOT NULL,
  `status` int(10) DEFAULT NULL,
  `remark` varchar(500) NOT NULL,
  `updatetime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_deliver_log','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_deliver_log','order_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD   `order_id` varchar(200) NOT NULL");}
if(!pdo_fieldexists('deamx_food_deliver_log','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD   `status` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_deliver_log','remark')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD   `remark` varchar(500) NOT NULL");}
if(!pdo_fieldexists('deamx_food_deliver_log','updatetime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD   `updatetime` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_deliver_log','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_deliver_log')." ADD   PRIMARY KEY (`id`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_desknumber` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `wxacode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_desknumber','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_desknumber')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_desknumber','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_desknumber')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_desknumber','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_desknumber')." ADD   `store_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_desknumber','name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_desknumber')." ADD   `name` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_desknumber','wxacode')) {pdo_query("ALTER TABLE ".tablename('deamx_food_desknumber')." ADD   `wxacode` varchar(255) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_goods` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `store_id` int(10) DEFAULT NULL,
  `displayorder` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `intro` varchar(255) DEFAULT NULL,
  `class_id` int(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `is_pbox` tinyint(3) DEFAULT '0',
  `pbox_price` decimal(10,2) DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `hasoption` tinyint(1) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `createtime` int(10) DEFAULT NULL,
  `realsales` int(10) DEFAULT '0',
  `old_option` tinyint(4) DEFAULT '1',
  `goods_attr` text,
  `goods_specs_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_goods','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_goods','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `store_id` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','displayorder')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `displayorder` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `name` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','intro')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `intro` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','class_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `class_id` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','is_pbox')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `is_pbox` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods','pbox_price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `pbox_price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','unit')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `unit` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','img')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `img` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','hasoption')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `hasoption` tinyint(1) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `status` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `createtime` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_goods','realsales')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `realsales` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods','old_option')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `old_option` tinyint(4) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_goods','goods_attr')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `goods_attr` text");}
if(!pdo_fieldexists('deamx_food_goods','goods_specs_title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods')." ADD   `goods_specs_title` varchar(255) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_goods_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `store_id` int(11) DEFAULT '0',
  `goodsid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `stock` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `specs` text,
  `realsales` int(11) DEFAULT '0',
  `is_new` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_goods_option','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_goods_option','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `uniacid` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `store_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','goodsid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `goodsid` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `title` varchar(50) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_option','marketprice')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `marketprice` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_goods_option','stock')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `stock` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','displayorder')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `displayorder` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','specs')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `specs` text");}
if(!pdo_fieldexists('deamx_food_goods_option','realsales')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `realsales` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','is_new')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   `is_new` tinyint(4) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_option','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   PRIMARY KEY (`id`)");}
if(!pdo_fieldexists('deamx_food_goods_option','idx_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   KEY `idx_uniacid` (`uniacid`)");}
if(!pdo_fieldexists('deamx_food_goods_option','idx_goodsid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_option')." ADD   KEY `idx_goodsid` (`goodsid`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_goods_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `store_id` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `displaytype` tinyint(3) DEFAULT '0',
  `content` text,
  `displayorder` int(11) DEFAULT '0',
  `propId` varchar(255) DEFAULT '',
  `is_new` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_goods_spec','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_goods_spec','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `uniacid` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `store_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','goodsid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `goodsid` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `title` varchar(50) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_spec','description')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `description` varchar(1000) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_spec','displaytype')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `displaytype` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','content')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `content` text");}
if(!pdo_fieldexists('deamx_food_goods_spec','displayorder')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `displayorder` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','propId')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `propId` varchar(255) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_spec','is_new')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   `is_new` tinyint(4) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   PRIMARY KEY (`id`)");}
if(!pdo_fieldexists('deamx_food_goods_spec','idx_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   KEY `idx_uniacid` (`uniacid`)");}
if(!pdo_fieldexists('deamx_food_goods_spec','idx_goodsid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec')." ADD   KEY `idx_goodsid` (`goodsid`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_goods_spec_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT '0',
  `store_id` int(11) DEFAULT '0',
  `specid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `show` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `valueId` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_uniacid` (`uniacid`),
  KEY `idx_specid` (`specid`),
  KEY `idx_show` (`show`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_goods_spec_item','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `uniacid` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `store_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','specid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `specid` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `title` varchar(255) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','show')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `show` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','displayorder')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `displayorder` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','valueId')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `valueId` varchar(255) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','virtual')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   `virtual` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   PRIMARY KEY (`id`)");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','idx_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   KEY `idx_uniacid` (`uniacid`)");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','idx_specid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   KEY `idx_specid` (`specid`)");}
if(!pdo_fieldexists('deamx_food_goods_spec_item','idx_show')) {pdo_query("ALTER TABLE ".tablename('deamx_food_goods_spec_item')." ADD   KEY `idx_show` (`show`)");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `credit1` decimal(10,2) DEFAULT NULL,
  `credit2` decimal(10,2) DEFAULT '0.00',
  `realname` varchar(255) DEFAULT NULL,
  `telephone` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_members','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_members','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_members','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `openid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_members','credit1')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `credit1` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_members','credit2')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `credit2` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_members','realname')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `realname` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_members','telephone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_members')." ADD   `telephone` varchar(25) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `from_openid` varchar(200) DEFAULT NULL,
  `store_id` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT NULL COMMENT '1:订单通知;2:呼叫通知;',
  `title` varchar(255) DEFAULT NULL,
  `content` text COMMENT '文字/音频',
  `status` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_notice','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_notice','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notice','from_openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `from_openid` varchar(200) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notice','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `store_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_notice','type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `type` tinyint(3) DEFAULT NULL COMMENT '1:订单通知;2:呼叫通知;'");}
if(!pdo_fieldexists('deamx_food_notice','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notice','content')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `content` text COMMENT '文字/音频'");}
if(!pdo_fieldexists('deamx_food_notice','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `status` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_notice','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notice')." ADD   `createtime` int(11) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_notify_id` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `form_id` varchar(100) DEFAULT NULL,
  `last_count` int(4) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_notify_id','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_notify_id','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notify_id','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD   `openid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notify_id','form_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD   `form_id` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notify_id','last_count')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD   `last_count` int(4) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_notify_id','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_notify_id')." ADD   `createtime` int(11) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `ordersn` varchar(100) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `order_type` tinyint(3) DEFAULT '1',
  `pay_type` tinyint(3) DEFAULT '1',
  `desk_id` int(11) DEFAULT '0',
  `goods_list` text,
  `count` int(11) DEFAULT NULL,
  `pbox_fee` decimal(10,2) DEFAULT '0.00',
  `send_fee` decimal(10,2) DEFAULT '0.00',
  `price` decimal(10,2) DEFAULT NULL,
  `need_pay` decimal(10,2) DEFAULT NULL,
  `enoughdeduct` decimal(10,2) DEFAULT '0.00',
  `use_coupon` int(10) DEFAULT '0',
  `coupon_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0为普通卡券，1为no_cash',
  `coupon_price` decimal(10,2) DEFAULT '0.00',
  `pay_price` decimal(10,2) DEFAULT NULL,
  `status` tinyint(3) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `paytime` int(11) DEFAULT '0',
  `prepay_id` varchar(255) DEFAULT NULL,
  `message_count` int(10) DEFAULT '0',
  `order_number` varchar(20) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `receivetime` int(11) DEFAULT NULL,
  `is_prompt` tinyint(3) DEFAULT '1',
  `address_info` text,
  `need_send_coupon` tinyint(3) DEFAULT '0',
  `is_send_coupon` tinyint(3) DEFAULT '0',
  `deliver_type` tinyint(3) DEFAULT '0',
  `deliver_dada_failreason` varchar(255) DEFAULT NULL,
  `refund_fee` decimal(10,2) DEFAULT '0.00',
  `print_count` int(11) DEFAULT '0',
  `getfood_time` varchar(255) DEFAULT NULL,
  `wechat_paytype` tinyint(4) DEFAULT '0',
  `is_get` tinyint(4) DEFAULT '1',
  `reserved_telephone` varchar(20) DEFAULT NULL COMMENT '自取单预留手机号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_order','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_order','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','ordersn')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `ordersn` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `store_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `openid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','member_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `member_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','order_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `order_type` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_order','pay_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `pay_type` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_order','desk_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `desk_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','goods_list')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `goods_list` text");}
if(!pdo_fieldexists('deamx_food_order','count')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `count` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','pbox_fee')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `pbox_fee` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_order','send_fee')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `send_fee` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_order','price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','need_pay')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `need_pay` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','enoughdeduct')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `enoughdeduct` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_order','use_coupon')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `use_coupon` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','coupon_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `coupon_type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0为普通卡券，1为no_cash'");}
if(!pdo_fieldexists('deamx_food_order','coupon_price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `coupon_price` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_order','pay_price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `pay_price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `status` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','remark')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `remark` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','paytime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `paytime` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','prepay_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `prepay_id` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','message_count')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `message_count` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','order_number')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `order_number` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `createtime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','receivetime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `receivetime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','is_prompt')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `is_prompt` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_order','address_info')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `address_info` text");}
if(!pdo_fieldexists('deamx_food_order','need_send_coupon')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `need_send_coupon` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','is_send_coupon')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `is_send_coupon` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','deliver_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `deliver_type` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','deliver_dada_failreason')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `deliver_dada_failreason` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','refund_fee')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `refund_fee` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_order','print_count')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `print_count` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','getfood_time')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `getfood_time` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order','wechat_paytype')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `wechat_paytype` tinyint(4) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order','is_get')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `is_get` tinyint(4) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_order','reserved_telephone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order')." ADD   `reserved_telephone` varchar(20) DEFAULT NULL COMMENT '自取单预留手机号'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `orderid` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `optionid` int(11) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `daytime` int(11) DEFAULT NULL,
  `optionname` varchar(255) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_order_goods','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_order_goods','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','orderid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `orderid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `store_id` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_order_goods','goodsid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `goodsid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','total')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `total` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','optionid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `optionid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `createtime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','daytime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `daytime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','optionname')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `optionname` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `openid` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_order_goods','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_order_goods')." ADD   `title` varchar(255) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_printer` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` tinyint(3) DEFAULT NULL,
  `print_type` tinyint(3) DEFAULT '0',
  `print_data` text,
  `print_class` varchar(1000) DEFAULT NULL,
  `createtime` int(11) DEFAULT NULL,
  `merchid` int(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `order_qrcode` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_printer','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_printer','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `store_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `type` tinyint(3) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','print_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `print_type` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_printer','print_data')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `print_data` text");}
if(!pdo_fieldexists('deamx_food_printer','print_class')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `print_class` varchar(1000) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `createtime` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','merchid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `merchid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_printer','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `status` tinyint(1) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_printer','order_qrcode')) {pdo_query("ALTER TABLE ".tablename('deamx_food_printer')." ADD   `order_qrcode` tinyint(1) DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_recharge_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL,
  `openid` varchar(100) NOT NULL,
  `uid` int(11) NOT NULL,
  `ordersn` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT '会员充值',
  `price` decimal(10,2) NOT NULL,
  `gives` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `paytime` int(11) NOT NULL,
  `prepay_id` varchar(255) DEFAULT NULL,
  `createtime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_recharge_log','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_recharge_log','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `uniacid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','openid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `openid` varchar(100) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','uid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `uid` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','ordersn')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `ordersn` varchar(50) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `title` varchar(255) DEFAULT '会员充值'");}
if(!pdo_fieldexists('deamx_food_recharge_log','price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `price` decimal(10,2) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','gives')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `gives` decimal(10,2) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `status` tinyint(1) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','paytime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `paytime` int(11) NOT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','prepay_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `prepay_id` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_recharge_log','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_recharge_log')." ADD   `createtime` int(11) DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_refundlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `refund_type` tinyint(2) DEFAULT '1',
  `refund_uniontid` varchar(64) DEFAULT NULL,
  `reason` varchar(100) DEFAULT NULL,
  `uniontid` varchar(64) DEFAULT NULL,
  `fee` decimal(10,2) DEFAULT NULL,
  `status` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_refundlog','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_refundlog','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_refundlog','refund_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `refund_type` tinyint(2) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_refundlog','refund_uniontid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `refund_uniontid` varchar(64) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_refundlog','reason')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `reason` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_refundlog','uniontid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `uniontid` varchar(64) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_refundlog','fee')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `fee` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_refundlog','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_refundlog')." ADD   `status` int(2) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_settings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  `bg_color` varchar(20) DEFAULT NULL,
  `fg_color` varchar(20) DEFAULT NULL,
  `share_title` varchar(255) DEFAULT NULL,
  `area_limit` int(10) DEFAULT '0',
  `tencent_map_apikey` varchar(100) DEFAULT NULL,
  `single_storeid` int(10) DEFAULT NULL,
  `store_blogo` varchar(255) DEFAULT NULL,
  `copyright` varchar(255) DEFAULT NULL,
  `template_status` tinyint(3) DEFAULT '0',
  `template_id` varchar(100) DEFAULT NULL,
  `template_id_param` varchar(200) DEFAULT '',
  `get_food_template_id` varchar(100) DEFAULT NULL,
  `get_food_template_id_param` varchar(200) DEFAULT '',
  `takeout_template_id` varchar(255) DEFAULT NULL,
  `takeout_template_id_param` varchar(200) DEFAULT '',
  `sms_status` tinyint(3) DEFAULT '0',
  `sms_type` tinyint(3) DEFAULT '1',
  `sms_params` text,
  `login_logo` varchar(255) DEFAULT NULL,
  `login_bg` varchar(255) DEFAULT NULL,
  `about_us` text,
  `store_count` int(11) DEFAULT '0',
  `wxapp_scan` tinyint(3) DEFAULT '1',
  `wxapp_scan_name` varchar(20) DEFAULT '堂食',
  `wxapp_scan_color` varchar(20) DEFAULT '#ffa1a1',
  `wxapp_scan_intro` varchar(60) DEFAULT '点我扫码进入',
  `wxapp_scan_logo` varchar(255) DEFAULT NULL,
  `wxapp_takeout` tinyint(3) DEFAULT '1',
  `wxapp_takeout_name` varchar(20) DEFAULT '外卖',
  `wxapp_takeout_color` varchar(20) DEFAULT '#faa040',
  `wxapp_takeout_intro` varchar(60) DEFAULT '点餐快速配送',
  `wxapp_takeout_logo` varchar(255) DEFAULT NULL,
  `wxapp_getself` tinyint(3) DEFAULT '1',
  `wxapp_getself_name` varchar(20) DEFAULT '自取',
  `wxapp_getself_color` varchar(20) DEFAULT '#76bdef',
  `wxapp_getself_intro` varchar(60) DEFAULT '点我开始订餐',
  `wxapp_getself_logo` varchar(255) DEFAULT NULL,
  `coupon_uniacid` int(10) DEFAULT NULL,
  `deliver_dada_status` tinyint(3) DEFAULT '0',
  `deliver_dada_app_key` varchar(100) DEFAULT NULL,
  `deliver_dada_app_secret` varchar(100) DEFAULT NULL,
  `deliver_dada_shopid` int(10) DEFAULT NULL,
  `bell_settings` text,
  `auth_setting` varchar(300) DEFAULT NULL,
  `plugins` text,
  `sets` text,
  `wxacode_color` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_settings','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_settings','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `name` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `type` tinyint(1) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','bg_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `bg_color` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','fg_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `fg_color` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','share_title')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `share_title` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','area_limit')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `area_limit` int(10) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_settings','tencent_map_apikey')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `tencent_map_apikey` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','single_storeid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `single_storeid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','store_blogo')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `store_blogo` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','copyright')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `copyright` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','template_status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `template_status` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_settings','template_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `template_id` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','template_id_param')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `template_id_param` varchar(200) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_settings','get_food_template_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `get_food_template_id` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','get_food_template_id_param')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `get_food_template_id_param` varchar(200) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_settings','takeout_template_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `takeout_template_id` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','takeout_template_id_param')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `takeout_template_id_param` varchar(200) DEFAULT ''");}
if(!pdo_fieldexists('deamx_food_settings','sms_status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `sms_status` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_settings','sms_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `sms_type` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_settings','sms_params')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `sms_params` text");}
if(!pdo_fieldexists('deamx_food_settings','login_logo')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `login_logo` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','login_bg')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `login_bg` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','about_us')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `about_us` text");}
if(!pdo_fieldexists('deamx_food_settings','store_count')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `store_count` int(11) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_scan')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_scan` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_scan_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_scan_name` varchar(20) DEFAULT '堂食'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_scan_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_scan_color` varchar(20) DEFAULT '#ffa1a1'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_scan_intro')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_scan_intro` varchar(60) DEFAULT '点我扫码进入'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_scan_logo')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_scan_logo` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_takeout')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_takeout` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_takeout_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_takeout_name` varchar(20) DEFAULT '外卖'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_takeout_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_takeout_color` varchar(20) DEFAULT '#faa040'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_takeout_intro')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_takeout_intro` varchar(60) DEFAULT '点餐快速配送'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_takeout_logo')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_takeout_logo` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_getself')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_getself` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_getself_name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_getself_name` varchar(20) DEFAULT '自取'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_getself_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_getself_color` varchar(20) DEFAULT '#76bdef'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_getself_intro')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_getself_intro` varchar(60) DEFAULT '点我开始订餐'");}
if(!pdo_fieldexists('deamx_food_settings','wxapp_getself_logo')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxapp_getself_logo` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','coupon_uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `coupon_uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','deliver_dada_status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `deliver_dada_status` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_settings','deliver_dada_app_key')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `deliver_dada_app_key` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','deliver_dada_app_secret')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `deliver_dada_app_secret` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','deliver_dada_shopid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `deliver_dada_shopid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','bell_settings')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `bell_settings` text");}
if(!pdo_fieldexists('deamx_food_settings','auth_setting')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `auth_setting` varchar(300) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_settings','plugins')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `plugins` text");}
if(!pdo_fieldexists('deamx_food_settings','sets')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `sets` text");}
if(!pdo_fieldexists('deamx_food_settings','wxacode_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_settings')." ADD   `wxacode_color` tinyint(4) DEFAULT '0'");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_store` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniacid` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `district_edit_self` tinyint(3) DEFAULT '0',
  `address` varchar(255) DEFAULT NULL,
  `longitude` varchar(20) DEFAULT NULL,
  `latitude` varchar(20) DEFAULT NULL,
  `status` tinyint(3) DEFAULT '1',
  `is_getself` tinyint(3) DEFAULT '1',
  `is_takeout` tinyint(3) DEFAULT '1',
  `close_reason` varchar(255) DEFAULT NULL,
  `starttime` varchar(20) DEFAULT NULL,
  `endtime` varchar(20) DEFAULT NULL,
  `bg_color` varchar(20) DEFAULT NULL,
  `fg_color` varchar(20) DEFAULT NULL,
  `imgs` text,
  `operator` text,
  `notice_tel` varchar(1000) DEFAULT NULL,
  `remark_text` varchar(255) DEFAULT NULL,
  `auto_order` tinyint(3) DEFAULT '1',
  `wxacode` varchar(255) DEFAULT NULL,
  `start_price` decimal(10,2) DEFAULT NULL,
  `send_limit` decimal(10,2) DEFAULT NULL,
  `send_fee` decimal(10,2) DEFAULT NULL,
  `deliver_area_type` tinyint(3) DEFAULT '0',
  `deliver_type` tinyint(3) DEFAULT '0',
  `deliver_dada_shopno` varchar(100) DEFAULT NULL,
  `deliver_dada_citycode` varchar(20) DEFAULT NULL,
  `enoughmoney` decimal(10,2) DEFAULT '0.00',
  `enoughdeduct` decimal(10,2) DEFAULT '0.00',
  `createtime` int(10) DEFAULT NULL,
  `takeout_open_time` varchar(255) DEFAULT NULL,
  `payment` text,
  `telephone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_store','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD 
  `id` int(10) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_store','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `uniacid` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `name` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','province')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `province` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','city')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `city` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','district')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `district` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','district_edit_self')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `district_edit_self` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_store','address')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `address` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','longitude')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `longitude` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','latitude')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `latitude` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `status` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_store','is_getself')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `is_getself` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_store','is_takeout')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `is_takeout` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_store','close_reason')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `close_reason` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','starttime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `starttime` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','endtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `endtime` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','bg_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `bg_color` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','fg_color')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `fg_color` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','imgs')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `imgs` text");}
if(!pdo_fieldexists('deamx_food_store','operator')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `operator` text");}
if(!pdo_fieldexists('deamx_food_store','notice_tel')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `notice_tel` varchar(1000) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','remark_text')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `remark_text` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','auto_order')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `auto_order` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_store','wxacode')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `wxacode` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','start_price')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `start_price` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','send_limit')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `send_limit` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','send_fee')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `send_fee` decimal(10,2) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','deliver_area_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `deliver_area_type` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_store','deliver_type')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `deliver_type` tinyint(3) DEFAULT '0'");}
if(!pdo_fieldexists('deamx_food_store','deliver_dada_shopno')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `deliver_dada_shopno` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','deliver_dada_citycode')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `deliver_dada_citycode` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','enoughmoney')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `enoughmoney` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_store','enoughdeduct')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `enoughdeduct` decimal(10,2) DEFAULT '0.00'");}
if(!pdo_fieldexists('deamx_food_store','createtime')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `createtime` int(10) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','takeout_open_time')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `takeout_open_time` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store','payment')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `payment` text");}
if(!pdo_fieldexists('deamx_food_store','telephone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store')." ADD   `telephone` varchar(20) DEFAULT NULL");}
pdo_query("CREATE TABLE IF NOT EXISTS `ims_deamx_food_store_clerk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniacid` int(11) DEFAULT NULL,
  `store_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `realname` varchar(50) DEFAULT NULL,
  `telphone` varchar(20) DEFAULT NULL,
  `status` tinyint(3) DEFAULT '1',
  `remark` varchar(255) DEFAULT NULL,
  `permission` text,
  `lastvisit` int(11) DEFAULT NULL,
  `lastip` varchar(100) DEFAULT NULL,
  `system_uid` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

");

if(!pdo_fieldexists('deamx_food_store_clerk','id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD 
  `id` int(11) NOT NULL AUTO_INCREMENT");}
if(!pdo_fieldexists('deamx_food_store_clerk','uniacid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `uniacid` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','store_id')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `store_id` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','name')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `name` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','password')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `password` varchar(50) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','realname')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `realname` varchar(50) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','telphone')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `telphone` varchar(20) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','status')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `status` tinyint(3) DEFAULT '1'");}
if(!pdo_fieldexists('deamx_food_store_clerk','remark')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `remark` varchar(255) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','permission')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `permission` text");}
if(!pdo_fieldexists('deamx_food_store_clerk','lastvisit')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `lastvisit` int(11) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','lastip')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `lastip` varchar(100) DEFAULT NULL");}
if(!pdo_fieldexists('deamx_food_store_clerk','system_uid')) {pdo_query("ALTER TABLE ".tablename('deamx_food_store_clerk')." ADD   `system_uid` int(10) DEFAULT '0'");}
