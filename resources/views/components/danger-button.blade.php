<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn bg-danger text-white hover:bg-error']) }}>
    {{ $slot }}
</button>
