<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Jobs\SendNewPostEmail;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{

    public function search($term) {        
        $posts = Post::search($term)->get();
        // Raw json, misses avatar
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    public function update(Post $post, Request $request) {
        $incomingFields =  $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

    $incomingFields['title'] = strip_tags($incomingFields['title']);
    $incomingFields['body'] = strip_tags($incomingFields['body']);

    $post->update($incomingFields);

    return back()->with('success', 'Post actualizado!');
    }

    public function showEditForm(Post $post) {
        return view('edit-post', [
            'post' => $post
        ]);
    }

    public function delete(Post $post) {
        $post->delete();
        return redirect('/profile/' . auth()->user()->username)->with('success', 'Se borró el post.');
    }


    public function storeNewPost(Request $request){
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

    $incomingFields['title'] = strip_tags($incomingFields['title']);
    $incomingFields['body'] = strip_tags($incomingFields['body']);
    $incomingFields['user_id'] = auth()->id();



    $newPost = Post::create($incomingFields);

    // Send email (sync task), queue it up with a job
    // Mail::to(auth()->user()->email)->send(new NewPostEmail([
    //     'name' => auth()->user()->username,
    //     'title' => $newPost->title,
    // ]));

    // Async
    dispatch(new SendNewPostEmail([
        'sendTo' => auth()->user()->email,
        'name' => auth()->user()->username,
        'title' => $newPost->title,
    ]));


    return redirect('/post/' . $newPost->id)->with('success', 'Post Creado!');

    }
        
    public function showCreateForm(){
         return view('create-post');
    }

    public function viewSinglePost(Post $post){   

        $post->body = strip_tags(Str::markdown($post->body), '<p><a><img><h1><h2><h3><h4><h5><h6><ul><ol><li><strong><em><del><code><pre><blockquote><br>');
        return view('single-post', [
            'post' => $post
        ]);
    }
}
