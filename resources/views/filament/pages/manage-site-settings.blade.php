<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="filament-form-actions flex justify-end">
            <x-filament::button type="submit" icon="heroicon-m-check">
                Save changes
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
