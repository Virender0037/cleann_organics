{{-- Custom paginator view matching the existing Shopery pagination markup
     (pagination-item/pagination-link classes, chevron SVGs) so switching
     from the old static page-number links to real pagination needed no
     visual/CSS changes. --}}
@if ($paginator->hasPages())
    <nav aria-label="Page navigation pagination--one" class="pagination-wrapper section--xl" style="padding-top: 20px;">
        <ul class="pagination justify-content-center">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <li class="page-item pagination-item disabled">
                    <a class="page-link pagination-link" href="#" tabindex="-1" aria-label="Previous">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.91663 1.16634L1.08329 6.99967L6.91663 12.833" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
            @else
                <li class="page-item pagination-item">
                    <a class="page-link pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.91663 1.16634L1.08329 6.99967L6.91663 12.833" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="page-item pagination-item"><p class="page-link pagination-link">{{ $element }}</p></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item pagination-item"><a class="page-link pagination-link active" href="{{ $url }}" aria-current="page">{{ $page }}</a></li>
                        @else
                            <li class="page-item pagination-item"><a class="page-link pagination-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <li class="page-item pagination-item">
                    <a class="page-link pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.08337 1.16634L6.91671 6.99967L1.08337 12.833" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
            @else
                <li class="page-item pagination-item disabled">
                    <a class="page-link pagination-link" href="#" tabindex="-1" aria-label="Next">
                        <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1.08337 1.16634L6.91671 6.99967L1.08337 12.833" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
@endif
