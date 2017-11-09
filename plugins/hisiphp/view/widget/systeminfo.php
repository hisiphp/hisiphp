<div class="fl" style="width:49%">
    <table class="layui-table" lay-skin="line">
        <colgroup>
            <col width="160">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">系统信息</th>
            </tr> 
        </thead>
        <tbody>
            <tr>
                <td>系统版本</td>
                <td>HisiPHP v{:config('hisiphp.version')}</td>
            </tr>
            <tr>
                <td>服务器环境</td>
                <td>{$Think.const.PHP_OS} / {$_SERVER["SERVER_SOFTWARE"]}</td>
            </tr>
            <tr>
                <td>PHP/MySql版本</td>
                <td>PHP {$Think.const.PHP_VERSION} / MySql {:db()->query('select version() as version')[0]['version']}</td>
            </tr>
            <tr>
                <td>ThinkPHP版本</td>
                <td>{$Think.VERSION}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="fr" style="width:49%">
    <table class="layui-table" lay-skin="line">
        <colgroup>
            <col width="160">
            <col>
        </colgroup>
        <thead>
            <tr>
                <th colspan="2">产品信息</th>
            </tr> 
        </thead>
        <tbody>
            <tr>
                <td>产品名称</td>
                <td>HisiPHP开发框架(简单.快速、高效.稳定)</td>
            </tr>
            <tr>
                <td>官方网站</td>
                <td><a href="http://www.hisiphp.com" target="_blank" rel="noreferrer">http://www.hisiphp.com</a></td>
            </tr>
            <tr>
                <td>官方QQ群</td>
                <td><a href="http://shang.qq.com/wpa/qunwpa?idkey=f70e4d4e0ad2ed6ad67a8b467475e695b286d536c7ff850db945542188871fc6" target="_blank" rel="noreferrer">群①：50304283</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://shang.qq.com/wpa/qunwpa?idkey=7f77ff420f91ae529eef4045557d25553f3362f4c076d575a09974396597c88c" target="_blank" rel="noreferrer">群②：640279557</a></td>
            </tr>
            <tr>
                <td>联系我们</td>
                <td><a href="mailto:service@hisiphp.com" target="_blank" rel="noreferrer">service@hisiphp.com</a></td>
            </tr>
        </tbody>
    </table>
</div>