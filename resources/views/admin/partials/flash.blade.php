@if(session('success'))
    <x-admin.ui.alert tone="success" title="Success">
        {{ session('success') }}
    </x-admin.ui.alert>
@endif

@if(session('error'))
    <x-admin.ui.alert tone="danger" title="Error">
        {{ session('error') }}
    </x-admin.ui.alert>
@endif

@if(session('warning'))
    <x-admin.ui.alert tone="warning" title="Notice">
        {{ session('warning') }}
    </x-admin.ui.alert>
@endif

@if(session('info'))
    <x-admin.ui.alert tone="neutral" title="Info">
        {{ session('info') }}
    </x-admin.ui.alert>
@endif
