<?php

namespace App\Http\Controllers;

//import
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $posts = Post::orderBy('title', 'desc')->paginate();
       $title = "Hey Temisan, the richest man alive. Believe";
            
       return view('posts.index')->with('posts',$posts)->with('title', $title);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
            
            $this->validate($request, [
                'title' => 'required',
                'body' => 'required',
                'cover_image' => 'image|nullable|max:1999'
            ]);

            //Handle File Upload
            if($request->hasFile('cover_image')){
                //get filename with the extension
                $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
                //get just filename
                $filename = pathinfo( $fileNameWithExt, PATHINFO_FILENAME);
                //get just ext
                $extension = $request->file('cover_image')->getClientOriginalExtension();
                $fileNameToStore = $filename. '_' .time(). '.' .$extension;
                //upload image
                $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
            } else {
                $fileNameToStore = 'noimage.jpg';
            }

            //create POST
            $post = new Post;
            $post -> title = $request -> input('title');
            $post -> body = $request -> input('body');
            $post -> user_id =auth()->user()->id;
            $post -> cover_image = $fileNameToStore;
            $post -> save();

            return redirect('/posts')->with('success', 'Post Created');
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
        return view('posts.show')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $post = Post::find($id);

        /** check for correct user */
        if (auth()->user()->id !==$post->user_id) {
            return redirect ('/posts')->with('error', 'Unauthorized Page');
        }

        return view('posts.edit')->with('post', $post);

        

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
          
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required',
        ]);

        //Handle File Upload
        if($request->hasFile('cover_image')){
            //get filename with the extension
            $fileNameWithExt = $request->file('cover_image')->getClientOriginalName();
            //get just filename
            $filename = pathinfo( $fileNameWithExt, PATHINFO_FILENAME);
            //get just ext
            $extension = $request->file('cover_image')->getClientOriginalExtension();
            $fileNameToStore = $filename. '_' .time(). '.' .$extension;
            //upload image
            $path = $request->file('cover_image')->storeAs('public/cover_images', $fileNameToStore);
        } 

        //create POST
        $post =  Post::find($id);
        $post -> title = $request -> input('title');
        $post -> body = $request -> input('body');
        if($request->hasFile('cover_image')){
            $post -> cover_image = $fileNameToStore;
        }
        $post -> save();

        return redirect('/posts')->with('success', 'Post Updated');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post =  Post::find($id);
        /** check for correct user */
        if (auth()->user()->id !==$post->user_id) {
            return redirect ('/posts')->with('error', 'Unauthorized Page');
        }

        if ($post->cover_image != 'noimage.jpg') {
            //Delete Image
            Storage::delete('public/cover_images/' . $post -> cover_image );
        }

        $post -> delete();
        return redirect('/posts')->with('success', 'Post Removed');

    }
}
