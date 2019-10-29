<?php

namespace Tests;

use DucCnzj\EsBuilder\Searchable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Searchable;

    public function esIndex () {
        return 'orders';
    }
}
