<?php

namespace OiLab\LaravelDocumentation\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use Inertia\Response;
use OiLab\LaravelDocumentation\Services\DocumentationService;

class DocumentationController extends Controller
{
    public function __construct(
        private readonly DocumentationService $documentationService
    ) {}

    public function index(): Response
    {
        $navigation = $this->documentationService->getNavigation();

        return Inertia::render('documentation/index', [
            'navigation' => $navigation,
        ]);
    }

    public function show(string $slug): Response
    {
        $document = $this->documentationService->getDocument($slug);

        if (! $document) {
            abort(404);
        }

        $navigation = $this->documentationService->getNavigation();
        $adjacentPages = $this->documentationService->getAdjacentPages($slug);

        return Inertia::render('documentation/show', [
            'document' => $document,
            'navigation' => $navigation,
            'slug' => $slug,
            'previousPage' => $adjacentPages['previous'],
            'nextPage' => $adjacentPages['next'],
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = strtolower(trim($request->input('q', '')));

        $minLength = config('oi-laravel-documentation.search.min_query_length', 2);

        if (strlen($query) < $minLength) {
            return response()->json([]);
        }

        $indexPath = base_path(config('oi-laravel-documentation.docs_path', 'resources/docs').'/'.config('oi-laravel-documentation.search_index_file', 'search-index.json'));

        if (! File::exists($indexPath)) {
            return response()->json([]);
        }

        $index = json_decode(File::get($indexPath), true);

        $results = array_filter($index, function ($item) use ($query) {
            return str_contains(strtolower($item['title']), $query) ||
                   str_contains(strtolower($item['description']), $query) ||
                   str_contains(strtolower($item['content']), $query) ||
                   collect($item['headings'])->contains(fn ($heading) => str_contains(strtolower($heading), $query));
        });

        $scoredResults = array_map(function ($item) use ($query) {
            $score = 0;

            if (str_contains(strtolower($item['title']), $query)) {
                $score += 10;
            }

            if (str_contains(strtolower($item['description']), $query)) {
                $score += 5;
            }

            if (collect($item['headings'])->contains(fn ($heading) => str_contains(strtolower($heading), $query))) {
                $score += 3;
            }

            if (str_contains(strtolower($item['content']), $query)) {
                $score += 1;
            }

            $excerpt = $this->extractExcerpt($item['content'], $query);

            return array_merge($item, ['score' => $score, 'excerpt' => $excerpt]);
        }, $results);

        usort($scoredResults, fn ($a, $b) => $b['score'] <=> $a['score']);

        return response()->json(array_values($scoredResults));
    }

    private function extractExcerpt(string $content, string $query): string
    {
        $length = config('oi-laravel-documentation.search.excerpt_length', 150);
        $context = config('oi-laravel-documentation.search.excerpt_context', 50);

        $position = stripos($content, $query);

        if ($position === false) {
            return mb_substr($content, 0, $length).'...';
        }

        $start = max(0, $position - $context);
        $excerpt = mb_substr($content, $start, $length);

        if ($start > 0) {
            $excerpt = '...'.$excerpt;
        }

        if (mb_strlen($content) > $start + $length) {
            $excerpt .= '...';
        }

        return $excerpt;
    }
}
