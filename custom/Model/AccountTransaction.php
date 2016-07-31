<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/BaseModel.php";

class AccountTransaction extends BaseModel{
    private $data = array();
    private $id;
    private $table_name;
    private $code;
    private $id_column;
    public function __construct($id){
        $this->setTableName("walmart_account_transaction");
        $this->setIdColumn("transaction_id");
        parent::__construct($id);
    }
}

?>