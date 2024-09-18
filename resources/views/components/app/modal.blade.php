@props([
    'formAction' => false,
    'headerClasses' => '',
    'contentClasses' => 'px-4 sm:p-6',
    'footerClasses' => 'px-4 pb-5 sm:px-4 sm:flex',
])

<div class="w-auto bg-white rounded-lg">
    @if($formAction)
        <form wire:submit.prevent="{{ $formAction }}">
    @endif
        <div class="{{ $headerClasses }}">
            @if(isset($title))
                <h3 class="flex items-center text-lg font-medium leading-6 text-secondary-900">
                    {{ $title }}
                </h3>
            @endif

            @if(isset($subtitle))
                <div class="mt-2">
                    <p class="text-sm leading-5 text-secondary-500">
                        {{ $subtitle }}
                    </p>
                </div>
            @endif
        </div>

        @if(isset($content))
            <div class="{{ $contentClasses }}">
                {{ $content }}
            </div>
        @endif

        <div class="{{ $footerClasses }}">
            {{ $buttons ?? null }}
        </div>
    @if($formAction)
        </form>
    @endif
</div>