Mijireh
=======

Before connecting to the Mijireh server via the API, the Mijireh access key must be set. In addition to setting the access key, the Mijireh class may be used to slurp a page onto the secure Mijireh servers, and to retrieve information about the store associated with the access key.

* __access\_key__

  Public static class attribute holding the api access key for a Mijireh store.
  
        Mijireh::$access_key = <your_mijireh_store_access_key>;
  
Functions
---------

* __slurp($url)__

  Slurp the given url onto the secure Mijireh servers. The slurp() function returns a job id that can be used for monitoring the progress of the page slurp.

        Mijireh::$access_key = <your_mijireh_store_access_key>;
        $job_id = Mijireh::slurp('http://mysite.com/store/mijireh_checkout');
      
* __get\_store\_info()__

  Returns an associative array of information about the Mijireh store associated with the access key.
  
        Mijireh::$access_key = <your_mijireh_store_access_key>;
        $store_data = Mijireh::get_store_info();

Mijireh\_Order
===============

The Mijireh_Order contains the information passed to Mijireh Checkout where the buyer will place their secure order. Mijireh\_Order contains the following attributes:

* __order\_number (read only)__

  The order number assigned to the order after a successful purchase is completed.
  
* __mode (read only)__
  
  The mode under which the order was placed (live vs test)
  
* __status (read only)__

  The current status of the order (pending, complete, etc)
  
* __order\_date (read only)__

  The date the order was completed. The date is based on the timezone specified in your mijireh account settings.
  
* __ip\_address (read only)___

  The ip address from which the order was placed. The ip address is assigned to the order when the order is completed.
  
* __checkout\_url__

  The url to the secure mijireh server where the billing information is collected and the order is completed.
  
* __total (required)__

  The total amount charged to the customer. The total must be equal to the sum of the cost of all the items, plus shipping, plus tax, less the discount. If the total is not correct, the order will not be created.
  
* __return\_url__ 

  The URL where the customer is redirected after successfully completing an order. 
  
* __items (required)__ 

  An array of Mijireh\_Item objects describing the products being purchased.
  
* __email__
  
  The customer's email address used to pre-fill the secure order form.
  
* __first\_name__

  The customer's first name used to pre-fill the secure order form.
  
* __last\_name__

  The customer's last name used to pre-fill the secure order form.
  
* __meta\_data__

  An associative array of custom information associated with the order. The meta\_data will be returned when an order is retrieved via the API and may also be used to customize the messaging on the secure order form.
  
  To customize the messaging on the secure order form, simply include the name of one (or more) meta keys anywhere on the HTML of your slurped page in double brackets like this:
  
  Hello {{first-name}}, this is a secure store.
  
  Be sure to add first-name as one of the key value pairs in the meta\_data array.
  
* __tax__ 

  The amount of sales tax to collect for the order
  
* __shipping__ 

  The amount to charge the customer for shipping
  
* __discount__

  The amount by which you want to discount the total cost of the order, such as if you are applying a coupon to the order. The discount should be a numeric value greater than or equal to zero. Do not include a currency symbol or commas in the discount amount.
  
* __shipping\_address__

  The shipping address of the customer, used to pre-fill the order form's billing address. The shipping address will remain attached to the order and can be retrieved as part of the order via the mijireh API.
  
Functions
----------

* __contruct($order\_number)__

  Instantiate a new Mijireh\_Order. The order number parameter is optional. If an order number is provided, the order will be initialized with the order information associated with the given order number. 
  
* __load($order\_number)__

  Load an order with the information associated with the given order number.
  
* __create()__

  Once an order is populated with the appropriate data, call create() to create the order on the secure mijireh platform. After calling create() the order will have a checkout\_url attribute containing the url to the secure mijireh checkout page. Redirect the customer to the checkout url for them to complete the order.
  
* __add\_item($name, $price, $quantity=1, $sku='')__

  Add an item to the order. The name and the price are required.
  
* __add\_item(Mijireh\_Item $item)__

  Add an item to the order using a Mijireh\_Item.
  
* __add\_meta\_data($key, $value)__

  Add a key/value pair to the meta_data associated with the order. Meta data can be used to customize the messaging on the secure order form or may simply be used for internal purposes in your integration with Mijireh.
  
* __item\_count()__

  Returns the number of items in the order
  
* __get\_items()__

  Returns an array of Mijireh_Item objects representing the items in the order
  
* __clear\_items()__

  Remove all of the items from the order. Items may only be removed before the order is created.
  
* __clear\_meta\_data()__

  Remove all of the meta data associated with the order.
  
* __validate()__

  Returns true if all of the required information is present and valid, otherwise returns false. 
  
* __set\_shipping\_address(Mijireh\_Address $address)__

  Set the customer's shipping address on the order.
  
* __get\_shipping\_address()__

  Returns an instance of Mijireh_Address describing the customer's shipping address.

* __set\_billing\_address(Mijireh\_Address $address)__

  Set the customer's billing address on the order.

* __get\_billing\_address()__

    Returns an instance of Mijireh_Address describing the customer's shipping address.
    
          // Set the Mijireh access key
          Mijireh::$access_key = <your_mijireh_store_access_key>;
          
          // create a basic order
          $order = new Mijireh_Order();
          $order->add_item('Example Product', 9.99, 1, 'example_sku');
          $order->return_url('http://www.mysite.com/store/receipt');
          $order->shipping = 5.00;
          $order->total = 14.99;
          $order->create();

          // create an order then redirect to the secure checkout page
          $item = new Mijireh_Item();
          $item->name = 'Example Product';
          $item->price = 10.00;
          $item->quantity = 2;

          $address = new Mijireh_Address();
          $address->first_name = 'Test';
          $address->last_name = 'Person';
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
          $order->return_url('http://www.mysite.com/store/receipt');
          $order->shipping = 5.00;
          $order->tax = 1.00;
          $order->discount = 3.00;
          $order->total = 23.00; // sum of items + shipping + tax - discount
          $order->create();
          header('Location: ' . $order->checkout_url);
          exit();

          // load an order
          $order = new Mijireh_Order('<order_number>');

Mijireh_Address
===============

A Mijireh\_Order may optionally contain a Mijireh\_Address describing the shipping address for the order. The Mijireh_Address contains the following attributes:

* __first\_name__

  (optional) The first name of the person associated with this address
  
* __last\_name__

  (optional) The last name of the person associated with this address

* __street (required)__
  
  The street address
  
* __city (required)__
  
  The full city name
  
* __state_province (required)__
  
  The abbreviation of the state or province name
  
* __zip_code (required)__
  
  The numeric zip code for the address
  
* __country (required)__

  The country abbreviation for the address such as US, UK, AU, etc.
  
* __company__

  (optional) The company name associated with this address
  
* __apt\_suite__ 

  (optional) The apartment or suite number
  
* __phone__

  (optional) The phone number
  
Functions
----------

* __validate()__

  Returns true if all of the required fields are present, otherwise false is returned.
  
Mijireh_Item
=============

A Mijireh\_Order must contain at list one Mijireh\_Item describing the products being purchased in the order. The Mijireh\_Item contains the following attributes:

* __name (required)__

  The name of the product being purchased
  
* __price (required)__

  The price of the item being purchased. The price must be a number greater than or equal to zero. The price should not contain currency symbols, or commas.
  
* __quantity__

  The quantity of the item being purchased. The quantity must be a number greater than or equal to 1. If the quantity is not specified, the default value is 1.
  
* __sku__

  The stock keeping unit for the item. This is an optional identifier that may be assigned to an item.
  
  
* __total__

  Total is a read only attribute that returns the calculated value of price multiplied by quantity.
  
Functions
---------

* __get_data()__

  Returns an associative array where the keys are the names of the attributes and the values are the associated values for those attributes. The returned array includes the calculated total.
  
* __validate()__

  Returns true if all of the required attributes are present and the quantity is a number greater than or equal to 1, otherwise, false is returned.
