<?php

namespace UsabilityDynamics\WPI {

  if (!class_exists('UsabilityDynamics\WPI\PDF_Template')) {

    class PDF_Template
    {

      private $invoice;
      private $item_index = 0;
      private $charge_index = 0;

      /**
       * @param $data
       */
      public function __construct($data)
      {
        $this->invoice = apply_filters('wpi::pdf::invoice_parts', $data);
      }

      /**
       * @return string|void
       */
      public function get_type()
      {
        return !empty($this->invoice['type']) ? $this->invoice['type'] : __('Undefined', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return string|void
       */
      public function get_ID()
      {
        return !empty($this->invoice['custom_id']) ? $this->invoice['custom_id'] : (!empty($this->invoice['invoice_id']) ? $this->invoice['invoice_id'] : __('Undefined', ud_get_wp_invoice_pdf()->domain));
      }

      /**
       * @return mixed
       */
      public function get_currency_sign()
      {
        global $wpi_settings;
        return !empty($wpi_settings['currency']['symbol'][$this->invoice['default_currency_code']]) ? $wpi_settings['currency']['symbol'][$this->invoice['default_currency_code']] : $wpi_settings['currency']['symbol'][$wpi_settings['currency']['default_currency_code']];
      }

      /**
       * @return bool|string|void
       */
      public function get_issue_date()
      {
        $time = strtotime($this->invoice['post_date']);
        $date = sprintf("%s %s, %s", __(date('F', $time), ud_get_wp_invoice_pdf()->domain), date('d', $time), date('Y', $time));
        return !empty($this->invoice['post_date']) ? $date : __('Undefined', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return bool
       */
      public function has_due_date()
      {
        return !empty($this->invoice['due_date_year']) && !empty($this->invoice['due_date_month']) && !empty($this->invoice['due_date_day']);
      }

      /**
       * @return bool|string|void
       */
      public function get_due_date()
      {
        $time = strtotime(sprintf("%s.%s.%s", $this->invoice['due_date_day'], $this->invoice['due_date_month'], $this->invoice['due_date_year']));
        $date = sprintf("%s %s, %s", __(date('F', $time), ud_get_wp_invoice_pdf()->domain), date('d', $time), date('Y', $time));
        return !empty($date) ? $date : __('Undefined', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return bool
       */
      public function display_address() {
        global $wpi_settings;
        return !empty( $wpi_settings['pdf']['display_name'] ) && $wpi_settings['pdf']['display_name'] == 'true';
      }

      /**
       * @return string|void
       */
      public function get_recepient_name()
      {
        return !empty($this->invoice['user_data']['display_name']) ? $this->invoice['user_data']['display_name'] : __('Name is Undefined', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return string|void
       */
      public function get_recipient_phone() {
        return !empty($this->invoice['user_data']['phonenumber']) ? $this->invoice['user_data']['phonenumber'] : __('Phone is Undefined', ud_get_wp_invoice_pdf()->domain);
      }

      /**
       * @return string|void
       */
      public function get_company_address()
      {
        $address_parts = array();

        $address_parts[] = !empty($this->invoice['user_data']['company_name']) ? $this->invoice['user_data']['company_name'] : false;
        $address_parts[] = !empty($this->invoice['user_data']['streetaddress']) ? $this->invoice['user_data']['streetaddress'] : false;
        $address_parts[] = !empty($this->invoice['user_data']['city']) ? $this->invoice['user_data']['city'] : false;
        $address_parts[] = !empty($this->invoice['user_data']['country']) ? $this->invoice['user_data']['country'] : false;
        $address_parts[] = !empty($this->invoice['user_data']['state']) ? $this->invoice['user_data']['state'] : false;
        $address_parts[] = !empty($this->invoice['user_data']['zip']) ? $this->invoice['user_data']['zip'] : false;

        $address_parts = array_filter($address_parts);

        return !empty($address_parts) && is_array($address_parts) ? implode(', ', $address_parts) : '';
      }

      /**
       * @return bool
       */
      public function has_logo()
      {
        global $wpi_settings;
        if (empty($wpi_settings['pdf']['display_logo']) || $wpi_settings['pdf']['display_logo'] != 'true') return false;
        return !empty($wpi_settings['pdf']['logo_path']);
      }

      /**
       * @return string
       */
      public function get_logo_url()
      {
        if (!$this->has_logo()) return '';
        global $wpi_settings;
        return $wpi_settings['pdf']['logo_path'];
      }

      /**
       * @return mixed|void
       */
      public function get_business_name()
      {
        global $wpi_settings;
        $invoice = new \stdClass();
        $invoice->data = $this->invoice;
        return apply_filters('wpi_business_name', $wpi_settings['business_name'], $invoice);
      }

      /**
       * @return mixed|void
       */
      public function get_business_address()
      {
        global $wpi_settings;
        $invoice = new \stdClass();
        $invoice->data = $this->invoice;
        return nl2br(strip_tags(apply_filters('wpi_business_address', $wpi_settings['business_address'], $invoice)));
      }

      /**
       * @return bool
       */
      public function has_business_address()
      {
        global $wpi_settings;
        $invoice = new \stdClass();
        $invoice->data = $this->invoice;
        $address = apply_filters('wpi_business_address', $wpi_settings['business_address'], $invoice);
        return !empty($address);
      }

      /**
       * @return mixed|void
       */
      public function get_business_phone()
      {
        global $wpi_settings;
        $invoice = new \stdClass();
        $invoice->data = $this->invoice;
        return apply_filters('wpi_business_phone', $wpi_settings['business_phone'], $invoice );
      }

      /**
       * @return bool
       */
      public function has_business_phone()
      {
        global $wpi_settings;
        $invoice = new \stdClass();
        $invoice->data = $this->invoice;
        $phone = trim(apply_filters('wpi_business_phone', $wpi_settings['business_phone'], $invoice));
        return !empty($phone);
      }

      /**
       * @return mixed|void
       */
      public function get_business_email()
      {
        global $wpi_settings;
        return $wpi_settings['email_address'];
      }

      /**
       * @return bool
       */
      public function has_business_email()
      {
        global $wpi_settings;
        return !empty($wpi_settings['email_address']);
      }

      /**
       * @return mixed
       */
      public function get_title()
      {
        return $this->invoice['post_title'];
      }

      /**
       * @return bool
       */
      public function has_description()
      {
        global $wpi_settings;
        if ( empty( $wpi_settings['pdf']['display_description'] ) || $wpi_settings['pdf']['display_description'] != 'true' ) return false;
        return !empty($this->invoice['post_content']);
      }

      /**
       * @return mixed
       */
      public function get_description()
      {
        return $this->invoice['post_content'];
      }

      /**
       * @return bool
       */
      public function has_items()
      {
        return !empty($this->invoice['itemized_list']) && is_array($this->invoice['itemized_list']);
      }

      /**
       * @return bool
       */
      public function get_item()
      {
        $this->invoice['itemized_list'] = array_values($this->invoice['itemized_list']);
        if (!empty($this->invoice['itemized_list'][$this->item_index]) && is_array($this->invoice['itemized_list'][$this->item_index])) {
          return new PDF_Invoice_Item($this->invoice['itemized_list'][$this->item_index++]);
        }
        return false;
      }

      /**
       * @param string $currency_sign
       * @return bool|string
       */
      public function get_total_tax($currency_sign = '$')
      {
        return !empty($this->invoice['total_tax']) && $this->invoice['total_tax'] > 0 ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->invoice['total_tax'])) : false;
      }

      /**
       *
       */
      public function get_amount_due($currency_sign = '$')
      {
        if ( $this->invoice['post_status'] == 'refund' ) {
          return !empty($this->invoice['net']) ? sprintf("<s>$currency_sign%s</s>", wp_invoice_currency_format($this->invoice['net'])) : '-';
        }
        return !empty($this->invoice['net']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->invoice['net'])) : '-';
      }

      /**
       * @return bool
       */
      public function is_refunded() {
        return $this->invoice['post_status'] == 'refund';
      }

      /**
       * @param string $currency_sign
       * @return string
       */
      public function get_total($currency_sign = '$')
      {
        return !empty($this->invoice['subtotal']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->invoice['subtotal'])) : 0;
      }

      /**
       * @param string $currency_sign
       */
      public function get_discount($currency_sign = '$')
      {
        return !empty($this->invoice['total_discount']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->invoice['total_discount'])) : 0;
      }

      /**
       * @param string $currency_sign
       * @return int|string
       */
      public function get_total_payments($currency_sign = '$')
      {
        return !empty($this->invoice['total_payments']) ? sprintf("$currency_sign%s", wp_invoice_currency_format($this->invoice['total_payments'])) : 0;
      }

      /**
       * @return bool
       */
      public function has_notes()
      {
        global $wpi_settings;
        if (empty($wpi_settings['pdf']['display_notes']) || $wpi_settings['pdf']['display_notes'] != 'true') return false;
        $notes = !empty($wpi_settings['pdf']['notes']) ? trim($wpi_settings['pdf']['notes']) : '';
        return !empty($notes);
      }

      /**
       * @return string
       */
      public function get_notes()
      {
        global $wpi_settings;
        return !empty($wpi_settings['pdf']['notes']) ? trim( nl2br ( $wpi_settings['pdf']['notes'] ) ) : '';
      }

      /**
       * @return bool
       */
      public function has_terms()
      {
        global $wpi_settings;
        if (empty($wpi_settings['pdf']['display_terms_n_conditions']) || $wpi_settings['pdf']['display_terms_n_conditions'] != 'true') return false;
        $notes = !empty($wpi_settings['pdf']['terms_n_conditions']) ? trim($wpi_settings['pdf']['terms_n_conditions']) : '';
        return !empty($notes);
      }

      /**
       * @return string
       */
      public function get_terms()
      {
        global $wpi_settings;
        return !empty($wpi_settings['pdf']['terms_n_conditions']) ? trim( nl2br ( $wpi_settings['pdf']['terms_n_conditions'] ) ) : '';
      }

      /**
       * @return bool
       */
      public function has_charges() {
        return !empty($this->invoice['itemized_charges']) && is_array($this->invoice['itemized_charges']);
      }

      /**
       * @return bool|PDF_Invoice_Item
       */
      public function get_charge()
      {
        $this->invoice['itemized_charges'] = array_values( $this->invoice['itemized_charges'] );
        if (!empty($this->invoice['itemized_charges'][$this->charge_index]) && is_array($this->invoice['itemized_charges'][$this->charge_index])) {
          return new PDF_Invoice_Charge($this->invoice['itemized_charges'][$this->charge_index++]);
        }
        return false;
      }

      /**
       *
       */
      public function get_adjustments( $currency_sign = '$' ) {
        if ( !isset($this->invoice['adjustments']) ) $this->invoice['adjustments'] = 0;
        $adjustments = (float)$this->invoice['adjustments'] + (float)$this->invoice['total_payments'];
        return !empty($adjustments) ? sprintf("$currency_sign%s", wp_invoice_currency_format($adjustments)) : 0;
      }
    }
  }

  /**
   *
   */
  if (!class_exists('UsabilityDynamics\WPI\PDF_Invoice_Charge')) {

    /**
     * Class PDF_Invoice_Charge
     * @package UsabilityDynamics\WPI
     */
    class PDF_Invoice_Charge
    {

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
       * @param string $currency_sign
       * @param bool $with_tax
       * @return string
       */
      public function get_amount($currency_sign = '$')
      {
        return sprintf("$currency_sign%s", wp_invoice_currency_format(!empty($this->item['after_tax']) ? $this->item['after_tax'] : 0));
      }
    }
  }

  /**
   *
   */
  if (!class_exists('UsabilityDynamics\WPI\PDF_Invoice_Item')) {

    /**
     * Class PDF_Invoice_Item
     * @package UsabilityDynamics\WPI
     */
    class PDF_Invoice_Item
    {

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
        return !empty($_description) ? $_description : false;
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