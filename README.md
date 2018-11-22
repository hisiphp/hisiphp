HisiPHP
===============

HisiPHP 基于ThinkPHP5+layui开发的一套开源PHP快速开发框架，默认集成了权限管理、模块管理、插件管理、钩子管理、数据库管理等常用功能，以方便开发者快速构建自己的应用，我们在发布第一个版本就为您集成了一键升级框架的功能，扩展的模块、插件、模板均可独立在线升级。为了让您开发的应用获得更多的使用者，HisiPHP在发布之初就上线了PHP应用市场([http://store.hisiphp.com](http://store.hisiphp.com?source=github))，我们诚邀您的加入。

## 目录结构
```
www  WEB部署目录（或者子目录）
├─app                   应用目录
│   ├─admin             系统模块目录
│   ├─common            系统公共模块目录
│   │   ├─behavior      行为目录
│   │   ├─controller    公共模块控制器目录
│   │   ├─lang          公共模块语言包目录
│   │   ├─model         公共模型目录
│   │   ├─util          扩展类库目录
│   │   ├─validate      公共验证器目录
│   ├─extra             扩展配置目录
│   ├─index             前台默认模块目录（禁止在此目录下开发）
│   ├─install           系统安装目录
│   ├─command.php       命令行工具配置文件
│   ├─common.php        公共（函数）文件
│   ├─function.php      为方便系统升级，二次开发中用到的公共函数请写在此文件
│   ├─config.php        应用（公共）配置文件
│   ├─database.php      数据库配置文件（安装时自动生成）
│   ├─route.php         路由配置文件
│   ├─tags.php          应用行为扩展定义文件
├─backup                备份目录（数据库、升级文件）
├─plugins               插件目录
├─static                静态资源目录（后台用）
├─theme                 主题模板目录
├─thinkphp              ThinkPHP核心框架目录
├─upload                文件上传目录
├─vendor                第三方类库目录（Composer）
├─index.php             应用入口文件
├─admin.php             后台管理入口文件
├─plugins.php           插件入口文件
├─version.php           系统版本文件
├─.htaccess             伪静态配置文件
```

## 帮助手册
[HisiPHP 完全开发手册]https://www.kancloud.cn/hisi/hisiphp

## 应用商店
[store.hisiphp.com](http://store.hisiphp.com?source=github)

## 演示地址
[HisiPHP v1(thinkphp5)](http://v1.demo.hisiphp.com/admin.php?from=github)

[HisiPHP v2(thinkphp5.1)](http://v2.demo.hisiphp.com/admin.php?from=github)


## 模块推荐 
[在线支付（支付宝、微信扫码支付、微信公众号支付、微信H5支付、微信小程序支付、微信APP支付）](http://store.hisiphp.com/detail/1000000.html?from=github)

[响应式轻博客](http://store.hisiphp.com/detail/1000009.html?from=github)

[通用RESTful API（接口文档自动生成，输入参数自动检查，集成Oauth授权登录、在线测试工具等）](https://store.hisiphp.com/detail/1000014.html?from=github)


## 插件推荐 
[第三方授权登录（QQ登录、微信登录、新浪微博登录）](http://store.hisiphp.com/detail/1000002.html?from=github)

[短信服务（阿里云通信、腾讯云短信、聚合数据短信API）](http://store.hisiphp.com/detail/1000008.html?from=github)

## QQ交流群
[群①：50304283(2000人)](http://shang.qq.com/wpa/qunwpa?idkey=f70e4d4e0ad2ed6ad67a8b467475e695b286d536c7ff850db945542188871fc6)、[群②：640279557](http://shang.qq.com/wpa/qunwpa?idkey=7f77ff420f91ae529eef4045557d25553f3362f4c076d575a09974396597c88c)、[群③：679881764](http://shang.qq.com/wpa/qunwpa?idkey=a242a5d4d68dea7f073176be3fcc6ebd68e03bb6ed238827cbd2f00baae3f21f)、[群④：375815448](http://shang.qq.com/wpa/qunwpa?idkey=409636b5d168ddb78d13d9785a59a5c7ab6f5e0e65f3ee4059e36cd83ebacacd)


## 鸣谢
感谢[ThinkPHP](http://www.thinkphp.cn)、[JQuery](http://jquery.com)、[Layui](http://www.layui.com)等优秀开源项目。

## 版权信息
HisiPHP承诺基础框架永久免费开源，您可用于学习和商用，但必须保留软件版权信息。
本项目包含的第三方源码和二进制文件之版权信息另行标注。

版权所有Copyright © 2017 HisiPHP.COM (http://www.hisiphp.com)

All rights reserved。
