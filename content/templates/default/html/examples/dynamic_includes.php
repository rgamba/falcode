<h1>Dynamic includes</h1>
<p>This loads an external view from the file content/templates/default/html/nav/test.php (but CAN'T access this view's variables):</p>
<?php echo $this->load->view("../nav/test.php")->render(); ?>
<br>
<p>This loads an external view from the file content/templates/default/html/nav/test.php (and CAN access this view's variables):</p>
<?php echo $this->load->view("../nav/test.php",array('test' => $test))->render(); ?>
<br>
<p>This loads an external view from the file content/templates/default/html/nav/nested_includes.php, which loads a dynamic view itself:</p>
<?php echo $this->load->view("../nav/nested_includes.php",array('test' => $test))->render(); ?>