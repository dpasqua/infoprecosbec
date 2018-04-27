<?php
namespace App\Http\Controllers;

use App\Author;
use Illuminate\Http\Request;

class BecprecosController extends Controller
{
    public function index()
    {
        $json = [ 'teste' => 'douglas' ];        
        return response()->json($json);
    }

    /** 
     * autocomplete prefeitura
     */
    public function autoCompletePrefeituras()
    {
        $data = [
            'PREF A',
            'PREF B',
            'PREF C',
            'PREF D',
            'PREF E',
            'PREF F',
            'PREF G',
            'PREF H',
        ];
        return response()->json($data);
    }
}
