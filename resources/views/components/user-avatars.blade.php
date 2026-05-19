@props([
    'users',
    'max' => 5,
    'size' => 'md',
])

@php
    $users = collect($users)->filter()->unique(fn ($user) => $user->getKey())->values();
    $visible = $users->take($max);
    $overflow = $users->count() - $visible->count();

    $sizeClasses = match ($size) {
        'sm' => 'h-6 w-6 text-[10px]',
        'lg' => 'h-10 w-10 text-sm',
        default => 'h-8 w-8 text-xs',
    };
@endphp

<div {{ $attributes->class(['flex items-center -space-x-2']) }}>
    @foreach ($visible as $user)
        <div
            @class([
                $sizeClasses,
                'flex-shrink-0 overflow-hidden rounded-full border-2 border-white bg-gray-200 dark:border-gray-900',
            ])
            title="{{ $user->name }}"
        >
            <img
                src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&color=7F9CF5&background=EBF4FF&size=128"
                alt="{{ $user->name }}"
                class="h-full w-full object-cover"
            />
        </div>
    @endforeach

    @if ($overflow > 0)
        <div
            @class([
                $sizeClasses,
                'flex flex-shrink-0 items-center justify-center rounded-full border-2 border-white bg-gray-300 font-medium text-gray-700 dark:border-gray-900 dark:bg-gray-600 dark:text-gray-200',
            ])
            title="{{ __('kanban::kanban.member.and_more', ['count' => $overflow]) }}"
        >
            +{{ $overflow }}
        </div>
    @endif
</div>
