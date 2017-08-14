<?php
defined('ROOT') OR exit;

class CardProcessor {
  public function __construct() {
    if (defined('CONFIG') &&
    array_key_exists('stripe', CONFIG) &&
    array_key_exists('api_key', CONFIG['stripe'])) {
      \Stripe\Stripe::setApiKey(CONFIG['stripe']['api_key']);
    }
  }
}

?>