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
            'rating' => 'required|integer|min:1|max:3',
            'comment' => 'nullable|string',
        ]);

        $survey = new SurveyPelananPublic();
        $survey->rating = $request->rating;
        $survey->comment = $request->comment;
        $survey->save();

        return response()->json(['message' => 'Terimakasih atas respon Anda 😊'], 201);
    }
}
