<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function getAttribute(): Collection|array
    {
        return Attribute::get();
    }

}
