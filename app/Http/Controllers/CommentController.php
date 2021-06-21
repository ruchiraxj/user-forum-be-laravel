<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use ErrorException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    /**
     * Display the specified resources related to the given ID
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //Validate user input
        $inputs = ['id' => $id];
        $validator = Validator::make($inputs, ['id' => 'required|integer|exists:posts,id']);

        //Handle validation failed scenario
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            //Fetch comments by id
            $comments = Comment::where('post_id', $id)->with('user')->orderByDesc('id')->get();
            if (!$comments) {
                return response([], 404);
            }
            return response($comments);

        } catch (\Throwable $th) {
            throw new ErrorException('System failed to fetch requested data');
        }
    }


        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //ID to identify the method
        $action = "create-comment";

        $fields = ['comment' => $request->input('comment'), 'post' => $request->input('post')];

        //log initial request
        Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'start', 'data' => $fields]);

        $validator = Validator::make($fields, [
            'comment' => 'required|string|min:3|max:1000',
            'post' => 'required|integer|exists:posts,id'
        ]);

        //Handle validation failed scenario
        if ($validator->fails()) {
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'validation-failed', ['data' => $validator->errors()]]);
            throw new ValidationException($validator);
        }

        try {

            $comment = Comment::create([
                'comment' => $fields['comment'],
                'post_id' => $fields['post'],
                'user_id' => $request->user()->id
            ]);

            if (isset($comment['id'])) {
                Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'success', 'data' => ['id' => $comment['id']]]);
            } else {
                Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'fail', 'data' => []]);
            }
            return response($comment);

        } catch (\Throwable $th) {
            //handle exception
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => [$th->getMessage()]]);
            throw new ErrorException('System failed to create new record');
        }
    }
}
