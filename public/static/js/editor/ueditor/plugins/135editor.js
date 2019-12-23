UE.registerUI('135editor',function(editor,uiName){
    var dialog = new UE.ui.Dialog({
        iframeUrl: editor.options.UEDITOR_HOME_URL+'dialogs/135editor/135EditorDialogPage.html',
        cssRules:"width:"+ parseInt(document.body.clientWidth*0.9) +"px;height:"+(window.innerHeight -50)+"px;",
        editor:editor,
        name:uiName,
        title:"135编辑器"
    });
    dialog.fullscreen = false;
    dialog.draggable = false;
    var btn = new UE.ui.Button({
        name:'btn-dialog-' + uiName,       
        className:'edui-for-135editor',
        title:'135编辑器',
        onclick:function () {
            dialog.render();
            dialog.open();
        }
    });
    return btn;
},undefined);
// 修改最后的undefined参数为数字序号，比如5，可调整135编辑器按钮的顺序。默认出现在最后面