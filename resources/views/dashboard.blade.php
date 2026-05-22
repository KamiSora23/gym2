<x-app-layout>

    @include('dashboard.styles')

    <div id="dashboard-view">

        @include('dashboard.sidebar')

        <div class="main-content">

            @include('dashboard.main')

        </div>
    </div>

    @include('dashboard.scripts')

</x-app-layout>