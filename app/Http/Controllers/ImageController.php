<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessImageRequest;
use App\Services\ImageHandler;

class ImageController extends Controller
{
    /**
     * Process image and detect darkest cells
     * 
     * @param  \App\Http\Requests\ProcessImageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function process(ProcessImageRequest $request)
    {
        $image_handler = new ImageHandler($request->image);
        $mines = $image_handler->filterDarkestCells($request->min_level);
        return response()->json(['mines' => $mines], 200);
    }
}
