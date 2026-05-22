<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-6 lg:px-10">
        <div class="flex justify-between items-center h-20">

            <!-- IZQUIERDA -->
            <div class="flex items-center gap-5">

                <!-- LOGO -->
                <!-- LOGO -->
                <a href="
@if(Auth::check() && Auth::user()->role->nombre == 'cliente')
    {{ url('/') }}
@else
    {{ route('dashboard') }}
@endif
" class="flex items-center gap-3">

                    <img src="{{ asset('img/logo.png') }}"
                        alt="Logo Gym"
                        class="w-14 h-14 object-contain">

                    <div>
                        <h1 class="text-xl font-bold text-gray-800 leading-none">
                            {{ $config->nombre_gym }}
                        </h1>

                        <p class="text-sm text-gray-400">
                            Sistema Administrativo
                        </p>
                    </div>
                </a>

            </div>

            <!-- CENTRO -->
            <div class="hidden md:flex items-center">

                <!-- BUSCADOR -->
                <div class="relative">

                    <input
                        type="text"
                        placeholder="Buscar..."
                        class="w-80 bg-gray-100 border-none rounded-2xl pl-12 pr-4 py-3 text-sm focus:ring-2 focus:ring-red-400">

                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400"></i>

                </div>

            </div>


            <!-- DERECHA -->
            <div class="flex items-center gap-6">

                <!-- NOTIFICACIONES -->
                <button class="relative">
                    <i class="fas fa-bell text-xl text-gray-500"></i>

                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] rounded-full px-1">
                        3
                    </span>
                </button>

                <!-- PERFIL -->
                <x-dropdown align="right" width="48">

                    <x-slot name="trigger">

                        <button class="flex items-center gap-3 bg-gray-50 hover:bg-gray-100 px-3 py-2 rounded-2xl transition">

                            <!-- AVATAR -->
                            <div class="w-11 h-11 rounded-full bg-gradient-to-r from-cyan-400 to-blue-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                            </div>

                            <!-- INFO -->
                            <div class="hidden md:block text-left">
                                <div class="text-sm font-semibold text-gray-800">
                                    {{ Auth::user()->name }}
                                </div>

                                <div class="text-xs text-gray-400 break-all leading-4 max-w-[180px]">
                                    {{ Auth::user()->email }}
                                </div>
                            </div>

                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>

                        </button>

                    </x-slot>

                    <!-- DROPDOWN -->
                    <x-slot name="content">

                        <div class="px-4 py-3 border-b">
                            <p class="font-semibold text-gray-800">
                                {{ Auth::user()->name }}
                            </p>

                            <p class="text-xs text-gray-400 break-all leading-4 max-w-[180px]">
                                {{ Auth::user()->email }}
                            </p>
                        </div>

                        <!-- PERFIL -->
                        <x-dropdown-link :href="route('profile.edit')">
                            ⚙️ Editar Perfil
                        </x-dropdown-link>

                        <!-- CONFIG -->
                        <x-dropdown-link href="#">
                            🔔 Notificaciones
                        </x-dropdown-link>

                        <!-- LOGOUT -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                this.closest('form').submit();">

                                🚪 Cerrar Sesión

                            </x-dropdown-link>
                        </form>

                    </x-slot>

                </x-dropdown>

            </div>

        </div>
    </div>
</nav>