<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NewsService
{
    /**
     * Garante que a tabela 'mw_news' e suas colunas existam no banco de dados
     */
    public static function ensureTableExists(): void
    {
        try {
            if (!Schema::hasTable('mw_news')) {
                Schema::create('mw_news', function (Blueprint $table) {
                    $table->id();
                    $table->string('title', 200);
                    $table->string('category', 50)->default('Servidor');
                    $table->string('summary', 300)->nullable();
                    $table->text('content');
                    $table->string('image_url', 255)->nullable();
                    $table->boolean('active')->default(true);
                    $table->boolean('show_on_home_modal')->default(false);
                    $table->dateTime('published_at')->nullable();
                    $table->string('author', 50)->default('Administração');
                    $table->timestamps();
                });
            } else {
                Schema::table('mw_news', function (Blueprint $table) {
                    if (!Schema::hasColumn('mw_news', 'slug')) {
                        $table->string('slug', 250)->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'title')) {
                        $table->string('title', 200)->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'category')) {
                        $table->string('category', 50)->default('Servidor');
                    }
                    if (!Schema::hasColumn('mw_news', 'summary')) {
                        $table->string('summary', 300)->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'content')) {
                        $table->text('content')->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'image_url')) {
                        $table->string('image_url', 255)->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'active')) {
                        $table->boolean('active')->default(true);
                    }
                    if (!Schema::hasColumn('mw_news', 'show_on_home_modal')) {
                        $table->boolean('show_on_home_modal')->default(false);
                    }
                    if (!Schema::hasColumn('mw_news', 'published_at')) {
                        $table->dateTime('published_at')->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'author')) {
                        $table->string('author', 50)->default('Administração');
                    }
                    if (!Schema::hasColumn('mw_news', 'created_at')) {
                        $table->timestamp('created_at')->nullable();
                    }
                    if (!Schema::hasColumn('mw_news', 'updated_at')) {
                        $table->timestamp('updated_at')->nullable();
                    }
                });

                // Se body, slug ou type existirem como NOT NULL no banco antigo, altera para NULLABLE para prevenir erros
                try {
                    if (Schema::hasColumn('mw_news', 'slug')) {
                        DB::statement("ALTER TABLE mw_news ALTER COLUMN slug NVARCHAR(255) NULL");
                    }
                    if (Schema::hasColumn('mw_news', 'body')) {
                        DB::statement("ALTER TABLE mw_news ALTER COLUMN body NVARCHAR(MAX) NULL");
                    }
                    if (Schema::hasColumn('mw_news', 'type')) {
                        DB::statement("ALTER TABLE mw_news ALTER COLUMN type NVARCHAR(50) NULL");
                    }
                } catch (\Throwable $ignore) {}
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Retorna a query base com join no MEMB_INFO para obter o nome real do autor
     */
    public static function getBaseQuery()
    {
        self::ensureTableExists();

        return DB::table('mw_news')
            ->leftJoin('MEMB_INFO', 'mw_news.author', '=', 'MEMB_INFO.memb___id')
            ->select(
                'mw_news.*',
                'mw_news.author as author_id',
                DB::raw("COALESCE(NULLIF(LTRIM(RTRIM(MEMB_INFO.memb_name)), ''), mw_news.author, 'Administração') as author_name")
            );
    }

    /**
     * Retorna query de notícias ativas e cujo agendamento já venceu (ou sem agendamento)
     */
    public static function getActiveQuery()
    {
        $now = now();
        return self::getBaseQuery()
            ->where('mw_news.active', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('mw_news.published_at')
                  ->orWhere('mw_news.published_at', '<=', $now);
            });
    }

    /**
     * Obtém as 3 notícias mais recentes para a Home
     */
    public static function getRecentForHome(int $limit = 3)
    {
        return self::getActiveQuery()
            ->orderBy('mw_news.id', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtém a notícia única marcada para ser exibida no Modal da página inicial (se ativa e pronta)
     */
    public static function getHomeModalNews()
    {
        return self::getActiveQuery()
            ->where('mw_news.show_on_home_modal', 1)
            ->orderBy('mw_news.id', 'desc')
            ->first();
    }

    /**
     * Retorna todas as notícias para o Admin (ativas e inativas)
     */
    public static function getAllForAdmin()
    {
        return self::getBaseQuery()
            ->orderBy('mw_news.id', 'desc')
            ->get();
    }

    /**
     * Gera um slug único e limpo baseado no título e resumo/subtítulo
     */
    public static function generateUniqueSlug(string $title, ?string $summary = null, ?int $excludeId = null): string
    {
        // Base de texto: título + primeiras palavras do resumo (se houver)
        $baseText = trim($title);
        if (!empty($summary)) {
            $cleanSummary = strip_tags($summary);
            $words = preg_split('/\s+/', trim($cleanSummary));
            $summarySnippet = implode(' ', array_slice($words, 0, 5));
            if (!empty($summarySnippet)) {
                $baseText .= ' ' . $summarySnippet;
            }
        }

        $baseSlug = \Illuminate\Support\Str::slug($baseText);
        if (empty($baseSlug)) {
            $baseSlug = 'noticia-' . time();
        }

        // Garante tamanho razoável para o slug (máx 150 caracteres)
        $baseSlug = \Illuminate\Support\Str::limit($baseSlug, 150, '');

        $slug = $baseSlug;
        $counter = 1;

        // Verifica unicidade no banco
        while (true) {
            $query = DB::table('mw_news')->where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }

            if (!$query->exists()) {
                break;
            }

            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }

        return $slug;
    }

    /**
     * Localiza notícia por slug ou por id numérico como fallback (com nome do autor vinculado)
     */
    public static function findBySlugOrId($identifier)
    {
        self::ensureTableExists();

        $query = self::getBaseQuery();

        if (Schema::hasColumn('mw_news', 'slug')) {
            $news = (clone $query)->where('mw_news.slug', $identifier)->first();
            if ($news) {
                return $news;
            }
        }

        if (is_numeric($identifier)) {
            return (clone $query)->where('mw_news.id', (int)$identifier)->first();
        }

        return null;
    }

    /**
     * Cria uma nova notícia
     */
    public static function create(array $data): int
    {
        self::ensureTableExists();

        $showModal = !empty($data['show_on_home_modal']);
        if ($showModal) {
            // Desmarca todas as outras notícias
            DB::table('mw_news')->update(['show_on_home_modal' => 0]);
        }

        $publishedAt = null;
        if (!empty($data['published_at'])) {
            $publishedAt = Carbon::parse($data['published_at'])->toDateTimeString();
        }

        // Geração automática e única de slug baseado no título e resumo
        $slug = self::generateUniqueSlug($data['title'], $data['summary'] ?? null);

        $insertData = [
            'title' => trim($data['title']),
            'category' => trim($data['category'] ?? 'Servidor'),
            'summary' => !empty($data['summary']) ? trim($data['summary']) : null,
            'content' => $data['content'],
            'image_url' => !empty($data['image_url']) ? trim($data['image_url']) : null,
            'active' => isset($data['active']) ? (int)(bool)$data['active'] : 1,
            'show_on_home_modal' => $showModal ? 1 : 0,
            'published_at' => $publishedAt,
            'author' => !empty($data['author']) ? trim($data['author']) : 'Administração',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Compatibilidade com banco legado MuCore / WebEngine (slug, body, type)
        if (Schema::hasColumn('mw_news', 'slug')) {
            $insertData['slug'] = $slug;
        }
        if (Schema::hasColumn('mw_news', 'body')) {
            $insertData['body'] = $data['content'];
        }
        if (Schema::hasColumn('mw_news', 'type')) {
            $insertData['type'] = trim($data['category'] ?? 'Servidor');
        }

        return DB::table('mw_news')->insertGetId($insertData);
    }

    /**
     * Atualiza uma notícia existente
     */
    public static function update(int $id, array $data): bool
    {
        self::ensureTableExists();

        $showModal = !empty($data['show_on_home_modal']);
        if ($showModal) {
            // Desmarca em todas as notícias exceto nesta
            DB::table('mw_news')->where('id', '!=', $id)->update(['show_on_home_modal' => 0]);
        }

        $publishedAt = null;
        if (!empty($data['published_at'])) {
            $publishedAt = Carbon::parse($data['published_at'])->toDateTimeString();
        }

        $updateData = [
            'title' => trim($data['title']),
            'category' => trim($data['category'] ?? 'Servidor'),
            'summary' => !empty($data['summary']) ? trim($data['summary']) : null,
            'content' => $data['content'],
            'image_url' => !empty($data['image_url']) ? trim($data['image_url']) : null,
            'active' => isset($data['active']) ? (int)(bool)$data['active'] : 1,
            'show_on_home_modal' => $showModal ? 1 : 0,
            'published_at' => $publishedAt,
            'author' => !empty($data['author']) ? trim($data['author']) : 'Administração',
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('mw_news', 'slug')) {
            $updateData['slug'] = self::generateUniqueSlug($data['title'], $data['summary'] ?? null, $id);
        }
        if (Schema::hasColumn('mw_news', 'body')) {
            $updateData['body'] = $data['content'];
        }
        if (Schema::hasColumn('mw_news', 'type')) {
            $updateData['type'] = trim($data['category'] ?? 'Servidor');
        }

        return DB::table('mw_news')->where('id', $id)->update($updateData) > 0;
    }

    /**
     * Alterna status ativo/inativo de uma notícia
     */
    public static function toggleActive(int $id): bool
    {
        self::ensureTableExists();
        $news = DB::table('mw_news')->where('id', $id)->first();
        if (!$news) return false;

        $newStatus = $news->active ? 0 : 1;
        return DB::table('mw_news')->where('id', $id)->update([
            'active' => $newStatus,
            'updated_at' => now(),
        ]) > 0;
    }

    /**
     * Exclui uma notícia
     */
    public static function delete(int $id): bool
    {
        self::ensureTableExists();
        return DB::table('mw_news')->where('id', $id)->delete() > 0;
    }
}
