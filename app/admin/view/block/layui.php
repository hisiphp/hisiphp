<script src="__ADMIN_JS__/layui/layui.js?v={:config('hisiphp.version')}"></script>
<script>
    var ADMIN_PATH = "{$_SERVER['SCRIPT_NAME']}", LAYUI_OFFSET = 60;
    layui.config({
        base: '__ADMIN_JS__/',
        version: '{:config("hisiphp.version")}'
    }).use('global');
</script>