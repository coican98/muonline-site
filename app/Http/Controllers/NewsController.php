<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NewsService;
use Auth;

class NewsController extends Controller
{
    /**
     * Listagem geral de notícias em /noticias
     */
    public function index()
    {
        $title = 'Mu Rootz - Notícias';
        $isAdmin = Auth::check() && Auth::user()->global_admin == 1;

        if ($isAdmin) {
            $newsList = NewsService::getAllForAdmin();
        } else {
            $newsList = NewsService::getActiveQuery()->orderBy('id', 'desc')->paginate(10);
        }

        return view('news.index', [
            'title' => $title,
            'newsList' => $newsList,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * Visualização de uma notícia individual em /noticias/{slug}
     */
    public function show($slug)
    {
        NewsService::ensureTableExists();
        $isAdmin = Auth::check() && Auth::user()->global_admin == 1;

        $news = NewsService::findBySlugOrId($slug);
        if (!$news) {
            abort(404, 'Notícia não encontrada');
        }

        // Se o usuário não for admin e a notícia estiver inativa ou agendada para o futuro
        if (!$isAdmin) {
            if (!$news->active) {
                abort(404, 'Notícia não disponível');
            }
            if ($news->published_at && \Carbon\Carbon::parse($news->published_at)->isFuture()) {
                abort(404, 'Notícia ainda não publicada');
            }
        }

        $title = $news->title . ' - Mu Rootz';

        return view('news.show', [
            'title' => $title,
            'news' => $news,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * Endpoint para salvar nova notícia (Admin)
     */
    public function store(Request $request)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:50',
            'summary' => 'nullable|string|max:300',
            'content' => 'required|string',
            'image_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
        ]);

        // Salva o login da conta (memb___id) como autor para vínculo ao User model
        $authorId = Auth::user()->username ?? Auth::user()->memb___id ?? 'admin';

        NewsService::create([
            'title' => $request->title,
            'category' => $request->category ?? 'Servidor',
            'summary' => $request->summary,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'active' => $request->has('active') ? 1 : 0,
            'show_on_home_modal' => $request->has('show_on_home_modal') ? 1 : 0,
            'published_at' => $request->published_at,
            'author' => $authorId,
        ]);

        return redirect()->back()->with('success', 'Notícia criada com sucesso!');
    }

    /**
     * Endpoint para atualizar notícia (Admin)
     */
    public function update(Request $request, $id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Acesso negado.');
        }

        $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'nullable|string|max:50',
            'summary' => 'nullable|string|max:300',
            'content' => 'required|string',
            'image_url' => 'nullable|url|max:255',
            'published_at' => 'nullable|date',
        ]);

        $updateData = [
            'title' => $request->title,
            'category' => $request->category ?? 'Servidor',
            'summary' => $request->summary,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'active' => $request->has('active') ? 1 : 0,
            'show_on_home_modal' => $request->has('show_on_home_modal') ? 1 : 0,
            'published_at' => $request->published_at,
        ];

        // Atualiza autor se fornecido ou mantém
        if (Auth::check()) {
            $updateData['author'] = Auth::user()->username ?? Auth::user()->memb___id;
        }

        NewsService::update($id, $updateData);

        return redirect()->back()->with('success', 'Notícia atualizada com sucesso!');
    }

    /**
     * Alternar status ativo/inativo (Admin)
     */
    public function toggle($id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Acesso negado.');
        }

        NewsService::toggleActive($id);
        return redirect()->back()->with('success', 'Status da notícia alterado!');
    }

    /**
     * Excluir notícia (Admin)
     */
    public function destroy($id)
    {
        if (!(Auth::check() && Auth::user()->global_admin == 1)) {
            return redirect('/')->with('error', 'Acesso negado.');
        }

        NewsService::delete($id);
        return redirect()->back()->with('success', 'Notícia excluída com sucesso!');
    }
}
