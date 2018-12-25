/***** HisiPHP By http://www.HisiPHP.com *****/
layui.define(['jquery', 'form'], function(exports) {
    var $ = layui.jquery, form = layui.form;
    var obj = {
        assign: function(formData) {
            var input = '', form = layui.form;
            for (var i in formData) {
                switch($('.field-'+i).attr('type')) {
                    case 'select':
                        input = $('.field-'+i).find('option[value="'+formData[i]+'"]');
                        input.prop("selected", true);
                        break;
                        
                    case 'radio':
                        input = $('.field-'+i+'[value="'+formData[i]+'"]');
                        input.prop('checked', true);
                        break;

                    case 'checkbox':
                        if (typeof(formData[i]) == 'object') {
                            for(var j in formData[i]) {
                                input = $('.field-'+i+'[value="'+formData[i][j]+'"]');
                                input.prop('checked', true);
                            }
                        } else {
                            input = $('.field-'+i+'[value="'+formData[i]+'"]');
                            input.prop('checked', true);
                        }
                        break;

                    case 'img':
                        if (formData[i]) {
                            input = $('.field-'+i);
                            input.attr('src', formData[i]);
                        }
                        break;

                    default:
                        input = $('.field-'+i);
                        input.val(formData[i]);
                        break;
                }
                
                if (input.attr('data-disabled')) {
                    input.prop('disabled', true);
                }

                if (input.attr('data-readonly')) {
                    input.prop('readonly', true);
                }
            }
            form.render();
        },
    };

    exports('func', obj);
}); 