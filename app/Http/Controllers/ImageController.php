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
     * @param  \App\Services\ImageHandler  $image_handler
     * @return \Illuminate\Http\Response
     */
    public function process(ProcessImageRequest $request, ImageHandler $image_handler)
    {
        $image_handler->processImage($request->image);
        $mines = $image_handler->filterDarkestCells($request->min_level);
        return response()->json(['mines' => $mines], 200);
    }
}
