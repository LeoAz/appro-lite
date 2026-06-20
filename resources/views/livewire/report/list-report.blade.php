<div>
    {{ $this->table }}

    @script
    <script>
        $wire.on('print-report', () => {
            window.print();
        });
    </script>
    @endscript

    <style>
        @media print {
            header, nav, .fi-sidebar, .fi-topbar, .fi-header-actions, .fi-ta-filters, .fi-ta-header-toolbar {
                display: none !important;
            }
            .fi-main {
                padding: 0 !important;
            }
        }
    </style>
</div>
