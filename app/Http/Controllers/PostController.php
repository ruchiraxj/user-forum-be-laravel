<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Post::all()->where('status', 1);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string|min:10|max:255',
            'description' => 'required|string|min:100|max:1000',
            'product' => 'required|integer|exists:products,id'
        ]); 

        $status = 0;
        $approved_by = NULL;

        if($request->user()->isAdministrator()){
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

        return Response($post);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);
        return Response($post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!Gate::allows('delete-post', $post)) {
            return Response('Permission Denied', 403);
        }
        
        $stat = false;
        if($post){
            $stat = $post->delete();
        }

        return Response($stat);
    }
}
