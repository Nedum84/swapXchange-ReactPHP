<?php
namespace App\Models;

class ProductModel{
  
    private $table_name = "product";
  
    // object properties
    public $id;
    public $product_id;
    public $product_name;
    public $category;
    public $sub_category;
    public $price;
    public $product_description;
    public $product_suggestion;
    public $product_condition;
    public $product_status;
    public $user_id;
    public $user_address;
    public $user_address_city;
    public $user_address_lat;
    public $user_address_long;
}
?>