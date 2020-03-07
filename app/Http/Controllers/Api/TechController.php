<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Tech;

class TechController extends Controller
{
    public function getAll(){
        return Tech::all();
    }
}
