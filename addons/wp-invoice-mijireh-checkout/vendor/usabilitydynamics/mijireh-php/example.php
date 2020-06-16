<?php 

// require the mijireh php library
require 'mijireh-php';

Mijireh::$access_key = '<store_access_key>';

// get store info
$store_info = Mijireh::get_store_info();

// slurp a page
$job_id = Mijireh::slurp('http://www.mysite.com/store/mijireh-secure-checkout');

// create a basic order
$order = new Mijireh_Order();
$order->add_item('Example Product', 9.99, 1, 'example_sku');
$order->return_url = 'http://www.mysite.com/store/receipt';
$order->shipping = 5.00;
$order->total = 14.99;
$order->create();

// create an order then redirect to the secure checkout page
$item = new Mijireh_Item();
$item->name = 'Example Product';
$item->price = 10.00;
$item->quantity = 2;

$address = new Mijireh_Address();
$address->street = '1234 Test Dr.';
$address->city = 'Lanexa';
$address->state_province = 'VA';
$address->zip_code = '23089';
$address->country = 'US';
$address->phone = '888-888-8888';

$order = new Mijireh_Order();
$order->set_shipping_address($address);
$order->set_billing_address($address);
$order->add_item($item);
$order->return_url = 'http://www.mysite.com/store/receipt';
$order->shipping = 5.00;
$order->tax = 1.00;
$order->discount = 3.00;
$order->total = 23.00; // sum of items + shipping + tax - discount
$order->create();
header('Location: ' . $order->checkout_url);
exit();

// load an order
$order = new Mijireh_Order('<order_number>');