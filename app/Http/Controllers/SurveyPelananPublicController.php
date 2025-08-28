<?php

namespace App\Http\Controllers;

use App\Models\SurveyPelananPublic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SurveyPelananPublicController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'hp' => 'required|string',
            'rating' => 'required|integer|min:1|max:3',
        ]);

        $survey = new SurveyPelananPublic();
        $survey->name = $request->name;
        $survey->hp = $request->hp;
        $survey->instansi = $request->instansi;
        $survey->email = $request->email;
        $survey->age = $request->age;
        $survey->rating = $request->rating;
        $survey->comment = $request->comment;
        $survey->save();

        return response()->json(['message' => 'Terimakasih atas respon Anda 😊'], 201);
    }
}
