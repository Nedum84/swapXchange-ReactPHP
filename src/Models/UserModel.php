<?php
namespace App\Models;

class UserModel{
  
    private $table_name = "users";
  
    // object properties
    public $user_id;
    public $uid;
    public $name;
    public $email;
    public $mobile_number;
    public $address;
    public $address_lat;
    public $address_long;
    public $state;
    public $profile_photo;
    public $device_token;
    public $user_app_version;
    public $last_login;
    public $created_at;
  
}
?>