@php
    $usuario = Auth::user();
    $enPanel = request()->routeIs('dashboard');
@endphp

<nav class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="px-4 lg:px-8">
        <div class="flex justify-between items-center h-16">

            <div class="flex items-center gap-3">
                @if ($enPanel)
                    <button type="button" id="sidebarToggle"
                        class="lg:hidden w-10 h-10 rounded-xl text-slate-600 hover:bg-slate-100 transition"
                        aria-label="Abrir menú">
                        <i class="fas fa-bars"></i>
                    </button>
                @endif

                <a href="{{ $usuario->homeRoute() }}" class="flex items-center gap-3">
                    <img src="{{ $config->logo_url }}" alt="Logo" class="w-10 h-10 object-contain rounded-lg">

                    <div class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight text-slate-900">
                            {{ $config->nombre_gym }}
                        </span>
                        <span class="block text-xs text-slate-400">
                            {{ $usuario->isStaff() ? 'Sistema administrativo' : 'Área de clientes' }}
                        </span>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ url('/') }}" target="_blank"
                    class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 px-3 py-2 rounded-xl hover:bg-slate-100 transition">
                    <i class="fas fa-arrow-up-right-from-square text-xs"></i> Ver sitio
                </a>

                @if ($usuario->isStaff() && ! $enPanel)
                    <a href="{{ $usuario->homeRoute() }}"
                        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-900 px-3 py-2 rounded-xl hover:bg-slate-100 transition">
                        <i class="fas fa-gauge text-xs"></i> Panel
                    </a>
                @endif

                <x-dropdown align="right" width="w-56">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-3 hover:bg-slate-100 pl-1 pr-3 py-1 rounded-2xl transition">
                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold"
                                style="background: var(--primary-color)">
                                {{ strtoupper(mb_substr($usuario->name, 0, 1)) }}
                            </span>

                            <span class="hidden md:block text-left leading-tight">
                                <span class="block text-sm font-semibold text-slate-800">{{ $usuario->name }}</span>
                                <span class="block text-xs text-slate-400">{{ $usuario->role?->etiqueta }}</span>
                            </span>

                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="font-semibold text-slate-800 truncate">{{ $usuario->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $usuario->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <i class="fas fa-user-gear w-5 text-slate-400"></i> Mi perfil
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fas fa-right-from-bracket w-5 text-slate-400"></i> Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

        </div>
    </div>
</nav>
