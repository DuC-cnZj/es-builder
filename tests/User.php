<?php

namespace DucCnzj\EsBuilder\Tests;

use DucCnzj\EsBuilder\Searchable;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use Searchable;
}
