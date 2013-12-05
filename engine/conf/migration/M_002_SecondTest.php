<?php
class M_002_SecondTest extends Migrate{
    public function up(){
        $this->db->addField("cars",array(
            'name' => 'brand',
            'type' => 'varchar',
            'size' => '100',
            'key' => 'brand'
        ));
    }

    public function down(){
        $this->db->deleteField("cars","brand");
    }
}