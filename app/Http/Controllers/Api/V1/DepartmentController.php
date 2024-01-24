<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\District;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function getDepartments(): Collection|array
    {
        return Department::with('districts')->get();
    }

    public function getDistricts()
    {
        return District::where('department_id', 3)->get();
    }
}
