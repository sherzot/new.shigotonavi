@if ($paginator->hasPages())
    <nav class="d-flex flex-column align-items-center my-3">
        {{-- ページ情報 (Ko'rsatilayotgan ma'lumotlar haqida) --}}
        <span class="page-info mb-2">
            {{ $paginator->firstItem() }}件目から{{ $paginator->lastItem() }}件目まで、合計{{ $paginator->total() }}件
        </span> 

        {{-- ページネーションリンク (Pagination Links) --}}
        <ul class="pagination justify-content-center flex-wrap" style="width: 100%; max-width: 100%;">

            {{-- 前へ (Oldingi Sahifa) --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">前へ</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">前へ</a>
                </li>
            @endif

            {{-- ページ番号 --}}
            @foreach ($elements as $element)
                {{-- "..." 表示 --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- ページ番号リンク --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- 次へ (Keyingi Sahifa) --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">次へ</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">次へ</span>
                </li>
            @endif
        </ul>
    </nav>
@endif

<style>
    /* Paginationni mobil qurilmalarga moslashtirish uchun */
    .page-info {
        text-align: center;
        font-size: 1rem;
    }

    .pagination {
        margin: 0;
    }

    @media (max-width: 768px) {
        .pagination {
            font-size: 0.9rem;
        }

        .page-link {
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
        }

        .page-info {
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .pagination {
            font-size: 0.8rem;
        }

        .page-link {
            padding: 0.4rem 0.6rem;
            font-size: 0.75rem;
        }

        .page-info {
            font-size: 0.8rem;
        }
    }
</style>
