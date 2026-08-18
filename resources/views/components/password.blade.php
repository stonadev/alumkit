@props(['name', 'label' => null, 'required' => false])

<div x-data="{ show: false }">
    @if ($label)
        <label for="{{ $name }}" class="dark:text-dark-400 mb-1 block text-sm font-semibold text-gray-600">
            {{ $label }}
        </label>
    @endif

    <div class="focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 focus-within:ring-2 dark:ring-dark-600 dark:text-dark-300 text-gray-600 ring-gray-300 dark:bg-dark-800 bg-white">
        <input type="password" id="{{ $name }}" name="{{ $name }}" :type="show ? 'text' : 'password'"
               @required($required)
               autocomplete="{{ $attributes->get('autocomplete', 'off') }}"
               {{ $attributes->except(['autocomplete'])->class([
                   'dark:placeholder-dark-400 w-full rounded-md border-0 bg-transparent py-1.5 ring-0',
                   'placeholder:text-gray-400 focus:outline-hidden focus:ring-transparent sm:text-sm sm:leading-6 pl-3 pr-0',
               ]) }}>

        <div class="dark:text-dark-400 flex select-none items-center whitespace-nowrap text-gray-500 sm:text-sm ml-1 mr-2">
            <button type="button" dusk="alumkit_form_password_reveal" @click="show = !show" class="cursor-pointer">
                <svg class="h-5 w-5" x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
                    <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd"/>
                </svg>
                <svg class="h-5 w-5" x-show="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path d="M3.53 2.47a.75.75 0 0 0-1.06 1.06l18 18a.75.75 0 1 0 1.06-1.06l-18-18ZM22.676 12.553a11.249 11.249 0 0 1-2.631 4.31l-3.099-3.099a5.25 5.25 0 0 0-6.71-6.71L7.759 4.577a11.217 11.217 0 0 1 4.242-.827c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113Z"/>
                    <path d="M15.75 12c0 .18-.013.357-.037.53l-4.244-4.243A3.75 3.75 0 0 1 15.75 12ZM12.53 15.713l-4.243-4.244a3.75 3.75 0 0 0 4.244 4.243Z"/>
                    <path d="M6.75 12c0-.619.107-1.213.304-1.764l-3.1-3.1a11.25 11.25 0 0 0-2.63 4.31c-.12.362-.12.752 0 1.114 1.489 4.467 5.704 7.69 10.675 7.69 1.5 0 2.933-.294 4.242-.827l-2.477-2.477A5.25 5.25 0 0 1 6.75 12Z"/>
                </svg>
            </button>
        </div>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
