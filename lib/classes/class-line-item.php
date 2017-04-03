<?php
/**
 * Class LineItem
 */

namespace UsabilityDynamics\WPI {

  if (!class_exists('\UsabilityDynamics\WPI\LineItem')) {

    /**
     * Class LineItem
     * @package UsabilityDynamics\WPI
     */
    class LineItem {

      private $item;

      /**
       * @param $data
       */
      public function __construct($data)
      {
        $this->item = $data;
      }

      /**
       * @return string|void
       */
      public function get_name()
      {
        return !empty($this->item['name']) ? $this->item['name'] : __('Unnamed', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return string
       */
      public function get_description()
      {
        $_description = trim(strip_tags($this->item['description']));
        return !empty($_description) ? nl2br($_description) : false;
      }

      /**
       * @return string
       */
      public function get_quantity()
      {
        return !empty($this->item['quantity']) ? $this->item['quantity'] : '-';
      }

      /**
       * @return int
       */
      public function get_price($currency_sign = '$')
      {
        return sprintf("$currency_sign%s", wp_invoice_currency_format(!empty($this->item['price']) ? $this->item['price'] : 0));
      }

      /**
       * @param string $currency_sign
       * @param bool $with_tax
       * @return string
       */
      public function get_amount($currency_sign = '$', $with_tax = false)
      {
        return $with_tax
            ? sprintf("$currency_sign%s", wp_invoice_currency_format(!empty($this->item['line_total_after_tax']) ? $this->item['line_total_after_tax'] : 0))
            : sprintf("$currency_sign%s", wp_invoice_currency_format(!empty($this->item['line_total_before_tax']) ? $this->item['line_total_before_tax'] : 0));
      }

      /**
       * @param string $currency_sign
       * @return string
       */
      public function get_tax($currency_sign = '$')
      {
        return !empty($this->item['line_total_tax']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->item['line_total_tax'])) : '-';
      }
    }
  }
}