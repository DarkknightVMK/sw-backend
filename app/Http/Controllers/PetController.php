<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\pets;

class PetController extends Controller
{
    public function index()
    {
        return pets::all();
    }
}
