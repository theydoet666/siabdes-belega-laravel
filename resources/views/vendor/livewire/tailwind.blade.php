@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Paginasi" class="flex items-center justify-between">
        <div class="flex justify-between flex-1 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-4 py-2 text-xs font-semibold text-tinta/40 bg-kertas/50 border border-garis cursor-not-allowed rounded-xl">
                    « Sebelumnya
                </span>
            @else
                <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 text-xs font-semibold text-tinta bg-white border border-garis rounded-xl hover:bg-kertas transition cursor-pointer">
                    « Sebelumnya
                </button>
            @endif

            @if ($paginator->hasMorePages())
                <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold text-tinta bg-white border border-garis rounded-xl hover:bg-kertas transition cursor-pointer">
                    Berikutnya »
                </button>
            @else
                <span class="relative inline-flex items-center px-4 py-2 ml-3 text-xs font-semibold text-tinta/40 bg-kertas/50 border border-garis cursor-not-allowed rounded-xl">
                    Berikutnya »
                </span>
            @endif
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-xs text-tinta/60">
                    Menampilkan
                    <span class="font-bold text-tinta">{{ $paginator->firstItem() ?? 0 }}</span>
                    sampai
                    <span class="font-bold text-tinta">{{ $paginator->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-bold text-tinta">{{ $paginator->total() }}</span>
                    data
                </p>
            </div>

            <div>
                <span class="relative z-0 inline-flex rounded-xl shadow-xs space-x-1">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Sebelumnya" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-tinta/30 bg-kertas/40 border border-garis/60 cursor-not-allowed rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="Sebelumnya" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-tinta bg-white border border-garis rounded-lg hover:bg-kertas hover:border-daun hover:text-daun transition cursor-pointer">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="relative inline-flex items-center px-3 py-1.5 text-xs font-bold text-tinta/40 bg-white border border-garis rounded-lg cursor-default">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-bold text-white bg-daun border border-daun rounded-lg shadow-xs cursor-default">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" class="relative inline-flex items-center px-3.5 py-1.5 text-xs font-medium text-tinta bg-white border border-garis rounded-lg hover:bg-kertas hover:border-daun hover:text-daun transition cursor-pointer">
                                        {{ $page }}
                                    </button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" aria-label="Berikutnya" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-tinta bg-white border border-garis rounded-lg hover:bg-kertas hover:border-daun hover:text-daun transition cursor-pointer">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    @else
                        <span aria-disabled="true" aria-label="Berikutnya" class="relative inline-flex items-center px-2.5 py-1.5 text-xs font-semibold text-tinta/30 bg-kertas/40 border border-garis/60 cursor-not-allowed rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
