<?php

namespace App\Http\Controllers;

use App\Models\Post;
use ErrorException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function viewUnapprovedPosts(Request $request)
    {
        //ID to identify the method
        $action = "view-pending-posts";

        if (!Gate::allows($action)) {
            return response(['error' => 'Permission Denied'], 403);
        }

        try {
            //Fetch pending posts
            $posts = Post::where("status", 0)->with('produc')->with('user')->get();

            //If no pending post available, return not found
            if (count($posts) < 1) {
                return response([], 404);
            }
            return response($posts);
        } catch (\Throwable $th) {
            //handle exception
            Log::channel('admin')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => [$th->getMessage()]]);
            throw new ErrorException('System failed to fetch requested data');
        }
    }

    public function updatePostStatus(Request $request, $id)
    {
        //ID to identify the method
        $action = "approve-posts";

        //log initial request
        Log::channel('admin')->info($request->bearerToken(), ['action' => $action, 'status' => 'start', 'data' => ['user' => $request->user()->id, 'post' => $id, 'status' => $request->input('status')]]);

        //check permission whether the logged in user has permission to access the file
        if (!Gate::allows($action)) {
            return response(['error' => 'Permission Denied'], 403);
        }

        //input validation
        $inputs = ['id' => $id, 'status' => $request->input('status')];
        $validator = Validator::make($inputs, ['id' => 'required|integer|exists:posts,id', 'status' => 'required|integer|between:1,2']);

        //handle validation failed scenario
        if ($validator->fails()) {
            Log::channel('admin')->error($request->bearerToken(), ['action' => $action, 'status' => 'validation-failed', ['data' => $validator->errors()]]);
            throw new ValidationException($validator);
        }

        try {

            //Update the post status in DB
            $post = Post::find($id);
            $post->status = $request->input('status');
            $post->approved_user_id = $request->user()->id;
            $stat = $post->save();

            //handle DB update response
            if ($stat === true) {
                Log::channel('admin')->info($request->bearerToken(), ['action' => $action, 'status' => 'success']);
            } else {
                Log::channel('admin')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed']);
            }

            return response(['status' => $stat]);
        } catch (\Throwable $th) {
            //handle exception
            Log::channel('admin')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => [$th->getMessage()]]);
            throw new ErrorException('System failed to update the record');
        }
    }
}
