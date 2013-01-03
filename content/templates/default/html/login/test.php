<?php echo $this->load->view("../nav/test.php",$this->template->getContext())->render() ?>
<h1><?php echo $title ?></h1>
<?php foreach($loop as $key => $val): ?>
<div><?php echo $val ?></div>
<?php endforeach; ?>

<script type="text/javascript">
    alert(Lang.get('module_not_found_msg',"Prueba"));
</script>