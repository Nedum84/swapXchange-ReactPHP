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
    public $chat_status;
}
?>