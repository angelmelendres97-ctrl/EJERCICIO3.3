@extends('filament-panels::resources.pages.create-record')

@push('scripts')
    <script>
        window.addEventListener('open-resumen-pedidos-pdf', (event) => {
            const url = event?.detail?.url;
            if (url) {
                window.open(url, '_blank');
            }
        });
    </script>
@endpush
