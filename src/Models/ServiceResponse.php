<?php

declare(strict_types=1);

namespace App\Models;

final class ServiceResponse{
    public $success;
    public $data;

    public function __construct(bool $success, $data){
        $this->success = $success;
        $this->data = $data;
    }
}
