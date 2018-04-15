<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $news = News::orderByDesc('created_at')->paginate(20);

        return view('platform.admin.news.index')->with([
            'news' => $news,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('platform.admin.news.create')->with([
            'news' => new News(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'title' => 'required|string|max:250',
            'content' => 'required|string|max:10000',
            'published' => 'boolean',
        ]);

        $news = new News();
        $news->published = false;
        $news->fill($attributes);
        $news->save();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article created.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function edit(News $news)
    {
        return view('platform.admin.news.edit')->with([
            'news' => $news,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\News  $news
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, News $news)
    {
        $attributes = $this->validate($request, [
            'title' => 'required|string|max:250',
            'content' => 'required|string|max:10000',
            'published' => 'boolean',
        ]);

        $news->published = false;
        $news->fill($attributes);
        $news->save();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\News $news
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(News $news)
    {
        $news->delete();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article deleted.');
    }
}
