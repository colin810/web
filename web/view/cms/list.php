<?php
    use \lib\Common;
    $this->render('/cms/_include/header');
    $this->render('/cms/_include/top');
?>
<script src="/resource/js/ZeroClipboard.min.js"></script>
<style type="text/css">
.container{width:1500px;}
.popover { max-width: none; width:500px; }
.undo { color:#c7254e; }
</style>

<div class="container">
    <div class="clearfix">
        <?php $this->render('/cms/_include/page');?>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <form id="xform" method="get">
                <div class="row">
                    <div class="col-lg-2">
                        <select class="form-control change_system" name="search_system">
                            <?php foreach ($this->cms_system as $key => $value) {
                                $selected = $this->search_arr['search_system'] == $key ? 'selected' : ''; ?>
                            <option value="<?php echo $key ?>"  <?php echo $selected; ?>><?php echo $value ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="col-lg-6">
                        <div class="checkbox" style="text-align:right;">
                            <?php foreach ($this->cms_lang as $key => $value) {
                                $checked = in_array($key, $this->search_arr['search_lang']) ? 'checked' : '';?>
                            <label>
                                <input class="change_lang" type="checkbox" name="search_lang[]" value="<?php echo $key; ?>" <?php echo $checked; ?> />
                                <?php echo $value . "({$key})"; ?>
                            </label> &nbsp;
                            <?php }?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="currType">
                                    <?php echo $this->cms_config['condition_type'][$this->search_arr['search_condition_type']] ?>
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" id="dropdownType">
                                    <?php foreach ($this->cms_config['condition_type'] as $key => $value) { ?>
                                    <li>
                                        <a href="javascript:;" data-value="<?php echo $key ?>"><?php echo $value; ?></a>
                                    </li>
                                    <?php }?>
                                </ul>
                                <input type="hidden" name="search_condition_type" id="search_condition_type" value="<?php echo $this->search_arr['search_condition_type'] ?>" />
                            </div>
                            <input type="text" name="search_value" class="form-control" placeholder="Search for..." value="<?php echo $this->search_arr['search_value'] ?>" />
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="submit">Go!</button>
                            </span>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="cur_page" name="cur_page" value="<?php echo isset($_GET['cur_page']) ? $_GET['cur_page'] : 1; ?>" />
                <input type="hidden" name="page_size" value="<?php echo isset($_GET['page_size']) ? $_GET['page_size'] : 20; ?>" />
            </form>
        </div>
        <form id="excel_form" method="post" enctype="multipart/form-data">
            <table class="table">
                <tr>
                    <th width="80">序号</th>
                    <th width="250">key_code</th>
                    <?php
                        $size_of_lang = sizeof($this->search_arr['search_lang']);
                        $width = floor(900 / $size_of_lang);
                        for ($i = 0; $i < $size_of_lang; $i++) {
                    ?>
                    <th width="<?php echo $width; ?>">
                        <?php echo $this->cms_lang[$this->search_arr['search_lang'][$i]]; ?>
                    </th>
                    <?php }?>
                    <th>
                        <span>
                            更新时间(GMT+8)
                        </span>
                    </th>
                    <th>
                        <span>
                            操作
                        </span>
                    </th>
                </tr>
                <?php foreach ($this->list as $key => $value) {  ?>
                <tr id="<?php echo $value['key_id'] ?>">
                    <td style="text-align:center;">
                        <input type="checkbox" class="row_id" title="<?php echo $value['key_code'] ?>"/>
                        <?php echo ($this->page['cur_page'] - 1) * $this->page['page_size'] + $key + 1; ?>
                    </td>
                    <td>
                        <?php echo $value['key_code'] ?>
                    </td>
                    <?php for ($i = 0; $i < $size_of_lang; $i++) { ?>
                    <td>
                        <a href="javascript:;" class="content <?php echo $value['max_version'] > $value[$this->search_arr['search_lang'][$i].'_version'] ? 'undo' : ''; ?>" data-lang="<?php echo $this->search_arr['search_lang'][$i] ?>" data-code="<?php echo $value['key_code'] ?>" data-id="<?php echo $value['key_id'] ?>">
                            <span class="glyphicon glyphicon-play" aria-hidden="true">
                            </span>
                            <span class="content_text">
                            <?php if (Common::hasHtml($value[$this->search_arr['search_lang'][$i] . '_content'])) {
                                    echo Common::cutstr($value[$this->search_arr['search_lang'][$i] . '_content_clean'], 30);
                                } else {
                                    echo $value[$this->search_arr['search_lang'][$i] . '_content_clean'];
                                }
                            ?>
                            </span>
                        </a>
                    </td>
                    <?php }?>
                    <td>
                        <span>
                            <?php echo date('Y-m-d H:i:s', $value['modify_time']) ?>
                        </span>
                    </td>
                    <td>
                        <span>
                            <a title="复制" href="javascript:;" class="glyphicon glyphicon-book" id="copy_<?php echo $value['key_id'] ?>"></a>
                        </span>
                    </td>
                </tr>
                <?php }?>
            </table>
        </form>
    </div>
    <?php $this->render('/cms/_include/page');?>
</div>

<script>

ZeroClipboard.config("/resouce/js/ZeroClipboard.swf");

// 隐藏pop
function hidepop(obj, key_id, lang) {
    $(obj).parent().parent().prev().trigger('click');
    window.open('<?php echo $this->createUrl('ueditor') ?>?ajax_key_id=' + key_id + '&ajax_lang=' + lang );
    return false;
}

// pop配置
var options = {
    'title': function(){
        var key_id = $(this).attr('data-id');
        var lang = $(this).attr('data-lang');
        var key_code = $(this).attr('data-code');
        return '<span class="glyphicon glyphicon-fire" aria-hidden="true"></span> ' + key_code + '<a onclick="return hidepop(this,\'' + key_id + '\', \'' + lang + '\');" target="_blank" class="pull-right" href="javascript:;">排版模式</a> <a class="pull-right" href="<?php echo $this->createUrl('loglist') ?>?ajax_key_id=' + key_id + '&ajax_lang=' + lang + '" target="_blank" style="margin-right:10px;">查看修改记录</a>';
    },
    'content': function(){
        var key_id = $(this).attr('data-id');
        var lang = $(this).attr('data-lang');
        var me = $(this);
        $.ajax({
            type   : 'GET',
            url    : '<?php echo $this->createUrl('content') ?>',
            data   : { 'ajax_key_id' : key_id, 'ajax_lang' : lang },
            // async  : false,
            success: function(data) {
                me.next().find('[name="edit_content"]').text(data).focus();
            }
        });

        return '<form class="xform" method="post" onsubmit="return before(this);">' +
                '<table width="100%"> <tr> <td>' +
                    '<input type="hidden" value="' + key_id + '" name="edit_key_id" />' +
                    '<input type="hidden" value="' + lang + '" name="edit_lang" />' +
                    '<textarea name="edit_content" class="form-control"></textarea>' +
                '</td> </tr> ' +
                '<tr> <td valign="middle">' +
                '<div class="checkbox"><label><input type="checkbox" name="edit_version" /> 更新版本 </label> <button class="btn btn-default pull-right btn-primary" type="submit" style="margin-left:10px;">Save</button><button class="btn btn-default pull-right" type="button" onclick="hideForm();">Cancel</button> </div>' +
                '</td> </tr> </table>' +
                '</form>';
    },
    'html': true,
    'placement': 'bottom',
};

$(function(){

    // 初始化pop
    $('.content').popover(options);

    // 隐藏其他pop
    $(document).on('inserted.bs.popover', '.content', function (e) {
        $(".content[aria-describedby]:not([aria-describedby='" + $(this).attr('aria-describedby') + "'])").trigger('click');
    });

    // 分页按钮触发查询表单提交
    $("#dropdownType a").click(function(event) {
        $("#currType").text($(this).text());
        $("#search_condition_type").val($(this).attr('data-value'));
        $("#cur_page").val(1);
        $("#xform").submit();
    });

    // 切换系统触发查询表单提交
    $(".change_system").change(function(){
        $("#cur_page").val(1);
        $("#xform").submit();
    });

    // 切换语言触发查询表单提交
    $(".change_lang").change(function(){
        $("#xform").submit();
    });

    // 绑定clip
    bindClip();
});

// 绑定clip
function bindClip() {
    $(".glyphicon-book").each(function(){
        var clip = new ZeroClipboard($(this));
        clip.on('ready', function(e) {
            clip.on('copy', function (event) {
                var tr_id = event.target.id.replace('copy_', '');
                var content = '';
                $("#" + tr_id).find('.content_text').each(function(){
                    content += $(this).text() + ' ';
                });
                var clipboard = event.clipboardData;
                clipboard.setData( "text/plain", content);
           });
        });
    });
}

// 隐藏pop
function hideForm() {
    $(".content[aria-describedby]").trigger('click');
}

// 表单提交
function before(obj) {
    $.post('<?php echo $this->createUrl('save') ?>', $(obj).serialize(), function(data){
        if (data.status == 'success') {
            var edit_key_id = $(obj).find('[name="edit_key_id"]').val();
            var search_lang = [];
            var index = $("#" + edit_key_id + ' td:eq(0)').text();
            var i = 0;
            // 返回的語言
            $("[name='search_lang[]']").each(function(){
                if(this.checked){
                    search_lang[i] = this.value;
                    i++;
                }
            });
            // 拼接table
            $.get('<?php echo $this->createUrl('getrow') ?>', { 'search_lang' : search_lang, 'edit_key_id' : edit_key_id }, function(row) {
                    if (row.status == 'success') {
                        var result = row.message;
                        var html = '<td style="text-align:center;">' +
                                    '<input type="checkbox" class="row_id" title="' + result.key_code + '"/>' +
                                    index + '</td><td>' + result.key_code + '</td>';
                        $.each(search_lang, function(i, v) {
                            html += '<td>' +
                                    '<a href="javascript:;" class="content ' + (result['max_version'] > result[v + '_version'] ? 'undo' : '') + '" data-lang="' + v + '" data-code="' + result['key_code'] + '" data-id="' + result['key_id'] + '">' +
                                    '<span class="glyphicon glyphicon-play" aria-hidden="true"></span>' +
                                    '<span class="content_text">' + result[v + '_content']  + '</span>' +
                                    '</a></td>';
                        });
                        html += '<td><span>' + result['modify_time'] + '</span></td>' +
                                '<td> <span> <a title="复制" href="javascript:;" class="glyphicon glyphicon-book" id="' + result['key_id'] + '"></a> </span> </td>';
                        $("#" + edit_key_id).empty().append(html);
                        // 绑定pop
                        $('.content').popover(options);
                        // 绑定clip
                        bindClip();
                    }
            }, 'json');
        } else {
            alert(data.message);
        }
    }, 'json');
    return false;
}

// 快捷键 ctrl + s 保存并更新版本
$(window).on('keydown', function(e) {
    if (e.keyCode == 83 && e.ctrlKey) {
        e.preventDefault();
        $(".xform:visible").find("[name='edit_version']").prop('checked', true);
        $(".xform:visible").submit();
    }
});

</script>
<?php
    $this->render('/_include/float');
    $this->render('/cms/_include/footer');
?>
