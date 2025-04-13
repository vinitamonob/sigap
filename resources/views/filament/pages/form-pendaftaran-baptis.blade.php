<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}
        
        <button type="submit" class="bg-amber-600 hover:bg-orange-400 text-white font-semibold text-sm mt-6 py-2 px-3 rounded-lg">
            Submit
        </button>
    </form>
</x-filament-panels::page>
