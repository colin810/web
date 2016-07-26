function showError(message) {
    $(".error").hide();
    $.each(message, function(k, v) {
        $("." + k + "_error").empty().html(v).show();
    });
}

function submitForm(options) {
    var xform = options['form'] ? options['form'] : "#xform";
    var beforeSubmitCallBack = options['beforeSubmit'];
    var errorCallBack = options['error'];
    var successCallBack = options['success'];
    var submitBtn = options['submitBtn'];
    if (submitBtn) {
        var defVal = $(submitBtn).val();
        var loadingFun = function() {
            $(submitBtn).attr('disabled', 'disabled').text('Loading...').val('Loading...');
        }
        var normalFun = function() {
            $(submitBtn).removeAttr('disabled').text(defVal).val(defVal);
        }
    }
    $(xform).ajaxForm({
        dataType: 'json',
        beforeSubmit: function(a, f, o) {
            if ($.isFunction(loadingFun)) {
                loadingFun();
            }
            if ($.isFunction(beforeSubmitCallBack)) {
                return beforeSubmitCallBack();
            }
        },
        error: function(e) {
            if (e.status != '200') {
                alert(e.statusText);
            }
            if ($.isFunction(normalFun)) {
                normalFun();
            }
            if ($.isFunction(errorCallBack)) {
                errorCallBack();
            }
        },
        success: function(data) {
            if ($.isFunction(normalFun)) {
                normalFun();
            }
            if ($.isFunction(successCallBack)) {
                successCallBack(data);
            }
        }
    });
}
