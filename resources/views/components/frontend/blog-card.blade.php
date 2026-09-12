@props(['blog'])
<div class="cards-blog">
    <div class="cards-blog__img-wrapper">
        <a href="{{ route('singleblog', $blog->slug) }}">
            <img
                src="{{ storage_image_url($blog->featured_image, asset('images/blogs/blog-01.png')) }}"
                alt="{{ $blog->title }}"
            />
        </a>
        @if ($blog->published_at)
            <div class="date">
                <h3 class="font-body--xxl-500">{{ $blog->published_at->format('d') }}</h3>
                <span class="font-body--sm-500">{{ $blog->published_at->format('M') }}</span>
            </div>
        @endif
    </div>
    <div class="cards-blog__info">
        <div class="cards-blog__info-tags d-flex">
            @if ($blog->category)
                <div class="cards-blog__info-tags-item">
                    <span>
                        <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M17.1583 11.6748L11.1833 17.6498C11.0285 17.8048 10.8447 17.9277 10.6424 18.0116C10.4401 18.0955 10.2232 18.1386 10.0042 18.1386C9.78513 18.1386 9.56825 18.0955 9.36592 18.0116C9.16359 17.9277 8.97978 17.8048 8.82499 17.6498L1.66666 10.4998V2.1665H9.99999L17.1583 9.32484C17.4687 9.63711 17.643 10.0595 17.643 10.4998C17.643 10.9401 17.4687 11.3626 17.1583 11.6748V11.6748Z"
                                stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M5.83331 6.33301H5.84165" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    {{ $blog->category->name }}
                </div>
            @endif
            <div class="cards-blog__info-tags-item">
                <span>
                    <svg width="14" height="17" viewBox="0 0 14 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M6.99993 7.66667C8.84088 7.66667 10.3333 6.17428 10.3333 4.33333C10.3333 2.49238 8.84088 1 6.99993 1C5.15898 1 3.6666 2.49238 3.6666 4.33333C3.6666 6.17428 5.15898 7.66667 6.99993 7.66667Z"
                            stroke="currentColor" stroke-width="1.2" />
                        <path
                            d="M9.49995 10.1665H4.49995C2.19828 10.1665 0.137447 12.2915 1.65161 14.024C2.68161 15.2023 4.38495 15.9998 6.99995 15.9998C9.61495 15.9998 11.3174 15.2023 12.3474 14.024C13.8624 12.2907 11.8008 10.1665 9.49995 10.1665Z"
                            stroke="currentColor" stroke-width="1.2" />
                    </svg>
                </span>
                By {{ $blog->author?->name ?? 'Admin' }}
            </div>
            <div class="cards-blog__info-tags-item">
                <span>
                    <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M9.52381 11.2728L8.48206 13.0087C8.43209 13.092 8.36139 13.1609 8.27687 13.2088C8.19234 13.2566 8.09686 13.2818 7.99972 13.2818C7.90258 13.2818 7.8071 13.2566 7.72257 13.2088C7.63804 13.1609 7.56735 13.092 7.51738 13.0087L6.47675 11.2728C6.42671 11.1895 6.35596 11.1206 6.27138 11.0728C6.1868 11.025 6.09128 10.9999 5.99413 11H1.8125C1.66332 11 1.52024 10.9407 1.41475 10.8352C1.30926 10.7298 1.25 10.5867 1.25 10.4375V1.4375C1.25 1.28832 1.30926 1.14524 1.41475 1.03975C1.52024 0.934263 1.66332 0.875 1.8125 0.875H14.1875C14.3367 0.875 14.4798 0.934263 14.5852 1.03975C14.6907 1.14524 14.75 1.28832 14.75 1.4375V10.4375C14.75 10.5867 14.6907 10.7298 14.5852 10.8352C14.4798 10.9407 14.3367 11 14.1875 11H10.0059C9.90881 11 9.81341 11.0252 9.72894 11.073C9.64446 11.1208 9.5738 11.1896 9.52381 11.2728V11.2728Z"
                            stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </span>
                {{ number_format($blog->view_count) }} {{ Str::plural('View', $blog->view_count) }}
            </div>
        </div>
        <a href="{{ route('singleblog', $blog->slug) }}" class="blog-title font-body--xl-500">{{ $blog->title }}</a>
        <a href="{{ route('singleblog', $blog->slug) }}">
            Read More
            <span>
                <svg width="17" height="15" viewBox="0 0 17 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 7.50049H1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9.95001 1.47559L16 7.49959L9.95001 13.5246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </a>
    </div>
</div>
