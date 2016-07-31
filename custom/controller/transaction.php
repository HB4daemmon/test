<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/../Model/AccountTransaction.php";
require_once dirname(__FILE__)."/../Model/GiftcardAccount.php";
require_once dirname(__FILE__)."/../Model/RaiseOrder.php";

function goBack(){
    echo "<br><a href='../view/finance.php'>Back</a>";
    exit;
}

$_cards = $_REQUEST['card'];
$transaction_type = $_REQUEST['transaction_type'];
$_note = trim($_REQUEST['note']);
$new_card = trim($_REQUEST['new_card']);
$pin = trim($_REQUEST['pin']);
$amount = floatval(trim($_REQUEST['amount']));

if($transaction_type == 'buy_new'){
    $note = $new_card;
}else{
    $note = $_note;
}

//Form validate
$cards = array();
foreach($_cards as $c){
    if (trim($c) != ''){
        array_push($cards,trim($c));
    }
}

if(count($cards) == 0){
    echo "There has no card, please check!";
    goBack();
}

if($transaction_type == 'buy_new' && ($new_card == '' || $pin == '')){
    echo "Please enter new card account and pin!";
    goBack();
}

if($amount<=0 || $amount == ''){
    echo "Please enter amount";
    goBack();
}

$gift_card = new GiftcardAccount('');
$total_amount = 0;
$operate = array();
$remain = $amount;
foreach($cards as $c){
    if($gift_card->ifIdExisted($c) == 0){
        echo "Card [$c] is not existed, please check.";
        goBack();
    }else{
        $_card = new GiftcardAccount($c);
        $_amount = $_card->get('remaining');
        if($_amount == 0){
            echo "Card [$c]'s remaining is $0"."<br>";
        }else{
            $total_amount += $_amount;
            if($remain != 0){
                if($_amount - $remain >= 0){
                    $operate[$c] = $remain;
                    $remain = 0;
                }else{
                    $operate[$c] = $_amount;
                    $remain -= $_amount;
                }
            }
        }
    }
}

if($total_amount < $amount){
    echo "The total of all cards' remaining is less then amount.";
    goBack();
}

$transaction = new AccountTransaction('');

foreach($operate as $k=>$v){
    $card = new GiftcardAccount($k);
    $origin_remaining = $card->get('remaining');
    $remaining = floatval($origin_remaining)-floatval($v);
    $d = array("remaining"=>$remaining);
    //$card->set("remaining",0);
    $card->setData($d);
    $update_res = $card->update();

    $d = array("account_number "=>$k,"origin_remaining"=>$origin_remaining ,"transaction_amount"=>$v,
        "transaction_type"=>$transaction_type,"note"=>$note);

    $transaction->setData($d);
    $res = $transaction->create();

    if($update_res == 'success'){
        echo "Card[$k] used $v,remain ".$remaining."<br>";
    }else{
        echo "Card[$k] used failed"."<br>";;
    }
}

if($transaction_type == 'buy_new'){
    $card = new GiftcardAccount('');
    $d = array("line_number "=>1,"giftcard_type"=>"walmart" ,"order_number"=>'',"name"=>'Walmart',"account_number"=>$new_card,
        "pin"=>$pin,"value"=>$amount,"price"=>$amount,"remaining"=>$amount,"discount_rate"=>0,
        "note"=>'');
    $card->setData($d);
    $res = $card->create();
    if($res == 'success'){
        echo "Create Card[$new_card] success"."<br>";
    }else{
        echo "Create Card[$new_card] failed"."<br>";
    }
}

goBack();

?>