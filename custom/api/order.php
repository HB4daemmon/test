<?php
require_once dirname(__FILE__)."/../util/connection.php";
require_once dirname(__FILE__)."/../util/mailer.php";

function getOrderList($status,$increment_id,$date){
    try{
        $conn = db_connect();
        if(!$status){
            $status_sql = ' ';
        }else{
            $status_sql = " and o.state = '$status' ";
        }

        if($status != 'processing' && $status != 'pending' && $status != 'canceled' && $status){
            throw new Exception("Status[".$status."] is invalid");
        }

        if(!$increment_id){
            $increment_sql = ' ';
        }else{
            $increment_sql = " and o.increment_id = '$increment_id' ";
        }

        if(!$date){
            $date_sql = '';
        }else{
            #$date_sql = " and unix_timestamp(date(o.created_at)) = unix_timestamp('$date') ";
            $date_sql = " and os.date = '$date'";
        }

        $sql = "SELECT o.entity_id as order_id,o.status,o.state,o.customer_id,o.base_grand_total,o.base_shipping_amount,
                    o.base_subtotal,o.grand_total,o.shipping_amount,o.subtotal,o.total_qty_ordered,o.shipping_address_id,
                    o.increment_id,o.customer_email,o.customer_firstname,o.customer_lastname,o.created_at
                    ,c.email,concat(os.date,' ',os.time_range,':00-',os.time_range+1,':00') as delivery_window,
                    c.reg_phone,s.name as store_name,driver_confirmed,soc.value as tips,
                    o.order_completed,o.tax_amount as order_tax
                    FROM sales_flat_order o,
                    sales_order_custom soc,
                    sales_flat_order_storegroup os,
                    (select c.entity_id,c.email,c_v.value as reg_phone from customer_entity c
                    left join eav_attribute c_eav on c.entity_type_id = c_eav.entity_type_id and c_eav.attribute_code = 'phone_number'
                    left join customer_entity_varchar c_v on c_eav.attribute_id = c_v.attribute_id and c_v.entity_id = c.entity_id) as c,
                    core_store s
                    where o.customer_id = c.entity_id
                    and o.parent_order_id is not null
                    and o.base_subtotal != 0
                    and os.order_id = o.entity_id
                    and o.store_id = s.store_id
                    and soc.order_id = o.entity_id
                    and soc.key = 'other'
                    $increment_sql
                    $status_sql
                    $date_sql
                    order by o.entity_id;";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select order list error.Msg:[$sql]");
        }
        $orders = array();
        while ($row = $res->fetch_assoc()){
            $shipping_address_id = $row['shipping_address_id'];
            $address = getAddress($shipping_address_id);
            if($address['success'] == 0){
                //throw new Exception($address['error_msg']);
                $row['address'] = '';
            }else{
                $row['address'] = $address['data'];
            }
            array_push($orders,$row);
        }

        $conn->close();
        return array("success"=>1,"data"=>$orders);
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function getAddress($shipping_address_id){
    try{
        $conn = db_connect();
        $sql = "select entity_id as address_id,region,postcode,lastname,street,city,email,telephone,country_id
                ,firstname,company from sales_flat_order_address
                where entity_id = $shipping_address_id;";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select customer address error.Msg[$sql]");
        }
        if($res->num_rows == 0){
            throw new Exception("This address is invalid");
        }
        $row = $res->fetch_assoc();
        $conn->close();
        return array("success"=>1,"data"=>$row);
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function getOrderDetail($increment_id){
    try{
        $conn = db_connect();
        $image_url = getImageBase();
        if(!$increment_id){
            throw new Exception("increment_id is null");
        }
        $sql = "select o.increment_id,oi.item_id,oi.product_id,oi.store_id,oi.product_id,oi.weight,oi.sku,oi.name,oi.qty_ordered,
                oi.price,cped.value as store_price,oi.base_price,oi.base_original_price,oi.row_total,oi.price_incl_tax,
                oi.base_price_incl_tax,oi.row_total_incl_tax,oi.base_row_total_incl_tax,
                if(oi.substitute=1,'Y','N') as substitute,oi.customer_message,oi.item_status,oi.sub_price,oi.sub_volume,
                oi.tax_percent,oi.tax_amount
                 from sales_flat_order_item oi,sales_flat_order o,catalog_product_entity_varchar cped,eav_attribute ea
                where oi.order_id = o.entity_id
                and oi.product_id = cped.entity_id
                and cped.attribute_id = ea.attribute_id
				and attribute_code = 'store_price'
                and ea.entity_type_id = 4
                and o.increment_id = $increment_id;";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select order detail error.Msg[$sql]");
        }
        $goods_detail = array();
        while($row = $res->fetch_assoc()){
            $product_id = $row['product_id'];
            $store_id = $row['store_id'];
            $location = getEavProductAttr($product_id,$store_id,'location');
            $image = getEavProductAttr($product_id,$store_id,'image');
            $quantity = getEavProductAttr($product_id,$store_id,'quantity');
            $row['location'] = $location;
            if($image){
                $row['image']=$image_url.$image;
            }
            $row['quantity'] = $quantity;
            array_push($goods_detail,$row);
        }
        $order = getOrderList('',$increment_id,'')['data'][0];
        $order['goods'] = $goods_detail;
        $conn->close();
        return array("success"=>1,"data"=>$order);
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function getItemDetail($item_id){
    try{
        $conn = db_connect();
        $image_url = getImageBase();
        if(!$item_id){
            throw new Exception("item_id is null");
        }
        $sql = "select o.increment_id,o.entity_id as order_id,oi.item_id,oi.store_id,oi.product_id,oi.weight,oi.sku,oi.name,oi.qty_ordered,
                oi.price,cped.value as store_price,oi.base_price,oi.base_original_price,oi.row_total,oi.price_incl_tax,
                oi.base_price_incl_tax,oi.row_total_incl_tax,oi.base_row_total_incl_tax,
                if(oi.substitute=1,'Y','N') as substitute,oi.customer_message,oi.sub_name,oi.sub_price,oi.sub_volume,oi.item_status,oi.markup
                 from sales_flat_order_item oi,sales_flat_order o,catalog_product_entity_varchar cped,eav_attribute ea
                where oi.order_id = o.entity_id
                and oi.product_id = cped.entity_id
                and cped.attribute_id = ea.attribute_id
				and attribute_code = 'store_price'
                and ea.entity_type_id = 4
                and oi.item_id = $item_id;";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select item detail error.Msg[$sql]");
        }
        $item_detail = array();
        $row = $res->fetch_assoc();
        $product_id = $row['product_id'];
        $store_id = $row['store_id'];
        $location = getEavProductAttr($product_id,$store_id,'location');
        $image = getEavProductAttr($product_id,$store_id,'image');
        $quantity = getEavProductAttr($product_id,$store_id,'quantity');
        $row['location'] = $location;
        if($image){
            $row['image']=$image_url.$image;
        }
        $row['quantity'] = $quantity;

        $conn->close();
        return array("success"=>1,"data"=>$row);
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function getEavProductAttr($product_id,$store_id,$attribute_code){
    try{
        $conn = db_connect();
        $sql = "select p_v.value from catalog_product_entity_varchar p_v,
                eav_attribute p_eav
                where p_v.attribute_id = p_eav.attribute_id
                and entity_id = $product_id
                and store_id = 0
                and attribute_code = '$attribute_code';";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select eav product detail error.Msg[$sql]");
        }
        if($res->num_rows == 0){
            $value = null;
        }else{
            $row = $res->fetch_assoc();
            $value = $row['value'];
        }
        $conn->close();
        return $value;
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function driverConfirmOrder($order_id,$username){
    try{
        $conn = db_connect();
        $driver_confirmed = getOrderInfo($order_id)['driver_confirmed'];
        if($driver_confirmed == 1){
            return array("success"=>0,"data"=>'',"error_msg"=>"This order has been confirmed");
        }
        $date = date('Y-m-d H:i:s');
        $sql = "update sales_flat_order set driver_confirmed = 1,driver_username = '$username',driver_confirm_time = '$date' where entity_id = $order_id";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Confirm order error.Msg[$sql]");
        }
        $conn->commit();
        $conn->close();
        return array("success"=>1,"data"=>'Confirm order successful');
    }catch (Exception $e){
        $conn->rollback();
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function disprocessOrder($order_id){
    try{
        $conn = db_connect();
        $driver_confirmed = getOrderInfo($order_id)['driver_confirmed'];
        if($driver_confirmed == 0){
            return array("success"=>0,"data"=>'',"error_msg"=>"This order hasn't been confirmed");
        }
        $sql = "update sales_flat_order set driver_confirmed = 0,driver_username = null,driver_confirm_time = null where entity_id = $order_id";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Disprocess order error.Msg[$sql]");
        }
        $conn->commit();
        $conn->close();
        return array("success"=>1,"data"=>'Disprocess order successful');
    }catch (Exception $e){
        $conn->rollback();
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}


function getOrderInfo($order_id){
    try{
        $conn = db_connect();
        $sql = "select * from sales_flat_order where entity_id = $order_id";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("Select order info error.Msg[$sql]");
        }

        $row = $res->fetch_assoc();
        $conn->close();
        return $row;
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function setSubItem($item_id,$sub_name,$sub_price,$sub_volume,$markup){
    try{
        $conn = db_connect();
        $sql = "update sales_flat_order_item set sub_name = '$sub_name',sub_price = '$sub_price',sub_volume = '$sub_volume',item_status= 'substitute',markup = '$markup' where item_id = $item_id";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("update sub item error.Msg[$sql]");
        }
        $conn->commit();
        $conn->close();
        return array("success"=>1,"data"=>"Success");
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function setItemStatus($item_id,$item_status){
    try{
        $conn = db_connect();
        if($item_status != 'pick_up' && $item_status != 'out_of_stock' && $item_status != 'substitute' && $item_status != ''){
            return array("success"=>0,"data"=>'',"error_msg"=>"Invalid status");
        }
        $sql = "update sales_flat_order_item set item_status = '$item_status' where item_id = $item_id";
        $res = $conn->query($sql);
        if(!$res){
            throw new Exception("update item status error.Msg[$sql]");
        }
        $conn->commit();
        $conn->close();
        return array("success"=>1,"data"=>"Success");
    }catch (Exception $e){
        $conn->close();
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }
}

function completeOrder($order_id){
    $conn = db_connect();
    $update = "update sales_flat_order_item set item_status = 'out_of_stock' where order_id = $order_id and item_status is null";
    $conn->query($update);
    $conn->commit();
    $conn->close();
    $template = getCompleteTemplate($order_id);
    try{
        $res = sendmail($template['email'],'Your groceries are on the way!',$template['html']);
//        $res = sendmail('229465154@qq.com','Your groceries are on the way!','test');
    }catch (Exception $e){
        return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
    }

    if($res){
        try{
            $conn = db_connect();
            $sql = "update sales_flat_order set order_completed = 1 where entity_id = $order_id";
            $res = $conn->query($sql);
            if(!$res){
                throw new Exception("update order status error.Msg[$sql]");
            }
            $update = "update sales_flat_order_item set item_status = 'out_of_stock' where order_id = $order_id and item_status is null";
            $conn->query($update);
            $conn->commit();
            $conn->close();
            return array("success"=>1,"data"=>"Success");
        }catch (Exception $e){
            $conn->rollback();
            $conn->close();
            return array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage());
        }
    }
}



try{
    $param = array();
    foreach($_REQUEST as $k=>$v){
        $param[addslashes($k)] = addslashes($v);
    }

    if(!$param['method']){
        throw new Exception("Method is null");
    }

    if($param['method'] == 'getOrderList'){
        echo json_encode(getOrderList($param['status'],'',$param['date']));
    }else if($param['method'] == 'getOrderDetail'){
        echo json_encode(getOrderDetail($param['increment_id']));
    }else if($param['method'] == 'driverConfirmOrder'){
        echo json_encode(driverConfirmOrder($param['order_id'],$param['username']));
    }else if($param['method'] == 'disprocessOrder'){
        echo json_encode(disprocessOrder($param['order_id']));
    }else if($param['method'] == 'getItemDetail'){
        echo json_encode(getItemDetail($param['item_id']));
    }else if($param['method'] == 'setSubItem'){
        echo json_encode(setSubItem($param['item_id'],$param['sub_name'],$param['sub_price'],$param['sub_volume'],$param['markup']));
    }else if($param['method'] == 'setItemStatus'){
        echo json_encode(setItemStatus($param['item_id'],$param['item_status']));
    }else if($param['method'] == 'completeOrder'){
        echo json_encode(completeOrder($param['order_id']));
//        print_r(completeOrder($param['order_id'])) ;
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}

?>