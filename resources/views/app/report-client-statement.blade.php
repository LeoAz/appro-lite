<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rapport de Situation Client') }}
        </h2>
    </x-slot>

    <livewire:report.client-statement-report />
</x-app-layout>
