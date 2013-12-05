<?php
class M_001_TestMigration extends Migrate{
    public function up(){
        $this->db->createTable('cars',array(
            array(
                'name' => 'id_car',
                'type' => 'int',
                'size' => 11,
                'auto_increment' => true,
                'primary_key' => true
            ),
            array(
                'name' => 'name',
                'type' => 'varchar',
                'size' => 255
            )
        ));
    }

    public function down(){
        $this->db->deleteTable('cars');
    }
}