@if ($paginator->hasPages())
  <nav aria-label="Navigasi halaman" class="my-2">
    <ul class="pagination justify-content-center gap-2 mb-0 pill-clean">
      @php
        $isFirst = $paginator->currentPage() <= 1;
        $isLast  = method_exists($paginator, 'lastPage')
                    ? ($paginator->currentPage() >= $paginator->lastPage())
                    : (!$paginator->hasMorePages());
      @endphp

      {{-- Prev --}}
      <li class="page-item {{ $isFirst ? 'disabled' : '' }}">
        @if ($isFirst)
          <span class="page-link rounded-pill px-3" aria-disabled="true">«</span>
        @else
          <a class="page-link rounded-pill px-3" href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a>
        @endif
      </li>

      {{-- Numbers --}}
      @foreach ($elements as $element)
        @if (is_string($element))
          <li class="page-item disabled"><span class="page-link rounded-pill px-3">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="page-item active" aria-current="page">
                <span class="page-link rounded-pill px-3">{{ $page }}</span>
              </li>
            @else
              <li class="page-item">
                <a class="page-link rounded-pill px-3" href="{{ $url }}">{{ $page }}</a>
              </li>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Next --}}
      <li class="page-item {{ $isLast ? 'disabled' : '' }}">
        @if ($isLast)
          <span class="page-link rounded-pill px-3" aria-disabled="true">»</span>
        @else
          <a class="page-link rounded-pill px-3" href="{{ $paginator->nextPageUrl() }}" rel="next">»</a>
        @endif
      </li>
    </ul>
  </nav>

  <style>
    /* Senada dengan UI kamu (soft, border tipis, aksen ungu/biru) */
    .pagination.pill-clean .page-link{
      border:1px solid #e6ebf2; background:#fff; color:#495057; min-width:36px; text-align:center;
      box-shadow:0 1px 0 rgba(0,0,0,0.02);
    }
    .pagination.pill-clean .page-link:hover{
      background:#eef2ff; border-color:#c7d2fe; color:#4338ca;
    }
    .pagination.pill-clean .active .page-link{
      background:#4f46e5; border-color:#4f46e5; color:#fff;
    }
    .pagination.pill-clean .disabled .page-link{
      background:#fff; opacity:.5; pointer-events:none;
    }
  </style>
@endif
