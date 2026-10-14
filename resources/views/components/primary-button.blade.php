<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn-primary disabled:opacity-40']) }}>
    {{ $slot }}
</button>
