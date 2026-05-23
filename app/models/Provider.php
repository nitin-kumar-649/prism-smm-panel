<?php

namespace App\Models;

use App\Core\Model;

class Provider extends Model
{
    protected string $table = 'providers';

    public function getActive(): array
    {
        return $this->where("status = 'active'");
    }
}
