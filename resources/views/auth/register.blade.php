<x-guest-layout>
    <x-slot:title>Crear cuenta</x-slot:title>

    <h1 class="text-headline-lg text-primary">Crear cuenta</h1>
    <p class="mt-1 mb-6 text-body-md text-muted">Regístrate como cliente para cotizar en línea.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="company" value="Empresa (opcional)" />
                <x-text-input id="company" class="block mt-1 w-full" type="text" name="company" :value="old('company')" autocomplete="organization" />
                <x-input-error :messages="$errors->get('company')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" value="Teléfono (opcional)" />
                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full mt-6">
            {{ __('Register') }}
        </x-primary-button>

        <p class="mt-4 text-center text-body-md text-muted">
            <a class="text-primary-container font-semibold hover:underline" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
        </p>
    </form>
</x-guest-layout>
