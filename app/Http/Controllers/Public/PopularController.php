<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class PopularController extends Controller
{
    /**
     * Routes expected:
     *  /popular
     *  /popular/{period}
     *  /popular/{period}/{page}
     *
     * period: week|month|all
     */
    public function index(Request $request, ?string $period = null, ?int $page = 1)
    {
        // Safety: if someone hits /popular/2, treat it as page=2, period=week
        if (is_numeric($period)) {
            $page = (int) $period;
            $period = null;
        }

        $period = $this->normalizePeriod($period);
        $page   = max(1, (int) $page);

        // Make LengthAwarePaginator use our {page} route param
        Paginator::currentPageResolver(fn () => $page);

        [$from, $label] = $this->periodRange($period);

        $posts = Post::query()
            ->where('status', Post::STATUS_PUBLISHED) // use your constant
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from)) // filter by post creation date
            ->with([
                'tags:id,name,slug',
                'user:id,username,name,avatar',
            ])
            ->orderByDesc('views')        // MOST VIEWED
            ->orderByDesc('created_at')   // tie-breaker
            ->paginate(10)
            ->withQueryString();

        return view('popular.index', [
            'posts'       => $posts,
            'period'      => $period,
            'periodLabel' => $label,
        ]);
    }

    private function normalizePeriod(?string $period): string
    {
        $period = strtolower(trim((string) $period));

        return in_array($period, ['week', 'month', 'all'], true)
            ? $period
            : 'week';
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'month' => [now()->subDays(30), 'Most viewed (created this month)'],
            'all'   => [null, 'Most viewed (all time)'],
            default => [now()->subDays(7),  'Most viewed (created this week)'],
        };
    }
}
