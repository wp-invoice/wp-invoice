<?php

namespace UsabilityDynamics\WPI {

  if (!class_exists('UsabilityDynamics\WPI\LineCharge')) {

    /**
     * Class LineCharge
     * @package UsabilityDynamics\WPI
     */
    class LineCharge {

      /**
       * @var
       */
      private $item;

      /**
       * @param $data
       */
      public function __construct($data) {
        $this->item = $data;
      }

      /**
       * @return string|void
       */
      public function get_name() {
        return !empty($this->item['name']) ? $this->item['name'] : __('Unnamed', ud_get_wp_invoice()->domain);
      }

      /**
       * @param string $currency_sign
       * @param bool $with_tax
       * @return string
       */
      public function get_amount($currency_sign = '$') {
        return sprintf("$currency_sign%s", wp_invoice_currency_format(!empty($this->item['after_tax']) ? $this->item['after_tax'] : 0));
      }
    }
  }
}