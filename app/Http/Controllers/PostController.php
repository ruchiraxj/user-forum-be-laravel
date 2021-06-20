<?php

namespace App\Http\Controllers;

use App\Models\Post;
use ErrorException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $posts = Post::where("status", 1)->orderByDesc('id')->with('product')->with('user')->get();
            if (count($posts) < 1) {
                return response([], 404);
            }
            return response($posts);
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
        $action = "create-post";

        $fields = ['title' => $request->input('title'), 'description' => $request->input('description'), 'product' => $request->input('product')];

        //log initial request
        Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'start', 'data' => ['user' => $request->user()->id, $fields]]);

        $validator = Validator::make($fields, [
            'title' => 'required|string|min:10|max:255',
            'description' => 'required|string|min:10|max:1000',
            'product' => 'required|integer|exists:products,id'
        ]);

        //Handle validation failed scenario
        if ($validator->fails()) {
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'validation-failed', ['data' => $validator->errors()]]);
            throw new ValidationException($validator);
        }

        try {

            $status = 0;
            $approved_by = NULL;

            if ($request->user()->isAdministrator()) {
                $status = 1;
                $approved_by = $request->user()->id;
            }

            $post = Post::create([
                'title' => $fields['title'],
                'description' => $fields['description'],
                'product_id' => $fields['product'],
                'status' => $status,
                'user_id' => $request->user()->id,
                'approved_user_id' => $approved_by
            ]);

            if (isset($post['id'])) {
                Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'success', 'data' => ['id' => $post['id']]]);
            } else {
                Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'fail', 'data' => []]);
            }
            return response($post);
        } catch (\Throwable $th) {
            //handle exception
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => [$th->getMessage()]]);
            throw new ErrorException('System failed to create new record');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //Validate user input
        $inputs = ['id' => $id];
        $validator = Validator::make($inputs, ['id' => 'required|integer']);

        //Handle validation failed scenario
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            //Fetch post by id
            $post = Post::where('id', $id)->with('product')->with('user')->first();
            if (!$post) {
                return response([], 404);
            }
            return response($post);
        } catch (\Throwable $th) {
            throw new ErrorException('System failed to fetch requested data');
        }
    }

    /**
     * Display the specified resource related to the user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function byUser(Request $request)
    {
        try {
            //Fetch posts created by the logged in user     
            $post = $request->user()->posts()->orderByDesc('id')->with('product')->with('user')->get();

            if (count($post) < 1) {
                return response([], 404);
            }
            return response($post);
        } catch (\Throwable $th) {
            throw new ErrorException('System failed to fetch requested data');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //ID to identify the method
        $action = "delete-post";

        $inputs = ['id' => $id];

        //Log request
        Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'start', 'data' => ['user' => $request->user()->id, 'post' => $inputs['id']]]);

        //Validate request
        $validator = Validator::make($inputs, ['id' => 'required|integer|exists:posts,id']);

        //handle validation failed scenario
        if ($validator->fails()) {
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'validation-failed', ['data' => $validator->errors()]]);
            throw new ValidationException($validator);
        }


        try {
            //Find the requested Post
            $post = Post::find($id);

            //check whether user has permission to delete the requested post
            if (!Gate::allows($action, $post)) {
                Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'permission-denied', 'data' => []]);

                return response(['error' => 'Permission Denied'], 403);
            }

            $stat = false;
            if ($post) {
                //Delete post if available
                $stat = $post->delete();
            }
            if ($stat == true) {
                Log::channel('audit')->info($request->bearerToken(), ['action' => $action, 'status' => 'success', 'data' => []]);
            } else {
                Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => []]);
            }

            return response(['status' => $stat]);
        } catch (\Throwable $th) {
            //handle exception
            Log::channel('audit')->error($request->bearerToken(), ['action' => $action, 'status' => 'failed', 'data' => [$th->getMessage()]]);
            throw new ErrorException('System failed to delete this record');
        }
    }
}
