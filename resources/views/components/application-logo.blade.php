@if(file_exists(public_path('images/billigventilation-logo.png')))
    <img src="{{ asset('images/billigventilation-logo.png') }}" alt="BilligVentilation" {{ $attributes->merge(['class' => 'object-contain']) }} style="max-height: 40px; width: auto;" />
@elseif(file_exists(public_path('images/billigventilation-logo.svg')))
    <img src="{{ asset('images/billigventilation-logo.svg') }}" alt="BilligVentilation" {{ $attributes->merge(['class' => 'object-contain']) }} style="max-height: 40px; width: auto;" />
@else
    {{-- Text logo fallback --}}
    <div {{ $attributes->merge(['class' => 'flex items-center space-x-2']) }}>
        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" fill="currentColor"/>
        </svg>
        <span class="font-bold text-lg whitespace-nowrap">BilligVentilation</span>
    </div>
@endif
