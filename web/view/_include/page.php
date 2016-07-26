<?php
unset($_REQUEST['cur_page']);
if($this->page && $this->page['page_num'] > 1){
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

<script>
var uri = "<?php echo $_SERVER['PATH_INFO'];?>?";
var query_string = "<?php echo http_build_query($_REQUEST);?>";
$(function(){
    $('.cur_pageva a').click(function(){
        var cur_page = $(this).attr('page');
        go(cur_page);
    });
});

function go(cur_page) {
    var url = uri + 'cur_page=' + cur_page;
    if(query_string) url += '&' + query_string;
    location.href = url;
}
</script>
<?php }?>
