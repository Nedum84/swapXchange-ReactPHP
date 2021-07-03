<?php
namespace App\Models;

class ProductChatsModel{
  
    public $table_name = "product_chats";
  
    // object properties
    public $id;
    public $product_id;
    public $offer_product_id;
    public $sender_id;
    public $receiver_id;
    public $sender_closed_deal = 0;
    public $receiver_closed_deal = 0;
    public $chat_status;
}
?>