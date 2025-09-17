@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Oldingi sahifa --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled"><span class="page-link">«</span></li>
            @else
                <li class="page-item">
                    <a class="page-link pagination-link" href="{{ $paginator->previousPageUrl() }}" data-page="{{ $paginator->currentPage() - 1 }}">«</a>
                </li>
            @endif

            {{-- Sahifa raqamlari --}}
            @foreach ($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                @if ($page == $paginator->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item">
                        <a class="page-link pagination-link" href="{{ $url }}" data-page="{{ $page }}">{{ $page }}</a>
                    </li>
                @endif
            @endforeach

            {{-- Keyingi sahifa --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link pagination-link" href="{{ $paginator->nextPageUrl() }}" data-page="{{ $paginator->currentPage() + 1 }}">»</a>
                </li>
            @else
                <li class="page-item disabled"><span class="page-link">»</span></li>
            @endif
        </ul>
    </nav>
@endif
