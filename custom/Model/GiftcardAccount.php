<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/BaseModel.php";

class GiftcardAccount extends BaseModel{
    private $data = array();
    private $id;
    private $table_name;
    private $code;
    private $id_column;
    public function __construct($id){
        $this->setTableName("giftcard_account_info");
        $this->setIdColumn("account_number");
        parent::__construct($id);
    }

}

?>