<?php

namespace App\Http\Controllers\Pub\V1;

use App\Http\Controllers\Pub\V1\Logic\PublicLogic;
use Illuminate\Http\Request;

class PublicController extends BaseController
{
    private $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = new PublicLogic();
    }

}
