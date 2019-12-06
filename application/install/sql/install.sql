
# Dump of table hisiphp_system_annex
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_annex`;

CREATE TABLE `hisiphp_system_annex` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的数据ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '类型',
  `group` varchar(100) NOT NULL DEFAULT 'sys' COMMENT '文件分组',
  `file` varchar(255) NOT NULL COMMENT '上传文件',
  `hash` varchar(64) NOT NULL COMMENT '文件hash值',
  `size` decimal(12,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '附件大小KB',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '使用状态(0未使用，1已使用)',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 上传附件';



# Dump of table hisiphp_system_annex_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_annex_group`;

CREATE TABLE `hisiphp_system_annex_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '附件分组',
  `count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '附件数量',
  `size` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT '附件大小kb',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 附件分组';



# Dump of table hisiphp_system_config
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_config`;

CREATE TABLE `hisiphp_system_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统配置(1是，0否)',
  `group` varchar(20) NOT NULL DEFAULT 'base' COMMENT '分组',
  `title` varchar(20) NOT NULL COMMENT '配置标题',
  `name` varchar(50) NOT NULL COMMENT '配置名称，由英文字母和下划线组成',
  `value` text NOT NULL COMMENT '配置值',
  `type` varchar(20) NOT NULL DEFAULT 'input' COMMENT '配置类型()',
  `options` text NOT NULL COMMENT '配置项(选项名:选项值)',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '文件上传接口',
  `tips` varchar(255) NOT NULL COMMENT '配置提示',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COMMENT='[系统] 系统配置';


INSERT INTO `hisiphp_system_config` (`id`, `system`, `group`, `title`, `name`, `value`, `type`, `options`, `url`, `tips`, `sort`, `status`, `ctime`, `mtime`)
VALUES
  (1,1,'sys','扩展配置分组','config_group','','array',' ','','请按如下格式填写：&lt;br&gt;键值:键名&lt;br&gt;键值:键名&lt;br&gt;&lt;span style=&quot;color:#f00&quot;&gt;键值只能为英文、数字、下划线&lt;/span&gt;',2,1,1492140215,1492140215),
  (13,1,'base','网站域名','site_domain','','input','','','',2,1,1492140215,1492140215),
  (14,1,'upload','图片上传大小限制','upload_image_size','0','input','','','单位：KB，0表示不限制大小',3,1,1490841797,1491040778),
  (15,1,'upload','允许上传图片格式','upload_image_ext','jpg,png,gif,jpeg,ico','input','','','多个格式请用英文逗号（,）隔开',4,1,1490842130,1491040778),
  (16,1,'upload','缩略图裁剪方式','thumb_type','2','select','1:等比例缩放\r\n2:缩放后填充\r\n3:居中裁剪\r\n4:左上角裁剪\r\n5:右下角裁剪\r\n6:固定尺寸缩放\r\n','','',5,1,1490842450,1491040778),
  (17,1,'upload','图片水印开关','image_watermark','1','switch','0:关闭\r\n1:开启','','',6,1,1490842583,1491040778),
  (18,1,'upload','图片水印图','image_watermark_pic','','image','','','',7,1,1490842679,1491040778),
  (19,1,'upload','图片水印透明度','image_watermark_opacity','50','input','','','可设置值为0~100，数字越小，透明度越高',8,1,1490857704,1491040778),
  (20,1,'upload','图片水印图位置','image_watermark_location','9','select','7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中','','',9,1,1490858228,1491040778),
  (21,1,'upload','文件上传大小限制','upload_file_size','0','input','','','单位：KB，0表示不限制大小',1,1,1490859167,1491040778),
  (22,1,'upload','允许上传文件格式','upload_file_ext','doc,docx,xls,xlsx,ppt,pptx,pdf,wps,txt,rar,zip','input','','','多个格式请用英文逗号（,）隔开',2,1,1490859246,1491040778),
  (23,1,'upload','文字水印开关','text_watermark','0','switch','0:关闭\r\n1:开启','','',10,1,1490860872,1491040778),
  (24,1,'upload','文字水印内容','text_watermark_content','','input','','','',11,1,1490861005,1491040778),
  (25,1,'upload','文字水印字体','text_watermark_font','','file','','','不上传将使用系统默认字体',12,1,1490861117,1491040778),
  (26,1,'upload','文字水印字体大小','text_watermark_size','20','input','','','单位：px(像素)',13,1,1490861204,1491040778),
  (27,1,'upload','文字水印颜色','text_watermark_color','#000000','input','','','文字水印颜色，格式:#000000',14,1,1490861482,1491040778),
  (28,1,'upload','文字水印位置','text_watermark_location','7','select','7:左下角\r\n1:左上角\r\n4:左居中\r\n9:右下角\r\n3:右上角\r\n6:右居中\r\n2:上居中\r\n8:下居中\r\n5:居中','','',11,1,1490861718,1491040778),
  (29,1,'upload','缩略图尺寸','thumb_size','300x300;500x500','input','','','为空则不生成，生成 500x500 的缩略图，则填写 500x500，多个规格填写参考 300x300;500x500;800x800',4,1,1490947834,1491040778),
  (30,1,'sys','开发模式','app_debug','1','switch','0:关闭\r\n1:开启','','&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;',3,1,1491005004,1492093874),
  (31,1,'sys','页面Trace','app_trace','0','switch','0:关闭\r\n1:开启','','&lt;strong class=&quot;red&quot;&gt;生产环境下一定要关闭此配置&lt;/strong&gt;',4,1,1491005081,1492093874),
  (33,1,'sys','富文本编辑器','editor','umeditor','select','ueditor:UEditor\r\numeditor:UMEditor\r\nkindeditor:KindEditor\r\nckeditor:CKEditor','','',0,1,1491142648,1492140215),
  (35,1,'databases','备份目录','backup_path','./backup/database/','input','','','数据库备份路径,路径必须以 / 结尾',0,1,1491881854,1491965974),
  (36,1,'databases','备份分卷大小','part_size','20971520','input','','','用于限制压缩后的分卷最大长度。单位：B；建议设置20M',0,1,1491881975,1491965974),
  (37,1,'databases','备份压缩开关','compress','1','switch','0:关闭\r\n1:开启','','压缩备份文件需要PHP环境支持gzopen,gzwrite函数',0,1,1491882038,1491965974),
  (38,1,'databases','备份压缩级别','compress_level','4','radio','1:最低\r\n4:一般\r\n9:最高','','数据库备份文件的压缩级别，该配置在开启压缩时生效',0,1,1491882154,1491965974),
  (39,1,'base','网站状态','site_status','1','switch','0:关闭\r\n1:开启','','站点关闭后将不能访问，后台可正常登录',1,1,1492049460,1494690024),
  (40,1,'sys','后台管理路径','admin_path','admin.php','input','','','必须以.php为后缀',1,1,1492139196,1492140215),
  (41,1,'base','网站标题','site_title','HisiPHP 开源后台管理框架','input','','','网站标题是体现一个网站的主旨，要做到主题突出、标题简洁、连贯等特点，建议不超过28个字',6,1,1492502354,1494695131),
  (42,1,'base','网站关键词','site_keywords','hisiphp,hisiphp框架,php开源框架','input','','','网页内容所包含的核心搜索关键词，多个关键字请用英文逗号&quot;,&quot;分隔',7,1,1494690508,1494690780),
  (43,1,'base','网站描述','site_description','','textarea','','','网页的描述信息，搜索引擎采纳后，作为搜索结果中的页面摘要显示，建议不超过80个字',8,1,1494690669,1494691075),
  (44,1,'base','ICP备案信息','site_icp','','input','','','请填写ICP备案号，用于展示在网站底部，ICP备案官网：&lt;a href=&quot;http://www.miibeian.gov.cn&quot; target=&quot;_blank&quot;&gt;http://www.miibeian.gov.cn&lt;/a&gt;',9,1,1494691721,1494692046),
  (45,1,'base','站点统计代码','site_statis','','textarea','','','第三方流量统计代码，前台调用时请先用 htmlspecialchars_decode函数转义输出',10,1,1494691959,1494694797),
  (46,1,'base','网站名称','site_name','HisiPHP','input','','','将显示在浏览器窗口标题等位置',3,1,1494692103,1494694680),
  (47,1,'base','网站LOGO','site_logo','','image','','','网站LOGO图片',4,1,1494692345,1494693235),
  (48,1,'base','网站图标','site_favicon','','image','','/system/annex/favicon','又叫网站收藏夹图标，它显示位于浏览器的地址栏或者标题前面，&lt;strong class=&quot;red&quot;&gt;.ico格式&lt;/strong&gt;，&lt;a href=&quot;https://www.baidu.com/s?ie=UTF-8&amp;wd=favicon&quot; target=&quot;_blank&quot;&gt;点此了解网站图标&lt;/a&gt;',5,1,1494692781,1494693966),
  (49,1,'base','手机网站','wap_site_status','1','switch','0:关闭\r\n1:开启','','如果有手机网站，请设置为开启状态，否则只显示PC网站',2,1,1498405436,1498405436),
  (50,1,'sys','云端推送','cloud_push','0','switch','0:关闭\r\n1:开启','','关闭之后，无法通过云端推送安装扩展',5,1,1504250320,1504250320),
  (51,1,'base','手机网站域名','wap_domain','','input','','','手机访问将自动跳转至此域名，示例：http://m.domain.com',2,1,1504304776,1504304837),
  (52,1,'sys','多语言支持','multi_language','0','switch','0:关闭\r\n1:开启','','开启后你可以自由上传多种语言包',6,1,1506532211,1506532211),
  (53,1,'sys','后台白名单验证','admin_whitelist_verify','0','switch','0:禁用\r\n1:启用','','禁用后不存在的菜单节点将不在提示',7,1,1542012232,1542012321),
  (54,1,'sys','系统日志保留','system_log_retention','30','input','','','单位天，系统将自动清除 ? 天前的系统日志',8,1,1542013958,1542014158),
  (55,1, 'upload', '上传驱动', 'upload_driver', 'local', 'select', 'local:本地上传', '', '资源上传驱动设置', 0, 1, 1558599270, 1558618703);


# Dump of table hisiphp_system_hook
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_hook`;

CREATE TABLE `hisiphp_system_hook` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统插件',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子名称',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '钩子来源[plugins.插件名，module.模块名]',
  `intro` varchar(200) NOT NULL DEFAULT '' COMMENT '钩子简介',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子表';


INSERT INTO `hisiphp_system_hook` (`id`, `system`, `name`, `source`, `intro`, `status`, `ctime`, `mtime`)
VALUES
  (1,1,'system_admin_index','','后台首页',1,1490885108,1490885108),
  (2,1,'system_admin_tips','','后台所有页面提示',1,1490713165,1490885137),
  (3,1,'system_annex_upload','','附件上传钩子，可扩展上传到第三方存储',1,1490884242,1490885121),
  (4,1,'system_member_login','','会员登陆成功之后的动作',1,1490885108,1490885108),
  (5,1,'system_member_register','','会员注册成功后的动作',1,1512610518,1512610518);


# Dump of table hisiphp_system_hook_plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_hook_plugins`;

CREATE TABLE `hisiphp_system_hook_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hook` varchar(32) NOT NULL COMMENT '钩子id',
  `plugins` varchar(32) NOT NULL COMMENT '插件标识',
  `ctime` int(11) unsigned NOT NULL DEFAULT '0',
  `mtime` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 钩子-插件对应表';


INSERT INTO `hisiphp_system_hook_plugins` (`id`, `hook`, `plugins`, `ctime`, `mtime`, `sort`, `status`)
VALUES
  (1,'system_admin_index','hisiphp',1509380301,1509380301,0,1);



# Dump of table hisiphp_system_language
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_language`;

CREATE TABLE `hisiphp_system_language` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '语言包名称',
  `code` varchar(20) NOT NULL DEFAULT '' COMMENT '编码',
  `locale` varchar(255) NOT NULL DEFAULT '' COMMENT '本地浏览器语言编码',
  `icon` varchar(30) NOT NULL DEFAULT '' COMMENT '图标',
  `pack` varchar(100) NOT NULL DEFAULT '' COMMENT '上传的语言包',
  `sort` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='[系统] 语言包';

INSERT INTO `hisiphp_system_language` (`id`, `name`, `code`, `locale`, `icon`, `pack`, `sort`, `status`)
VALUES
  (1,'简体中文','zh-cn','zh-CN,zh-CN.UTF-8,zh-cn','','1',1,1);


# Dump of table hisiphp_system_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_log`;

CREATE TABLE `hisiphp_system_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `url` varchar(200) DEFAULT '',
  `param` text,
  `remark` varchar(255) DEFAULT '',
  `count` int(10) unsigned NOT NULL DEFAULT '1',
  `ip` varchar(128) DEFAULT '',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 操作日志';

# Dump of table hisiphp_system_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_menu`;

CREATE TABLE `hisiphp_system_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID(快捷菜单专用)',
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `module` varchar(20) NOT NULL COMMENT '模块名或插件名，插件名格式:plugins.插件名',
  `title` varchar(20) NOT NULL COMMENT '菜单标题',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-shezhi' COMMENT '菜单图标',
  `url` varchar(200) NOT NULL COMMENT '链接地址(模块/控制器/方法)',
  `param` varchar(200) NOT NULL DEFAULT '' COMMENT '扩展参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '打开方式(_blank,_self)',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `debug` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '开发模式可见',
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否为系统菜单，系统菜单不可删除',
  `nav` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否为菜单显示，1显示0不显示',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 管理菜单';


INSERT INTO `hisiphp_system_menu` (`id`, `uid`, `pid`, `module`, `title`, `icon`, `url`, `param`, `target`, `sort`, `debug`, `system`, `nav`, `status`, `ctime`)
VALUES
  (1, 0, 0, 'system', '首页', '', 'system/index', '', '_self', 0, 0, 1, 1, 1, 1490315067),
  (2, 0, 0, 'system', '系统', '', 'system/system', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (3, 0, 0, 'system', '插件', 'aicon ai-shezhi', 'system/plugins', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (4, 0, 1, 'system', '快捷菜单', 'aicon ai-caidan', 'system/quick', '', '_self', 0, 0, 1, 1, 1, 1490315067),
  (5, 0, 3, 'system', '插件列表', 'aicon ai-mokuaiguanli', 'system/plugins', '', '_self', 0, 0, 1, 1, 1, 1490315067),
  (6, 0, 2, 'system', '系统基础', 'aicon ai-gongneng', 'system/system', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (7,0,17,'system','导入主题SQL','','system/module/exeSql','','_self',10,0,1,0,1,1490315067),
  (8, 0, 2, 'system', '系统扩展', 'aicon ai-shezhi', 'system/extend', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (9, 0, 4, 'system', '预留占位', '', '', '', '_self', 4, 0, 1, 1, 0, 1490315067),
  (10, 0, 6, 'system', '系统设置', 'aicon ai-icon01', 'system/system/index', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (11, 0, 6, 'system', '配置管理', 'aicon ai-peizhiguanli', 'system/config/index', '', '_self', 2, 1, 1, 1, 1, 1490315067),
  (12, 0, 6, 'system', '系统菜单', 'aicon ai-systemmenu', 'system/menu/index', '', '_self', 3, 1, 1, 1, 1, 1490315067),
  (13, 0, 6, 'system', '管理员角色', '', 'system/user/role', '', '_self', 4, 0, 1, 0, 1, 1490315067),
  (14, 0, 6, 'system', '系统管理员', 'aicon ai-tubiao05', 'system/user/index', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (15, 0, 6, 'system', '系统日志', 'aicon ai-xitongrizhi-tiaoshi', 'system/log/index', '', '_self', 7, 0, 1, 1, 1, 1490315067),
  (16, 0, 6, 'system', '附件管理', '', 'system/annex/index', '', '_self', 8, 0, 1, 0, 1, 1490315067),
  (17, 0, 8, 'system', '本地模块', 'aicon ai-mokuaiguanli1', 'system/module/index', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (18, 0, 8, 'system', '本地插件', 'aicon ai-chajianguanli', 'system/plugins/index', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (19, 0, 8, 'system', '插件钩子', 'aicon ai-icon-test', 'system/hook/index', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (20, 0, 4, 'system', '预留占位', '', '', '', '_self', 1, 0, 1, 1, 0, 1490315067),
  (21, 0, 4, 'system', '预留占位', '', '', '', '_self', 2, 0, 1, 1, 0, 1490315067),
  (22, 0, 4, 'system', '预留占位', '', '', '', '_self', 1, 0, 1, 1, 0, 1490315067),
  (23, 0, 4, 'system', '预留占位', '', '', '', '_self', 2, 0, 1, 1, 0, 1490315067),
  (24, 0, 4, 'system', '后台首页', '', 'system/index/index', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (25, 0, 4, 'system', '清空缓存', '', 'system/index/clear', '', '_self', 2, 0, 1, 0, 1, 1490315067),
  (26, 0, 12, 'system', '添加菜单', '', 'system/menu/add', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (27, 0, 12, 'system', '修改菜单', '', 'system/menu/edit', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (28, 0, 12, 'system', '删除菜单', '', 'system/menu/del', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (29, 0, 12, 'system', '状态设置', '', 'system/menu/status', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (30, 0, 12, 'system', '排序设置', '', 'system/menu/sort', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (31, 0, 12, 'system', '添加快捷菜单', '', 'system/menu/quick', '', '_self', 6, 0, 1, 1, 1, 1490315067),
  (32, 0, 12, 'system', '导出菜单', '', 'system/menu/export', '', '_self', 7, 0, 1, 1, 1, 1490315067),
  (33, 0, 13, 'system', '添加角色', '', 'system/user/addrole', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (34, 0, 13, 'system', '修改角色', '', 'system/user/editrole', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (35, 0, 13, 'system', '删除角色', '', 'system/user/delrole', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (36, 0, 13, 'system', '状态设置', '', 'system/user/statusRole', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (37, 0, 14, 'system', '添加管理员', '', 'system/user/adduser', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (38, 0, 14, 'system', '修改管理员', '', 'system/user/edituser', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (39, 0, 14, 'system', '删除管理员', '', 'system/user/deluser', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (40, 0, 14, 'system', '状态设置', '', 'system/user/status', '', '_self', 4, 0, 1, 0, 1, 1490315067),
  (41, 0, 4, 'system', '个人信息设置', '', 'system/user/info', '', '_self', 5, 0, 1, 0, 1, 1490315067),
  (42, 0, 18, 'system', '安装插件', '', 'system/plugins/install', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (43, 0, 18, 'system', '卸载插件', '', 'system/plugins/uninstall', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (44, 0, 18, 'system', '删除插件', '', 'system/plugins/del', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (45, 0, 18, 'system', '状态设置', '', 'system/plugins/status', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (46, 0, 18, 'system', '生成插件', '', 'system/plugins/design', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (47, 0, 18, 'system', '运行插件', '', 'system/plugins/run', '', '_self', 6, 0, 1, 1, 1, 1490315067),
  (48, 0, 18, 'system', '更新插件', '', 'system/plugins/update', '', '_self', 7, 0, 1, 1, 1, 1490315067),
  (49, 0, 18, 'system', '插件配置', '', 'system/plugins/setting', '', '_self', 8, 0, 1, 1, 1, 1490315067),
  (50, 0, 19, 'system', '添加钩子', '', 'system/hook/add', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (51, 0, 19, 'system', '修改钩子', '', 'system/hook/edit', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (52, 0, 19, 'system', '删除钩子', '', 'system/hook/del', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (53, 0, 19, 'system', '状态设置', '', 'system/hook/status', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (54, 0, 19, 'system', '插件排序', '', 'system/hook/sort', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (55, 0, 11, 'system', '添加配置', '', 'system/config/add', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (56, 0, 11, 'system', '修改配置', '', 'system/config/edit', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (57, 0, 11, 'system', '删除配置', '', 'system/config/del', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (58, 0, 11, 'system', '状态设置', '', 'system/config/status', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (59, 0, 11, 'system', '排序设置', '', 'system/config/sort', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (60, 0, 10, 'system', '基础配置', '', 'system/system/index', 'group=base', '_self', 1, 0, 1, 1, 1, 1490315067),
  (61, 0, 10, 'system', '系统配置', '', 'system/system/index', 'group=sys', '_self', 2, 0, 1, 1, 1, 1490315067),
  (62, 0, 10, 'system', '上传配置', '', 'system/system/index', 'group=upload', '_self', 3, 0, 1, 1, 1, 1490315067),
  (63, 0, 10, 'system', '开发配置', '', 'system/system/index', 'group=develop', '_self', 4, 0, 1, 1, 1, 1490315067),
  (64, 0, 17, 'system', '生成模块', '', 'system/module/design', '', '_self', 6, 1, 1, 1, 1, 1490315067),
  (65, 0, 17, 'system', '安装模块', '', 'system/module/install', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (66, 0, 17, 'system', '卸载模块', '', 'system/module/uninstall', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (67, 0, 17, 'system', '状态设置', '', 'system/module/status', '', '_self', 3, 0, 1, 1, 1, 1490315067),
  (68, 0, 17, 'system', '设置默认模块', '', 'system/module/setdefault', '', '_self', 4, 0, 1, 1, 1, 1490315067),
  (69, 0, 17, 'system', '删除模块', '', 'system/module/del', '', '_self', 5, 0, 1, 1, 1, 1490315067),
  (70, 0, 4, 'system', '预留占位', '', '', '', '_self', 1, 0, 1, 1, 0, 1490315067),
  (71, 0, 4, 'system', '预留占位', '', '', '', '_self', 2, 0, 1, 1, 0, 1490315067),
  (72, 0, 4, 'system', '预留占位', '', '', '', '_self', 3, 0, 1, 1, 0, 1490315067),
  (73, 0, 4, 'system', '预留占位', '', '', '', '_self', 4, 0, 1, 1, 0, 1490315067),
  (74, 0, 4, 'system', '预留占位', '', '', '', '_self', 5, 0, 1, 1, 0, 1490315067),
  (75, 0, 4, 'system', '预留占位', '', '', '', '_self', 0, 0, 1, 1, 0, 1490315067),
  (76, 0, 4, 'system', '预留占位', '', '', '', '_self', 0, 0, 1, 1, 0, 1490315067),
  (77, 0, 4, 'system', '预留占位', '', '', '', '_self', 0, 0, 1, 1, 0, 1490315067),
  (78, 0, 16, 'system', '附件上传', '', 'system/annex/upload', '', '_self', 1, 0, 1, 1, 1, 1490315067),
  (79, 0, 16, 'system', '删除附件', '', 'system/annex/del', '', '_self', 2, 0, 1, 1, 1, 1490315067),
  (80, 0, 8, 'system', '框架升级', 'aicon ai-iconfontshengji', 'system/upgrade/index', '', '_self', 4, 0, 1, 1, 1, 1491352728),
  (81, 0, 80, 'system', '获取升级列表', '', 'system/upgrade/lists', '', '_self', 0, 0, 1, 1, 1, 1491353504),
  (82, 0, 80, 'system', '安装升级包', '', 'system/upgrade/install', '', '_self', 0, 0, 1, 1, 1, 1491353568),
  (83, 0, 80, 'system', '下载升级包', '', 'system/upgrade/download', '', '_self', 0, 0, 1, 1, 1, 1491395830),
  (84, 0, 6, 'system', '数据库管理', 'aicon ai-shujukuguanli', 'system/database/index', '', '_self', 6, 0, 1, 1, 1, 1491461136),
  (85, 0, 84, 'system', '备份数据库', '', 'system/database/export', '', '_self', 0, 0, 1, 1, 1, 1491461250),
  (86, 0, 84, 'system', '恢复数据库', '', 'system/database/import', '', '_self', 0, 0, 1, 1, 1, 1491461315),
  (87, 0, 84, 'system', '优化数据库', '', 'system/database/optimize', '', '_self', 0, 0, 1, 1, 1, 1491467000),
  (88, 0, 84, 'system', '删除备份', '', 'system/database/del', '', '_self', 0, 0, 1, 1, 1, 1491467058),
  (89, 0, 84, 'system', '修复数据库', '', 'system/database/repair', '', '_self', 0, 0, 1, 1, 1, 1491880879),
  (90, 0, 21, 'system', '设置默认等级', '', 'system/member/setdefault', '', '_self', 0, 0, 1, 1, 1, 1491966585),
  (91, 0, 10, 'system', '数据库配置', '', 'system/system/index', 'group=databases', '_self', 5, 0, 1, 0, 1, 1492072213),
  (92, 0, 17, 'system', '模块打包', '', 'system/module/package', '', '_self', 7, 0, 1, 1, 1, 1492134693),
  (93, 0, 18, 'system', '插件打包', '', 'system/plugins/package', '', '_self', 0, 0, 1, 1, 1, 1492134743),
  (94, 0, 17, 'system', '主题管理', '', 'system/module/theme', '', '_self', 8, 0, 1, 1, 1, 1492433470),
  (95, 0, 17, 'system', '设置默认主题', '', 'system/module/setdefaulttheme', '', '_self', 9, 0, 1, 1, 1, 1492433618),
  (96, 0, 17, 'system', '删除主题', '', 'system/module/deltheme', '', '_self', 10, 0, 1, 1, 1, 1490315067),
  (97, 0, 6, 'system', '语言包管理', '', 'system/language/index', '', '_self', 9, 0, 1, 0, 1, 1490315067),
  (98, 0, 97, 'system', '添加语言包', '', 'system/language/add', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (99, 0, 97, 'system', '修改语言包', '', 'system/language/edit', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (100, 0, 97, 'system', '删除语言包', '', 'system/language/del', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (101, 0, 97, 'system', '排序设置', '', 'system/language/sort', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (102, 0, 97, 'system', '状态设置', '', 'system/language/status', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (103, 0, 16, 'system', '收藏夹图标上传', '', 'system/annex/favicon', '', '_self', 3, 0, 1, 0, 1, 1490315067),
  (104, 0, 17, 'system', '导入模块', '', 'system/module/import', '', '_self', 11, 0, 1, 0, 1, 1490315067),
  (105, 0, 4, 'system', '后台首页', '', 'system/index/welcome', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (106, 0, 4, 'system', '布局切换', '', 'system/user/iframe', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (107, 0, 15, 'system', '删除日志', '', 'system/log/del', 'table=admin_log', '_self', 100, 0, 1, 0, 1, 1490315067),
  (108, 0, 15, 'system', '清空日志', '', 'system/log/clear', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (109, 0, 17, 'system', '编辑模块', '', 'system/module/edit', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (110, 0, 17, 'system', '模块图标上传', '', 'system/module/icon', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (111, 0, 18, 'system', '导入插件', '', 'system/plugins/import', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (112, 0, 4, 'system', '钩子插件状态', '', 'system/hook/hookPluginsStatus', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (113, 0, 4, 'system', '设置主题', '', 'system/user/setTheme', '', '_self', 100, 0, 1, 0, 1, 1490315067),
  (114, 0, 8, 'system', '应用市场', 'aicon ai-app-store', 'system/store/index', '', '_self', 0, 0, 1, 1, 1, 1490315067),
  (115, 0, 114, 'system', '安装应用', '', 'system/store/install', '', '_self', 0, 0, 1, 1, 1, 1490315067),
  (116, 0, 21, 'system', '重置密码', '', 'system/member/resetPwd', '', '_self', 6, 0, 1, 1, 1, 1490315067),
  (117, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (118, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (119, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (120, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (121, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (122, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (123, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (124, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (125, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (126, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (127, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (128, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (129, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (130, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (131, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (132, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (133, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (134, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (135, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (136, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (137, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (138, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (139, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067),
  (140, 0, 4, 'system', '预留占位', '', '', '', '_self', 100, 0, 1, 1, 0, 1490315067);


# Dump of table hisiphp_system_menu_lang
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_menu_lang`;

CREATE TABLE `hisiphp_system_menu_lang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) unsigned NOT NULL DEFAULT '0',
  `title` varchar(120) NOT NULL DEFAULT '' COMMENT '标题',
  `lang` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '语言包',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=271 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理菜单语言包';

INSERT INTO `hisiphp_system_menu_lang` (`id`, `menu_id`, `title`, `lang`)
VALUES
  (131, 1, '首页', 1),
  (132, 2, '系统', 1),
  (133, 3, '插件', 1),
  (134, 4, '快捷菜单', 1),
  (135, 5, '插件列表', 1),
  (136, 6, '系统基础', 1),
  (137, 7, '预留占位', 1),
  (138, 8, '系统扩展', 1),
  (139, 9, '开发专用', 1),
  (140, 10, '系统设置', 1),
  (141, 11, '配置管理', 1),
  (142, 12, '系统菜单', 1),
  (143, 13, '管理员角色', 1),
  (144, 14, '系统管理员', 1),
  (145, 15, '系统日志', 1),
  (146, 16, '附件管理', 1),
  (147, 17, '本地模块', 1),
  (148, 18, '本地插件', 1),
  (149, 19, '插件钩子', 1),
  (150, 20, '预留占位', 1),
  (151, 21, '预留占位', 1),
  (152, 22, '预留占位', 1),
  (153, 23, '预留占位', 1),
  (154, 24, '后台首页', 1),
  (155, 25, '清空缓存', 1),
  (156, 26, '添加菜单', 1),
  (157, 27, '修改菜单', 1),
  (158, 28, '删除菜单', 1),
  (159, 29, '状态设置', 1),
  (160, 30, '排序设置', 1),
  (161, 31, '添加快捷菜单', 1),
  (162, 32, '导出菜单', 1),
  (163, 33, '添加角色', 1),
  (164, 34, '修改角色', 1),
  (165, 35, '删除角色', 1),
  (166, 36, '状态设置', 1),
  (167, 37, '添加管理员', 1),
  (168, 38, '修改管理员', 1),
  (169, 39, '删除管理员', 1),
  (170, 40, '状态设置', 1),
  (171, 41, '个人信息设置', 1),
  (172, 42, '安装插件', 1),
  (173, 43, '卸载插件', 1),
  (174, 44, '删除插件', 1),
  (175, 45, '状态设置', 1),
  (176, 46, '生成插件', 1),
  (177, 47, '运行插件', 1),
  (178, 48, '更新插件', 1),
  (179, 49, '插件配置', 1),
  (180, 50, '添加钩子', 1),
  (181, 51, '修改钩子', 1),
  (182, 52, '删除钩子', 1),
  (183, 53, '状态设置', 1),
  (184, 54, '插件排序', 1),
  (185, 55, '添加配置', 1),
  (186, 56, '修改配置', 1),
  (187, 57, '删除配置', 1),
  (188, 58, '状态设置', 1),
  (189, 59, '排序设置', 1),
  (190, 60, '基础配置', 1),
  (191, 61, '系统配置', 1),
  (192, 62, '上传配置', 1),
  (193, 63, '开发配置', 1),
  (194, 64, '生成模块', 1),
  (195, 65, '安装模块', 1),
  (196, 66, '卸载模块', 1),
  (197, 67, '状态设置', 1),
  (198, 68, '设置默认模块', 1),
  (199, 69, '删除模块', 1),
  (200, 70, '预留占位', 1),
  (201, 71, '预留占位', 1),
  (202, 72, '预留占位', 1),
  (203, 73, '预留占位', 1),
  (204, 74, '预留占位', 1),
  (205, 75, '预留占位', 1),
  (206, 76, '预留占位', 1),
  (207, 77, '预留占位', 1),
  (208, 78, '附件上传', 1),
  (209, 79, '删除附件', 1),
  (210, 80, '框架升级', 1),
  (211, 81, '获取升级列表', 1),
  (212, 82, '安装升级包', 1),
  (213, 83, '下载升级包', 1),
  (214, 84, '数据库管理', 1),
  (215, 85, '备份数据库', 1),
  (216, 86, '恢复数据库', 1),
  (217, 87, '优化数据库', 1),
  (218, 88, '删除备份', 1),
  (219, 89, '修复数据库', 1),
  (220, 90, '设置默认等级', 1),
  (221, 91, '数据库配置', 1),
  (222, 92, '模块打包', 1),
  (223, 93, '插件打包', 1),
  (224, 94, '主题管理', 1),
  (225, 95, '设置默认主题', 1),
  (226, 96, '删除主题', 1),
  (227, 97, '语言包管理', 1),
  (228, 98, '添加语言包', 1),
  (229, 99, '修改语言包', 1),
  (230, 100, '删除语言包', 1),
  (231, 101, '排序设置', 1),
  (232, 102, '状态设置', 1),
  (233, 103, '收藏夹图标上传', 1),
  (234, 104, '导入模块', 1),
  (235, 105, '后台首页', 1),
  (236, 106, '布局切换', 1),
  (237, 107, '删除日志', 1),
  (238, 108, '清空日志', 1),
  (239, 109, '编辑模块', 1),
  (240, 110, '模块图标上传', 1),
  (241, 111, '导入插件', 1),
  (242, 112, '钩子插件状态', 1),
  (243, 113, '设置主题', 1),
  (244, 114, '应用市场', 1),
  (245, 115, '安装应用', 1),
  (246, 116, '重置密码', 1),
  (247,117,'预留占位',1),
  (248,118,'预留占位',1),
  (249,119,'预留占位',1),
  (250,120,'预留占位',1),
  (251,121,'预留占位',1),
  (252,122,'预留占位',1),
  (253,123,'预留占位',1),
  (254,124,'预留占位',1),
  (255,125,'预留占位',1),
  (256,126,'预留占位',1),
  (257,127,'预留占位',1),
  (258,128,'预留占位',1),
  (259,129,'预留占位',1),
  (260,130,'预留占位',1),
  (261,131,'预留占位',1),
  (262,132,'预留占位',1),
  (263,133,'预留占位',1),
  (264,134,'预留占位',1),
  (265,135,'预留占位',1),
  (266,136,'预留占位',1),
  (267,137,'预留占位',1),
  (268,138,'预留占位',1),
  (269,139,'预留占位',1),
  (270,140,'预留占位',1);

# Dump of table hisiphp_system_module
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_module`;

CREATE TABLE `hisiphp_system_module` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '系统模块',
  `name` varchar(50) NOT NULL COMMENT '模块名(英文)',
  `identifier` varchar(100) NOT NULL COMMENT '模块标识(模块名(字母).开发者标识.module)',
  `title` varchar(50) NOT NULL COMMENT '模块标题',
  `intro` varchar(255) NOT NULL COMMENT '模块简介',
  `author` varchar(100) NOT NULL COMMENT '作者',
  `icon` varchar(80) NOT NULL DEFAULT 'aicon ai-mokuaiguanli' COMMENT '图标',
  `version` varchar(20) NOT NULL COMMENT '版本号',
  `url` varchar(255) NOT NULL COMMENT '链接',
  `sort` int(5) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0未安装，1未启用，2已启用',
  `default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '默认模块(只能有一个)',
  `config` text NOT NULL COMMENT '配置',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '应用市场ID(0本地)',
  `app_keys` varchar(200) DEFAULT '' COMMENT '应用秘钥',
  `theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT '主题模板',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='[系统] 模块';

INSERT INTO `hisiphp_system_module` (`id`, `system`, `name`, `identifier`, `title`, `intro`, `author`, `icon`, `version`, `url`, `sort`, `status`, `default`, `config`, `app_id`, `app_keys`, `theme`, `ctime`, `mtime`)
VALUES
  (1, 1, 'system', 'system.hisiphp.module', '系统管理模块', '系统核心模块，用于后台各项管理功能模块及功能拓展', 'HisiPHP官方出品', '', '1.0.0', 'http://www.hisiphp.com', 0, 2, 0, '', '0', '', 'default', 1489998096, 1489998096),
  (2, 1, 'index', 'index.hisiphp.module', '默认模块', '推荐使用扩展模块作为默认首页。', 'HisiPHP官方出品', '', '1.0.0', 'http://www.hisiphp.com', 0, 2, 0, '', '0', '', 'default', 1489998096, 1489998096),
  (3, 1, 'install', 'install.hisiphp.module', '系统安装模块', '系统安装模块，勿动。', 'HisiPHP官方出品', '', '1.0.0', 'http://www.hisiphp.com', 0, 2, 0, '', '0', '', 'default', 1489998096, 1489998096);


# Dump of table hisiphp_system_plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_plugins`;

CREATE TABLE `hisiphp_system_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL COMMENT '插件名称(英文)',
  `title` varchar(32) NOT NULL COMMENT '插件标题',
  `icon` varchar(64) NOT NULL COMMENT '图标',
  `intro` text NOT NULL COMMENT '插件简介',
  `author` varchar(32) NOT NULL COMMENT '作者',
  `url` varchar(255) NOT NULL COMMENT '作者主页',
  `version` varchar(16) NOT NULL DEFAULT '' COMMENT '版本号',
  `identifier` varchar(64) NOT NULL DEFAULT '' COMMENT '插件唯一标识符',
  `config` text NOT NULL COMMENT '插件配置',
  `app_id` varchar(30) NOT NULL DEFAULT '0' COMMENT '来源(0本地)',
  `app_keys` varchar(200) DEFAULT '' COMMENT '应用秘钥',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='[系统] 插件表';

INSERT INTO `hisiphp_system_plugins` (`id`, `system`, `name`, `title`, `icon`, `intro`, `author`, `url`, `version`, `identifier`, `config`, `app_id`, `app_keys`, `ctime`, `mtime`, `sort`, `status`)
VALUES
  (1, 1, 'hisiphp', '系统基础信息', '/static/plugins/hisiphp/hisiphp.png', '后台首页展示系统基础信息和开发团队信息', 'HisiPHP', 'http://www.hisiphp.com', '1.0.0', 'hisiphp.hisiphp.plugins', '', '0', '', 1509379331, 1509379331, 0, 2);

# Dump of table hisiphp_system_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_role`;

CREATE TABLE `hisiphp_system_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `intro` varchar(200) NOT NULL COMMENT '角色简介',
  `auth` text NOT NULL COMMENT '角色权限',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理角色';

INSERT INTO `hisiphp_system_role` (`id`, `name`, `intro`, `auth`, `ctime`, `mtime`, `status`)
VALUES
  (1,'超级管理员','拥有系统最高权限','0',1489411760,0,1),
  (2,'系统管理员','拥有系统管理员权限','[\"1\",\"4\",\"25\",\"24\",\"2\",\"6\",\"10\",\"60\",\"61\",\"62\",\"63\",\"91\",\"11\",\"55\",\"56\",\"57\",\"58\",\"59\",\"12\",\"26\",\"27\",\"28\",\"29\",\"30\",\"31\",\"32\",\"13\",\"33\",\"34\",\"35\",\"36\",\"14\",\"37\",\"38\",\"39\",\"40\",\"41\",\"16\",\"78\",\"79\",\"84\",\"85\",\"86\",\"87\",\"88\",\"89\",\"7\",\"20\",\"75\",\"76\",\"77\",\"21\",\"90\",\"70\",\"71\",\"72\",\"73\",\"74\",\"8\",\"17\",\"65\",\"66\",\"67\",\"68\",\"94\",\"95\",\"18\",\"42\",\"43\",\"45\",\"47\",\"48\",\"49\",\"19\",\"80\",\"81\",\"82\",\"83\",\"9\",\"22\",\"23\",\"3\",\"5\"]',1489411760,1507731116,1),
  (3,'普通管理员','普通管理员','{\"0\":\"1\",\"1\":\"4\",\"2\":\"25\",\"4\":\"24\",\"6\":\"106\",\"8\":\"113\"}',1507737902,1542075415,1);


# Dump of table hisiphp_system_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_user`;

CREATE TABLE `hisiphp_system_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(64) NOT NULL,
  `nick` varchar(50) NOT NULL COMMENT '昵称',
  `mobile` varchar(11) NOT NULL,
  `email` varchar(50) NOT NULL COMMENT '邮箱',
  `auth` text NOT NULL COMMENT '权限',
  `iframe` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0默认，1框架',
  `theme` varchar(50) NOT NULL DEFAULT 'default' COMMENT '主题',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态',
  `last_login_ip` varchar(128) NOT NULL COMMENT '最后登陆IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登陆时间',
  `ctime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `mtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='[系统] 管理用户';


# Dump of table hisiphp_system_user_role
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_system_user_role`;

CREATE TABLE `hisiphp_system_user_role` (
  `user_id` int(11) unsigned NOT NULL,
  `role_id` int(10) unsigned DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员角色索引';

# Dump of table hisiphp_jobs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_jobs`;

CREATE TABLE `hisiphp_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Dump of table hisiphp_token
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hisiphp_token`;

CREATE TABLE IF NOT EXISTS `hisiphp_token` (
  `token` varchar(128) NOT NULL DEFAULT '' COMMENT 'Token',
  `tag` varchar(50) DEFAULT '' COMMENT '标签',
  `value` varchar(30) NOT NULL DEFAULT '' COMMENT '映射的值',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `expire_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '过期时间',
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='token表';