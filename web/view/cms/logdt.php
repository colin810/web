<?php $this->render('/cms/_include/header'); ?>
<?php $this->render('/cms/_include/top'); ?>

<div class="container">
    <div class="panel panel-default">
	  <div class="panel-heading"><?php echo $this->model['key_code'] ?></div>
	  <div class="panel-body">
	  	<table class="table">
	  		<tr>
	  			<td width="100">所属系统：</td>
				<td><?php echo $this->cms_config['system'][$this->model['system']] ?></td>
	  		</tr>
			<tr>
	  			<td>翻译语言：</td>
				<td><?php echo $this->cms_config['lang'][$this->model['lang']] ?></td>
	  		</tr>
	  		<tr>
	  			<td>修改人：</td>
				<td><?php echo $this->model['modify_clerk'] ?></td>
	  		</tr>
	  		<tr>
	  			<td>修改时间：</td>
				<td><?php echo date('Y-m-d H:i:s',$this->model['modify_time']) ?></td>
	  		</tr>
	  		<tr>
	  			<td>操作类型：</td>
				<td><?php echo $this->cms_config['opt_flag'][$this->model['opt_flag']]?></td>
	  		</tr>
	  		<tr>
	  			<td>翻译内容：</td>
				<td><?php echo $this->model['content'] ?></td>
	  		</tr>
	  		<tr>
	  			<td>备注：</td>
				<td><?php echo $this->model['remark'] ?></td>
	  		</tr>
	  	</table>
	  </div>
	</div>

</div>


<?php $this->render('/cms/_include/footer') ?>
