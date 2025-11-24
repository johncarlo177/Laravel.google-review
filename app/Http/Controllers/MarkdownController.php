<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Str;

class MarkdownController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return [
            'html' => $request->markdown ? Str::markdown(
                string: $request->markdown,
                extensions: [
                    new \League\CommonMark\Extension\Table\TableExtension,
                ]
            ) : ''
        ];
    }
}
