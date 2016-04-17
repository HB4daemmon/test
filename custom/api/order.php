<?php
require_once dirname(__FILE__)."/../util/connection.php";

function getOrderList($status,$increment_id,$date){
    try{
        $conn = db_connect();
        if(!$status){
            $status_sql = ' ';
        }else{
            $status_sql = " and o.status = '$status' ";
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
            $date_sql = " and unix_timestamp(date(o.created_at)) = unix_timestamp('$date') ";
        }

        $sql = "SELECT o.entity_id as order_id,o.status,o.customer_id,o.base_grand_total,o.base_shipping_amount,
                    o.base_subtotal,o.grand_total,o.shipping_amount,o.subtotal,o.total_qty_ordered,o.shipping_address_id,
                    o.increment_id,o.customer_email,o.customer_firstname,o.customer_lastname,o.created_at
                    ,c.email,concat(os.date,' ',os.time_range,':00-',os.time_range+1,':00') as delivery_window,
                    c.reg_phone,s.name as store_name
                    FROM sales_flat_order o,
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
        $image_url = 'http://www.cartgogogo.com/media/catalog/product';
        if(!$increment_id){
            throw new Exception("increment_id is null");
        }
        $sql = "select o.increment_id,oi.store_id,oi.product_id,oi.weight,oi.sku,oi.name,oi.qty_ordered,
                oi.price,oi.base_price,oi.base_original_price,oi.row_total,oi.price_incl_tax,
                oi.base_price_incl_tax,oi.row_total_incl_tax,oi.base_row_total_incl_tax,
                if(oi.substitute=1,'Y','N') as substitute,oi.customer_message
                 from sales_flat_order_item oi,sales_flat_order o
                where oi.order_id = o.entity_id
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
        $sql = "";
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

try{
    $param = $_REQUEST;

    if(!$param['method']){
        throw new Exception("Method is null");
    }

    if($param['method'] == 'getOrderList'){
        echo json_encode(getOrderList($param['status'],'',$param['date']));
    }else if($param['method'] == 'getOrderDetail'){
        echo json_encode(getOrderDetail($param['increment_id']));
    }else{
        throw new Exception("Invalid Method");
    }

}catch(Exception $e){
    echo json_encode(array("success"=>0,"data"=>'',"error_msg"=>$e->getMessage()));
    exit;
}

?>