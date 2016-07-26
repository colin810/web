<link rel="stylesheet" href="/resource/css/float.css" />
<div id="floatPanel">
    <div class="ctrolPanel">
        <a class="arrow" href="#"><span>顶部</span></a>
        <a class="arrow" href="#"><span>底部</span></a>
    </div>
</div>

<script>
$(function(){
    $("#floatPanel > .ctrolPanel > a.arrow").eq(0).click(function() {
        $("html,body").animate({
            scrollTop: 0
        }, 800);
        return false;
    });
    $("#floatPanel > .ctrolPanel > a.arrow").eq(1).click(function() {
        $("html,body").animate({
            scrollTop: $(document).height()
        }, 800);
        return false;
    });
});
</script>
