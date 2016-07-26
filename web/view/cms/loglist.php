<?php $this->render('/cms/_include/header'); ?>
<?php $this->render('/cms/_include/top'); ?>

<div class="container">
	<div class="panel panel-default">
	  <div class="panel-heading">
		<div class="row">
		  <form method="get">
		  <div class="col-lg-6">
		    <div class="input-group">
		      <span class="input-group-btn">
		        <h4>修改记录</h4>
		      </span>
		    </div>
		  </div>
		  </form>
		  <form method="get">
		  <div class="col-lg-6">
		    <div class="input-group">
		      <input type="hidden" name="ajax_key_id" value="<?php echo $this->params['key_id'] ?>">
		      <input type="hidden" name="ajax_lang" value="<?php echo $this->params['lang'] ?>">
		      <input type="text" name="search_value" class="form-control" placeholder="Search for..." value="<?php echo $this->params['search_value'] ?>">
		      <span class="input-group-btn">
		        <button type="submit"  class="btn btn-default">Go!</button>
		      </span>
		    </div>
		  </div>
		  </form>
		</div>
	  </div>
	  <table class="table">
	  	<tr>
	  		<th>序号</th>
			<th>内容</th>
			<th>修改人</th>
			<th>修改时间</th>
			<th>修改类型</th>
			<th>操作</th>
	  	</tr>
		<?php foreach ($this->list as $key => $value) { ?>
	  	<tr>
	  		<td align="center"><?php echo $key + 1 ?></td>
			<td width="50%"><span><?php echo $value["content_clean"];?></span></td>
			<td><span><?php echo $value['modify_clerk'] ?></span></td>
			<td><span><?php echo date('Y-m-d H:i:s', $value['modify_time']) ?></span></td>
			<td><span><?php echo $this->opt_flag[$value['opt_flag']] ?></span></td>
			<td><a href="<?php echo $this->createUrl('logdt',array('id'=>$value['id'])) ?>" target="_blank" >详情 </a></td>
	  	</tr>
	  	<?php } ?>
	  </table>
	</div>
	<?php $this->render('/cms/_include/page'); ?>
</div>

<?php $this->render('/cms/_include/footer') ?>
