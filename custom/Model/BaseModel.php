<?php
require_once dirname(__FILE__)."/../util/connection.php";

class BaseModel{
    private $data = array();
    private $id_column;
    private $id;
    private $table_name;
    private $code;
    public function __construct($id){
        if($id != '' and $this->getTableName() != ''){
            $this->setId($id);
            $this->setData($this->getResource());
        }
    }

    public function setIdColumn($id_column){
        $this->id_column = $id_column;
    }

    public function getIdColumn(){
        return $this->id_column;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getId(){
        return $this->id;
    }

    public function setCode($code){
        $this->code = $code;
    }

    public function getCode(){
        return $this->code;
    }

    public function setData($data){
        $this->data = $data;
    }

    public function getData(){
        return $this->data;
    }

    public function set($column,$value){
        $data = $this->data;
        $data[$column] = $value;
    }

    public function get($column){
        $data = $this->data;
        return $data[$column];
    }

    public function update(){
        try{
            $id_column = $this->getIdColumn();
            $id = $this->getId();
            $conn = db_connect('finance');
            if($id != ''){
                $data = $this->getData();
                $table_name = $this->getTableName();
                if(isset($data) and isset($table_name) and $table_name != ''){
                    $update_string = '';
                    foreach($data as $k=>$v){
                        $update_string .= $k."= '$v',";
                    }
                    $update_string = trim($update_string,',');
                    $sql="update $table_name set $update_string where $id_column = '$id'";
                    $sqlres = $conn->query($sql);
                    if(!$sqlres){
                        throw new Exception("Insert Error");
                    }
                    return "success";
                }else{
                    throw new Exception("Lack Information.");
                }
            }else{
                throw new Exception("Lack id");
            }

            $conn->commit();
            $conn->close();
            return array("success");
        }catch (Exception $e){
            $conn->close();
            return array("errormsg"=>$e->getMessage());
        }
    }

    public function create(){
        try{
            $conn = db_connect('finance');
            $data = $this->getData();
            $table_name = $this->getTableName();
            $column_string = "";
            $value_string = "";
            $id_column = $this->getIdColumn();
            if(isset($data) and isset($table_name) and $table_name != ''){
                foreach($data as $k=>$v){
                    $column_string .= $k.",";
                    $value_string .= "'".$v."',";
                }
                $column_string = trim($column_string,',');
                $value_string = trim($value_string,',');
                $sql="insert into $table_name($column_string) values($value_string)";
                $sqlres = $conn->query($sql);
                if(!$sqlres){
                    throw new Exception("Insert Error".$sql);
                }
                $sql = "select last_insert_id() as id ";
                $sqlres = $conn->query($sql);
                $row = $sqlres->fetch_assoc();
                $this->setId($row['id']);
                return "success";
            }else{
                throw new Exception("Lack Information.");
            }


            $conn->commit();
            $conn->close();
            return array("success");
        }catch (Exception $e){
            $conn->close();
            return array("errormsg"=>$e->getMessage());
        }
    }

    public function setTableName($table){
        $this->table_name = $table;
    }

    public function getTableName(){
        return $this->table_name;
    }

    public function getResource(){
        try{
            $conn = db_connect('finance');
            $table_name = $this->getTableName();
            $id_column = $this->getIdColumn();
            $id = $this->getId();
            $sql = "select * from $table_name where $id_column = '$id'";
            $sqlres = $conn->query($sql);
            if(!$sqlres){
                throw new Exception("Query Error");
            }
            $row = $sqlres->fetch_assoc();
            $conn->close();
            return $row;
        }catch (Exception $e){
            $conn->close();
            return array("errormsg"=>$e->getMessage());
        }
    }

    public function createId(){
        try{
            $conn = db_connect('finance');
            $table_name = $this->getTableName();
            $sql = "select max(id) as new_id from $table_name";
            $sqlres = $conn->query($sql);
            if(!$sqlres){
                throw new Exception("Query Error");
            }
            $row = $sqlres->fetch_assoc();
            $new_id = intval($row['new_id'])+1;
            $conn->close();
            return $new_id;
        }catch (Exception $e){
            $conn->close();
            return array("errormsg"=>$e->getMessage());
        }
    }

    public function getIdFromCode($code){
        try{
            $conn = db_connect('finance');
            $table_name = $this->getTableName();
            $code_name = $this->getCode();
            $id_column = $this->getIdColumn();
            if($table_name != ''){
                $sql = "select $id_column from $table_name where $code_name = '$code'";
                $sqlres = $conn->query($sql);
                if(!$sqlres){
                    throw new Exception("Query Error");
                }
                $row = $sqlres->fetch_assoc();
                $id = $row['id'];
                return $id;
            }else{
                return 'error';
            }

        }catch (Exception $e){
            return array("errormsg"=>$e->getMessage());
        }
    }

    public function ifIdExisted($tid){
        try{
            $conn = db_connect('finance');
            $table_name = $this->getTableName();
            $id_column = $this->getIdColumn();
            $sql = "select * from $table_name where $id_column = '$tid'";
            $sqlres = $conn->query($sql);
            if(!$sqlres){
                throw new Exception("Query Error MSG:".$sql);
            }
            $count = $sqlres->num_rows;
            $conn->close();
            return $count;
        }catch (Exception $e){
            $conn->close();
            return array("errormsg"=>$e->getMessage());
        }
    }
}
?>