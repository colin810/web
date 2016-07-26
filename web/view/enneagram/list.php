<?php $this->render('/enneagram/_include/header');?>
<style>
	table.table>tbody>tr>td,table.table>tbody>tr>th {vertical-align: middle;text-align: center;border: 1px solid #ccc;}
</style>

<div class="container">
	<div class="page-header">
	  	<h1>测试结果列表</h1>
	</div>

	<form method="get" class="form-inline">
		<div class="clearfix">
			<div class="input-group pull-right">
		      <input type="text" name="search" class="form-control" placeholder="Search for..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : '' ?>">
		      <span class="input-group-btn">
		        <button type="submit" class="btn btn-default">查询</button>
		      </span>
		    </div>
	    </div>
	</form>
	<br/>
	<table class="table">
		<tr>
			<th>序号</th>
			<th>日期</th>
			<th>姓名</th>
			<th>电话号码</th>
			<th>判断</th>
		</tr>

		<?php foreach ($this->result as $key => $value) {?>
		<tr>
			<td valign="middle"><?php echo ($this->page['cur_page'] - 1) * ($this->page['page_size'] / 9) + $key + 1; ?></td>
			<td><?php echo date('Y-m-d H:i:s', $value['create_time']) ?></td>
			<td><?php echo $value['create_clerk'] ?></td>
			<td><?php echo $value['mobile'] ?></td>
			<td>
				<a class="btn btn-success detail" role="button" data-toggle="popover" session-id="<?php echo $value['session_id'] ?>">
				<?php echo $this->enneagram[$value['result']]['enneagram_name'] ?>
				</a>
			</td>
		</tr>
		<?php }?>

	</table>
	<?php $this->render('_include/page')?>
</div>

<?php $this->render('/enneagram/_include/footer')?>
<script>
$(function(){
	$(".detail").popover({
		'html': true,
		'content' : function(e) {
			var session_id = $(this).attr('session-id');
			var t = '';
			$.ajax({
				type   : 'POST',
				url    : '<?php echo $this->createUrl('detail') ?>',
				data   : { 'session_id' : session_id },
				async  : false,
				success: function(data) {
					t = data;
				}
			});
			return t;
		}
	});

});
</script>
