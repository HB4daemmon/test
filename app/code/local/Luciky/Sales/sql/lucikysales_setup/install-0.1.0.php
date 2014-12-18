<?php
/**
*Magento Order升级脚本
*/

$installer = $this;
$installer->startSetup();
//sales_quote_store
//sales_quote_item
$quoteitemTable = $installer->getTable('sales/quote_item');
//sales_order_item
$orderitemTable = $installer->getTable('sales/order_item');
//sales_order
$orderTable = $installer->getTable('sales/order');

$ordergridTable  = $installer -> getTable('sales/order_grid');
/**
 * Create table 'sales/order_address'
 */


$installer->getConnection()
   ->addColumn($ordergridTable,'storegroup_id',array(
       'type'  => Varien_Db_Ddl_Table::TYPE_INTEGER,
       'comment' => 'Storegroup Id'
       ));


 $table = $installer->getConnection()
    ->newTable($installer->getTable('sales/quote_storegroup'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('quote_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Quote Id')
    ->addColumn('storegroup_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Store Id')
    ->addColumn('storegroup_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Store Name')
  ->addColumn('deliver_starttime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'deliver time from')
    ->addColumn('deliver_endtime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'deliver time to ')
    ->addIndex($installer->getIdxName('sales/quote_storegroup', array('quote_id')),
        array('quote_id'))
    ->setComment('Sales Flat quote Storegroup');
 $installer->getConnection()->createTable($table);

 $table = $installer->getConnection()
    ->newTable($installer->getTable('sales/order_storegroup'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Quote Id')
    ->addColumn('storegroup_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Store Id')
    ->addColumn('storegroup_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Store Name')
  ->addColumn('deliver_starttime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'deliver time from')
    ->addColumn('deliver_endtime', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'deliver time to ')
    ->addIndex($installer->getIdxName('sales/order_storegroup', array('quote_id')),
        array('order_id'))
    ->setComment('Sales Flat Order Storegroup');
 $installer->getConnection()->createTable($table);


 $installer->getConnection()
    ->addColumn($quoteitemTable,'sales_quote_storegroup_id',array(
        'type'  => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Sales Quote Storegroup Id'
        ));

 $installer->getConnection()
    ->addColumn($orderitemTable,'sales_order_storegroup_id',array(
        'type'  => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'comment' => 'Sales order Storegroup Id'
        ));
 $installer->getConnection()
     ->addColumn($orderitemTable,'real_order_id',array(
     		'type'  => Varien_Db_Ddl_Table::TYPE_INTEGER,
     		'comment' => 'real order Id'
     ));


 $installer->getConnection()
     ->addColumn($quoteitemTable, 'substitute', array(
             'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
             'length'    => 2,
             'comment'   => 'substitute---0:NO,1:YES,2:Call Me To Confirm'
     ));
 $installer->getConnection()
     ->addColumn($quoteitemTable , 'customer_message', array(
             'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
             'length'    => 255,
             'comment'   => 'customer request note'
     ));

 $installer->getConnection()
     ->addColumn($orderitemTable, 'substitute', array(
             'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
             'length'    => 2,
             'comment'   => 'substitute---0:NO,1:YES,2:Call Me To Confirm'
     ));
 $installer->getConnection()
     ->addColumn($orderitemTable , 'customer_message', array(
             'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
             'length'    => 255,
             'comment'   => 'customer request note'
     ));

 $installer->getConnection()
     ->addColumn($orderTable,'parent_order_id',array(
         'type'=>Varien_Db_Ddl_Table::TYPE_INTEGER,
         'nullable'  => true,
         'comment' => 'Parent Order Id'
        ));
     $installer->getConnection()
         ->addColumn($orderTable,'storegroup_id',array(
             'type'=>Varien_Db_Ddl_Table::TYPE_INTEGER,
             'nullable'  => true,
             'comment' => 'storegroup Id'
            ));
         $installer->getConnection()
         ->addColumn($orderTable,'sales_flat_storegroup_id',array(
         		'type'=>Varien_Db_Ddl_Table::TYPE_INTEGER,
         		'nullable'  => true,
         		'comment' => 'Sales Storegorup table Id'
         ));
$installer->endSetup();