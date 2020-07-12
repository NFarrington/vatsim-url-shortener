<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\News;
use App\Repositories\NewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class NewsController extends Controller
{
    protected EntityManagerInterface $em;
    protected NewsRepository $newsRepository;

    public function __construct(EntityManagerInterface $entityManager, NewsRepository $newsRepository)
    {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->em = $entityManager;
        $this->newsRepository = $newsRepository;
    }

    public function index()
    {
        $news = $this->newsRepository->findAll();

        return view('platform.admin.news.index')->with([
            'news' => $news,
        ]);
    }

    public function create()
    {
        $news = new News();
        $news->setTitle('');
        $news->setContent('');

        return view('platform.admin.news.create')->with([
            'news' => $news,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'title' => 'required|string|max:250',
            'content' => 'required|string|max:10000',
            'published' => 'boolean',
        ]);

        $news = new News();
        $news->setTitle($attributes['title']);
        $news->setContent($attributes['content']);
        $news->setPublished($attributes['published'] ?? false);
        $this->em->persist($news);
        $this->em->flush();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article created.');
    }

    public function show(News $news)
    {
        Session::reflash();

        return redirect()->route('platform.admin.news.edit', $news);
    }

    public function edit(News $news)
    {
        return view('platform.admin.news.edit')->with([
            'news' => $news,
        ]);
    }

    public function update(Request $request, News $news)
    {
        $attributes = $this->validate($request, [
            'title' => 'required|string|max:250',
            'content' => 'required|string|max:10000',
            'published' => 'boolean',
        ]);

        $news->setTitle($attributes['title']);
        $news->setContent($attributes['content']);
        $news->setPublished($attributes['published'] ?? false);
        $this->em->flush();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article updated.');
    }

    public function destroy(News $news)
    {
        $this->em->remove($news);
        $this->em->flush();

        return redirect()->route('platform.admin.news.index')
            ->with('success', 'News article deleted.');
    }
}
