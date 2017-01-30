<?php
require_once dirname(__FILE__) . '/../vendor/PHPMailer/class.phpmailer.php';
require_once dirname(__FILE__)."/connection.php";
define("LOGO_URL","http://cartgogogo.com/skin/frontend/default/ma_orion/images/cartgogo_logo.jpg");

function sendmail($to,$subject,$content) {
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPAuth = true;
	$mail->IsHTML(true);
	$mail->SMTPSecure = "ssl";
	$mail->CharSet ="utf-8";
	$mail->Encoding = "base64";
	$mail->AddAddress($to, "");
	$mail->Subject = $subject;
	$mail->Body    = $content;
//	$host    = 'smtp.gmail.com';
//	$username = 'cartgogogo@gmail.com';
//	$password = 'startup2015';
//	$from    = 'cartgogogo@gmail.com';
//	$fromname = 'Cartgogogo Team';
    $host    = 'smtp.163.com';
    $username = 'hb4daemon@163.com';
    $password = 'HB4daemon';
    $from    = 'hb4daemon@163.com';
    $fromname = 'Cartgogogo Team';
	$mail->Host    = $host;
	$mail->Username = $username;
	$mail->Password = $password;
	$mail->From    = $from;
	$mail->FromName = $fromname;
	$mail->Port = 465;
	$mail->SMTPDebug = true;
	$res = $mail->send();
    return $res;
}

function fill_mail($host, $content) {
	
    $html = "
<html>
<body style='text-align:center;'>
	<div>
		<div style='background: none repeat scroll 0% 0% rgb(37, 16, 42); text-align:center;margin:auto;padding: 15px; width: 670px;'>
		</div>
		<div style='text-align:center;margin:auto;padding-left: 15px;padding-right:15px; width: 670px;font-family:proxima-nova,Proxima Nova, sans-serif;'>
			<p style='font-size:40px;'>Celebrate life everyday!</p>
			<p style='font-size:16px;color:#4f4f4f;'>$content</p>
		</div>
		<div style='background: none repeat scroll 0% 0% rgb(37, 16, 42); text-align:left;margin:auto;padding-left:10px;padding-right:10px;padding-top:5px;padding-bottom: 5px;width:680px;color:white;font-family:proxima;'>
			<p style='margin-left:30px;font-family:proxima-nova,Proxima Nova, sans-serif;'>2015&copy;Kantwait</p>
		</div>
		
	</div>
</body>
</html>
    ";
    return $html;
}

function getCompleteTemplate($order_id){
    try{
        $conn = db_connect();
        $sql = sprintf("select grand_total,shipping_amount,subtotal,total_qty_ordered,o.customer_email,concat(o.customer_firstname,' ',o.customer_lastname) as user_name,
                        o.created_at,a.region,a.city,a.street,a.telephone,a.postcode,o.grand_total-o.shipping_amount-o.subtotal as tips
                        ,concat(
                        case when os.time_range < 11 then concat(os.time_range,' am')
							 when os.time_range = 12 then 'noon'
                             else concat(os.time_range-12,' pm')
                             end
                        ,'-',
                        case when os.time_range+1 < 11 then concat(os.time_range+1,' am')
							 when os.time_range+1 = 12 then 'noon'
                             else concat(os.time_range-11,' pm')
                             end
                        ) as delivery_window,os.date
                        from sales_flat_order o,sales_flat_order_address a,sales_flat_order_storegroup os
                        where a.parent_id = o.entity_id
                        and a.address_type = 'shipping'
                        and os.order_id = o.entity_id
                        and o.entity_id = %s",$order_id);
        $order_res = $conn->query($sql);
        if(!$order_res){
            throw new Exception(sprintf("select order info error.MSG[%s]",$sql));
        }
        $order = $order_res->fetch_assoc();

        $delivery_dates = explode('-',$order['date']);
        $delivery_date = mktime(0,0,0,$delivery_dates[0],$delivery_dates[1],$delivery_dates[2]);
        $delivery_window = $order['delivery_window'].','.date('F d, Y, l',$delivery_date);

        $item_sql = sprintf("select sku,name,sub_name,item_status,qty_ordered,price,if(item_status = '' or item_status = 'pick_up','pick_up',item_status) as item_status,
                            case when item_status = '' or item_status = 'pick_up' then qty_ordered when item_status = 'substitute' then sub_volume when item_status = 'out_of_stock' then 0 else 0 end as qty_ordered_true,
                            if(item_status = 'substitute',sub_price,price) as price_true
                            from sales_flat_order_item
                            where order_id = %s",$order_id);
        $item_res = $conn->query($item_sql);
        if(!$item_res){
            throw new Exception(sprintf("select item info error.MSG[%s]",$sql));
        }
        $items = array();
        $sub_items = array();
        $oos_items = array();
        while($row = $item_res->fetch_assoc()){
            array_push($items,$row);
            if($row['item_status'] == 'substitute'){
                array_push($sub_items,$row);
            }else if ($row['item_status'] == 'out_of_stock'){
                array_push($oos_items,$row);
            }
        }
        $conn->close();
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"error_msg"=>$e->getMessage());
    }

    $item_table = "";

    if(count($sub_items) != 0){
        $item_table = $item_table."
        <p>Per your instructions and store availability, a few substitutions had to be made:</p>";
        foreach($sub_items as $s){
            if(trim($s['sub_name']) != ''){
                $item_table = $item_table.sprintf("
                <p>[Item: %s 	Qty: %s	Subtotal: $%s ] ——>> [Item: %s 	Qty: %s 	Subtotal $%s]</p>
            ",$s['name'],number_format($s['qty_ordered'],2),number_format(round(intval($s['qty_ordered'])*floatval($s['price']),2),2),
                        $s['sub_name'],number_format($s['qty_ordered_true'],2), number_format(round(intval($s['qty_ordered_true'])*floatval($s['price_true']),2),2));
            }else{
                $item_table = $item_table.sprintf("
                <p>[Item: %s 	Qty: %s	Subtotal: $%s ] ——>> [Qty: %s 	Subtotal $%s][Price or Qty updated]</p>
            ",$s['name'],number_format($s['qty_ordered'],2),number_format(round(intval($s['qty_ordered'])*floatval($s['price']),2),2),
                        number_format($s['qty_ordered_true'],2), number_format(round(intval($s['qty_ordered_true'])*floatval($s['price_true']),2),2));

            }

        }
    }

    if(count($oos_items) != 0){
        $item_table = $item_table."
        <p>Unfortunately, our shoppers could not locate the following item(s) you requested:</p>";
        foreach($oos_items as $s){
            $item_table = $item_table.sprintf("
                <p>[Item: %s	Qty: %s 	Subtotal: $%s ]</p>
            ",$s['name'],number_format($s['qty_ordered'],2), number_format(round(intval($s['qty_ordered'])*floatval($s['price']),2),2));
        }
    }

    $subtotal = 0;
    foreach($items as $i){
        $item_table = $item_table.sprintf("
                <tr style='text-align: center'>
                    <td style='text-align: left'>
                        %s
                    </td>
                    <td style='text-align: left'>
                        %s
                    </td>
                    <td>
                        %s
                    </td>
                    <td >
                        %s
                    </td>
                    <td style='text-align: left'>
                        $%s
                    </td>
                </tr>
		",$i['name'],$i['sku'],$i['item_status'],$i['qty_ordered_true'],number_format(intval($i['qty_ordered_true'])*floatval($i['price_true']),2));
        $subtotal += round(intval($i['qty_ordered_true'])*floatval($i['price_true']),2);
    }
    $item_table = $item_table.sprintf("
        <tr>
        <td>&nbsp;</td>
        </tr>
        <tr style='text-align: right'>
                    <td colspan='3' ><b>Subtotal</b></td>
                    <td colspan='1' ></td>
                    <td colspan='1' style='text-align: left'>$%s</td>
                </tr>
                <tr style='text-align: right'>
                    <td colspan='3'><b>Tips</b></td>
                    <td colspan='1' ></td>
                    <td colspan='1' style='text-align: left'>$%s</td>
                </tr>
                <tr style='text-align: right'>
                    <td colspan='3'><b>Shipping amount</b></td>
                    <td colspan='1' ></td>
                    <td colspan='1' style='text-align: left'>$%s</td>
                </tr>
                <tr style='text-align: right'>
                    <td colspan='3'><b>Grand total</b></td>
                    <td colspan='1' ></td>
                    <td colspan='1' style='text-align: left'>$%s</td>
                </tr>",number_format($subtotal,2),number_format($order['tips'],2),number_format($order['shipping_amount'],2),number_format($subtotal+$order['tips']+$order['shipping_amount'],2));

    $item =  "<div style='text-align:center;margin:auto;padding-left: 15px;padding-right:15px; width: 670px;'>
			<table style='text-align: center'>
                <tr>
                    <td style='width:60%'>
                        Item
                    </td>
                    <td style='width:10%'>
                        Sku
                    </td>
                    <td style='width:10%'>
                        Status
                    </td>
                    <td style='width:10%'>
                        Qty
                    </td>
                    <td style='width:10%'>
                        Subtotal
                    </td>
                </tr>
                $item_table
                </table>

        </div>";


    $html = sprintf("
        <html>
<body style='text-align:left;'>
	<div>
		<div style='text-align:left;margin:auto;padding: 15px; width: 670px;'>
            <div>
                <img src='%s' style='width:200px'>
            </div>
            <p style='font-size:30px'>
                Dear %s
            </p>
            <p>
                Your order is now on its way from the store to your door!
            </p>
            <p style='font-size:25px'>
                Your delivery time:<span style='font-size:18px'>(%s)</span>
            </p>
            <div style='font-size:20px'>
                <div style='font-size:25px'>
                    Delivery to : <span >%s</span>
                </div>
                <div>
                    %s
                </div>
                <div>
                    %s,%s
                </div>
                <div>
                    %s
                </div>
                <div>
                    tel:%s
                </div>
            </div>


		</div>
		%s
		<div style='text-align:left;margin:auto;width:670px;'>
		    <p>Thank you for your business, </p>
			<p>2016&copy;Cartgogogo</p>
		</div>

	</div>
</body>
</html>
    ",LOGO_URL,$order['user_name'],$delivery_window,$order['user_name'],$order['street'],$order['city'],$order['postcode'],$order['region'],$order['telephone'],$item);

return array("html"=>$html,"email"=>$order['customer_email']);
}

?>
