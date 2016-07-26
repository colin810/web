<?php
unset($_REQUEST['cur_page']);
$cur_page   = $this->page['cur_page'];
$page_size  = $this->page['page_size'];
$page_num   = $this->page['page_num'];
$total      = $this->page['total'];

$first      = 1;
$previous   = $cur_page == $first ? $first : $cur_page - 1;
$next       = $cur_page == $page_num ? $page_num : $cur_page + 1;
$last       = $page_num;

$first_class    = $cur_page == 1 ? 'disabled' : 'cur_pageva';
$previous_class = $cur_page == $first ? 'disabled' : 'cur_pageva';
$next_class     = $cur_page == $page_num ? 'disabled' : 'cur_pageva';
$last_class     = $cur_page == $page_num ? 'disabled' : 'cur_pageva';
?>

<div class="pull-left">
    <nav>
      <ul class="pagination">
        <li class="<?php echo $first_class;?>"><a href="javascript:;"  page="<?php echo $first;?>"><span>&laquo;&laquo;</span></a></li>
        <li class="<?php echo $previous_class;?>"><a href="javascript:;"  page="<?php echo $previous;?>"><span>&laquo;</span></a></li>
        <?php for ($i = 1; $i <= $page_num; $i++) {
                $cur_page_class = $cur_page == $i ? 'active' : 'cur_pageva';
                if($cur_page - 5 < $i && $cur_page + 5 > $i){ ?>
        <li class="<?php echo $cur_page_class?>"><a href="javascript:;" page="<?php echo $i;?>"><?php echo $i;?> <span class="sr-only">(current)</span></a></li>
        <?php }} ?>
        <li class="<?php echo $next_class;?>"><a page="<?php echo $next;?>" href="javascript:;"><span>&raquo;</span></a></li>
        <li class="<?php echo $last_class;?>"><a page="<?php echo $last;?>" href="javascript:;"><span>&raquo;&raquo;</span></a></li>
      </ul>
    </nav>
</div>

<div style="width:350px;margin-left:20px;" class="pull-left">
    <nav>
      <ul class="pagination">
        <li <?php if($page_size == 20) echo 'class="active"'; ?>><a class="pagesize" href="javascript:;" pagesize="20"><span>每頁20條</span></a></li>
        <li <?php if($page_size == 50) echo 'class="active"'; ?>><a class="pagesize" href="javascript:;" pagesize="50"><span>每頁50條</span></a></li>
        <li <?php if($page_size == 100) echo 'class="active"'; ?>><a class="pagesize" href="javascript:;" pagesize="100"><span>每頁100條</span></a></li>
        <li <?php if($page_size == 999) echo 'class="active"'; ?>><a class="pagesize" href="javascript:;" pagesize="999"><span>全部顯示</span></a></li>
      </ul>
    </nav>
</div>
<div style="width:260px;" class="pull-right">
    <form onsubmit="return before();" id="pform">
    <nav>
        <ul class="pagination">
            <li>
                <div class="input-group">
                  <input type="number" class="form-control" placeholder="Enter Page Number!"  min="1" max="<?php echo $page_num; ?>" step="1" aria-describedby="basic-addon2" id="page_num" />
                  <span class="input-group-addon" id="basic-addon2">共有 <?php echo $cur_page . ' / ' . $page_num ?> 頁</span>
                </div>
            </li>
        </ul>
    </nav>
    </form>
</div>
<style>
input[type=number] { -moz-appearance:textfield; }
input[type=number]::-webkit-inner-spin-button,input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0;}
</style>
<script>

var uri = "<?php echo $_SERVER['PATH_INFO'];?>?";
var query_string = "<?php echo http_build_query($_REQUEST);?>";
$(function(){
    $('.cur_pageva a').click(function(){
        var cur_page = $(this).attr('page');
        go(cur_page);
    });

    $(".pagesize").click(function(){
        var pagesize = $(this).attr('pagesize');
        var url = uri + 'page_size=' + pagesize;
        if(query_string){
            var args = query_string.split('&');
            for(var i = 0; i < args.length; i++) {
                if (args[i].indexOf('cur_page=') == 0) {
                    continue;
                }
                if (args[i].indexOf('page_size=') == 0) {
                    continue;
                }
                url += '&' + args[i];
            }
        }
        location.href = url;
    });
});

function before(){
    var cur_page = $("#page_num").val();
    if (cur_page > 0) {
        go(cur_page);
    }
    return false;
}

function go(cur_page) {
    var url = uri + 'cur_page=' + cur_page;
    if(query_string) url += '&' + query_string;
    location.href = url;
}
</script>
