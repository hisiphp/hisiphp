HisiPHP
===============

HisiPHP V2版 基于ThinkPHP5.1 + layui开发的一套开源PHP快速开发框架，默认集成了权限管理、模块管理、插件管理、钩子管理、数据库管理等常用功能，以方便开发者快速构建自己的应用，我们在发布第一个版本就为您集成了一键升级框架的功能，扩展的模块、插件、模板均可独立在线升级。为了让您开发的应用获得更多的使用者，HisiPHP在发布之初就上线了PHP应用市场([http://store.hisiphp.com](http://store.hisiphp.com?source=oschina))，我们诚邀您的加入。

## 目录结构
```
www  WEB部署目录（不建议使用子目录）
├─application          应用目录
│  ├─system            系统基础模块（禁止修改）
│  ├─common            公共模块目录
│  │   ├─behavior      行为目录
│  │   ├─controller    公共模块控制器目录
│  │   ├─model         公共模型目录
│  │   ├─validate      公共验证器目录
│  │   ├─taglib        标签库目录
│  │   │   ├─Hisi.php  Hisi通用标签库
│  │   │   └─ ...      更多自定义标签库
│  │   └─ ...          更多类库目录
│  │
│  ├─index             前台默认模块
│  │  ├─home           前台控制器目录
│  ├─install           系统安装模块（安装成功后可删除）
│  ├─module_name       模块目录（请使用开发助手创建）
│  │  ├─admin          后台控制器目录
│  │  ├─home           前台控制器目录
│  │  ├─model          模型目录
│  │  ├─view           后台视图目录
│  │  ├─config         配置目录
│  │  ├─common.php     模块函数文件
│  │  └─ ...           更多类库目录
│  │
│  ├─command.php        命令行定义文件
│  ├─common.php         公共函数文件（禁止修改）
│  ├─function.php       **为方便系统升级，二次开发中用到的公共函数请写在此文件**
│  ├─install.lock       安装成功之后自动生成（禁止删除）
│  └─tags.php           应用行为扩展定义文件
│
├─backup                备份目录
│
├─config                应用配置目录
│  ├─module_name        模块配置目录
│  │  ├─database.php    数据库配置
│  │  ├─cache           缓存配置
│  │  └─ ...            
│  │
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─cookie.php         Cookie配置
│  ├─database.php       数据库配置
│  ├─hs_cloud.php       云平台配置（禁止修改）
│  ├─hs_system.php      HisiPHP基础配置（禁止修改）
│  ├─log.php            日志配置
│  ├─session.php        Session配置
│  ├─template.php       模板引擎配置
│  └─trace.php          Trace配置
│
├─route                 路由定义目录
│  ├─hisi.php           HisiPHP基础路由（禁止修改）
│  ├─route.php          路由定义
│  └─ ...                更多
│
├─public                WEB目录（对外访问目录）
│  ├─static             静态资源目录
│  │   ├─fonts          字体图标目录
│  │   ├─js             js资源目录
│  │   │   ├─editor     网页编辑器目录
│  │   │   ├─fileupload 文件上传
│  │   │   ├─layer      layer弹窗
│  │   │   ├─layui      layui
│  │   │   ├─jquery.2.1.4.min.js 	Jquery
│  │   │   ├─jquery.qrcode.min.js 	Jquery生成二维码插件
│  │   │   └─query.SuperSlide.2.1.1.js 	Jquery幻灯片插件
│  │   ├─plugins        插件静态资源目录
│  │   ├─system         后台静态资源目录
│  │   ├─module_name    扩展模块资源目录
│  │   └─ ......         更多
│  │
│  ├─theme              前台模板目录
│  │   ├─module_name    扩展模块资源目录
│  │   └─ ......         更多
│  │
│  ├─upload             资源上传目录
│  ├─index.php          默认入口文件
│  ├─admin.php          后台入口文件
│  ├─robots.txt         Robots协议
│  ├─router.php         快速测试文件
│  └─.htaccess          用于apache的重写
│
├─thinkphp              框架系统目录
│  ├─lang               语言文件目录
│  ├─library            框架类库目录
│  │  ├─think           Think类库包目录
│  │  └─traits          系统Trait目录
│  │
│  ├─tpl                系统模板目录
│  ├─base.php           基础定义文件
│  ├─convention.php     框架惯例配置文件
│  ├─helper.php         助手函数文件
│  └─logo.png           框架LOGO文件
│
├─extend                扩展类库目录
│  ├─hisi               HisiPHP提供的基础类库（禁止修改）
│  │  ├─Cloud.php       云平台类
│  │  ├─Database.php    数据库操作类
│  │  ├─Dir.php         文件或文件夹操作类
│  │  ├─Download.php    文件下载类
│  │  ├─Http.php        Http请求类
│  │  ├─PclZip.php      压缩包操作类
│  │  └─Xml.php         xml操作类
│  │
│  └─ ......            更多
│
├─plugins               插件目录
│  ├─hisiphp            HisiPHP系统基础信息插件
│  ├─plugins_name       扩展插件目录
│  └─ ......            更多
│
├─runtime               应用的运行时目录（可写，可定制）
├─vendor                第三方类库目录（Composer依赖库）
├─.env                  环境变量配置
├─composer.json         composer 定义文件
├─LICENSE.txt           授权说明文件
├─README.md             README 文件
├─think                 命令行入口文件
├─version.php           HisiPHP框架版本信息
```

## 帮助手册
[HisiPHP 完全开发手册]https://www.kancloud.cn/hisi/hisiphp_v2

## 应用商店
[store.hisiphp.com](http://store.hisiphp.com?source=oschina)

## 演示地址
[v2.demo.hisiphp.com](http://v2.demo.hisiphp.com/admin.php?from=oschina)

## 应用推荐 

[CMS内容管理系统](https://store.hisiphp.com/detail/1000025.html?from=oschina)

[在线支付（支付宝、微信扫码支付、微信公众号支付、微信H5支付、微信小程序支付、微信APP支付）](https://store.hisiphp.com/detail/1000019.html?from=oschina)

[通用RESTfulAPI接口](https://store.hisiphp.com/detail/1000022.html?from=oschina)

[响应式轻博客](https://store.hisiphp.com/detail/1000021.html?from=oschina)

[第三方登录](https://store.hisiphp.com/detail/1000024.html?from=oschina)



## QQ交流群
[群①：50304283(2000人)](http://shang.qq.com/wpa/qunwpa?idkey=f70e4d4e0ad2ed6ad67a8b467475e695b286d536c7ff850db945542188871fc6)、[群②：640279557](http://shang.qq.com/wpa/qunwpa?idkey=7f77ff420f91ae529eef4045557d25553f3362f4c076d575a09974396597c88c)、[群③：679881764](http://shang.qq.com/wpa/qunwpa?idkey=a242a5d4d68dea7f073176be3fcc6ebd68e03bb6ed238827cbd2f00baae3f21f)、[群④：375815448](http://shang.qq.com/wpa/qunwpa?idkey=409636b5d168ddb78d13d9785a59a5c7ab6f5e0e65f3ee4059e36cd83ebacacd)


## 鸣谢
感谢[ThinkPHP](http://www.thinkphp.cn)、[JQuery](http://jquery.com)、[Layui](http://www.layui.com)等优秀开源项目。

## 版权信息
HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017-2021 HisiPHP.COM (http://www.hisiphp.com)

All rights reserved。

