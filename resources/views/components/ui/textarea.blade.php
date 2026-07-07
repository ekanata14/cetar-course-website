{{--
    Textarea standar Cetar — gaya identik dengan x-ui.input.
--}}
@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
])

<div>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif
            class="block text-sm font-semibold text-secondary mb-1.5">{{ $label }}</label>
    @endif

    <textarea rows="{{ $rows }}" @if ($name) id="{{ $name }}" name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl bg-surface-soft border border-black/5 px-4 py-3 text-[15px] text-ink placeholder:text-ink-faint focus:outline-none focus:ring-2 focus:ring-primary focus:bg-surface transition-all',
        ]) }}></textarea>

    @if ($name)
        @error($name)
            <p class="text-sm text-bad mt-1.5">{{ $message }}</p>
        @enderror
    @endif
</div>
