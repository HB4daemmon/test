<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/../Model/AccountTransaction.php";
require_once dirname(__FILE__)."/../Model/GiftcardAccount.php";
require_once dirname(__FILE__)."/../Model/RaiseOrder.php";

function CSVFormat($str){
    return trim($str,"#");
}

if ($_FILES["file"]["error"] > 0)
{
    echo "Upload file error: " . $_FILES["file"]["error"] . "<br />";
}
else
{
    $type = $_REQUEST["type"];
    $file = dirname(__FILE__)."/../upload/" . $_FILES["file"]["name"];
    if (file_exists($file)){
        unlink($file);
    }
    move_uploaded_file($_FILES["file"]["tmp_name"], $file);
    chmod($file, 0755);
}

if (file_exists($file))
{
    $f = fopen($file,'r');
    if($type == 'raise_order'){
        $lines = 0;
        while($data = fgetcsv($f)){
            $lines ++;
            if($lines == 1 and CSVFormat($data[0]) != 'IN001'){
                echo "The file is not correct, please use template IN001<br>";
                echo "<a href='../view/finance.php'>Back</a>";
                exit;
            }else if($lines >= 3){
                $date = date('Y-m-d H:i:s',strtotime(CSVFormat($data[0])));
                $order_number = CSVFormat($data[1]);
                $items = CSVFormat($data[2]);
                $total = trim(CSVFormat($data[3]),"$");
                $status = CSVFormat($data[4]);
                $d = array("order_date"=>$date,"order_number"=>$order_number,"items"=>$items,"total"=>$total,"status"=>$status);
                $order = new RaiseOrder('');

                if($order->ifIdExisted($order_number) == 0){
                    $order->setData($d);
                    $result = $order->create();
                }else{
                    $result = array("errormsg"=>"Order repeat");
                }

                if($result == 'success'){
                    echo "Order[".$order_number."]import into system success！<br>";
                }else{
                    echo "Order[".$order_number."]import into system failed！[".$result['errormsg']."]<br>";
                }
            }
        }
        echo "<a href='../view/finance.php'>返回</a>";
    }
    else if($type == 'raise_account'){
        $lines = 0;
        while($data = fgetcsv($f)){
            $lines ++;
            if($lines == 1 and CSVFormat($data[0]) != 'IN002'){
                echo "The file is not correct, please use template IN002<br>";
                echo "<a href='../view/finance.php'>Back</a>";
                exit;
            }else if ($lines == 2){
                $order_number = CSVFormat($data[1]);
            }else if($lines >= 4){
                $number = CSVFormat($data[0]);
                $name = CSVFormat($data[1]);
                $account_number = CSVFormat($data[2]);
                $pin = CSVFormat($data[3]);
                $value = CSVFormat($data[4]);
                $price = CSVFormat($data[5]);
                $remaining = CSVFormat($data[6]);
                $discount_rate = floatval(trim(CSVFormat($data[7]),'%'))/100;
                $note = CSVFormat($data[8]);

                $d = array("line_number "=>$number,"giftcard_type"=>"raise" ,"order_number"=>$order_number,"name"=>$name,"account_number"=>$account_number,
                    "pin"=>$pin,"value"=>$value,"price"=>$price,"remaining"=>$remaining,"discount_rate"=>$discount_rate,
                    "note"=>$note);
                $account = new GiftcardAccount('');

                if($account->ifIdExisted($account_number) == 0){
                    $account->setData($d);
                    $result = $account->create();
                }else{
                    $result = array("errormsg"=>"Account repeat");
                }

                if($result == 'success'){
                    echo "Account[".$account_number."]import into system success！<br>";
                }else{
                    echo "Account[".$account_number."]import into system failed！[".$result['errormsg']."]<br>";
                }
            }
        }
        echo "<a href='../view/finance.php'>返回</a>";
    }
    fclose($f);
}

?>