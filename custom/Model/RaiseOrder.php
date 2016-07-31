<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/BaseModel.php";

class RaiseOrder extends BaseModel{
    private $data = array();
    private $id;
    private $table_name;
    private $code;
    private $id_column;
    public function __construct($id){
        $this->setTableName("raise_order_info");
        $this->setIdColumn("order_number");
        parent::__construct($id);
    }

}

?>