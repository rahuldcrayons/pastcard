<div class="header-wishlist link wishlist">
    <a href="{{ route('wishlists.index') }}">
        <span class="counter qty">
            @if(Auth::check())
                {{ count(Auth::user()->wishlists)}}
            @else
                0
            @endif
        </span>
    </a>
</div>
