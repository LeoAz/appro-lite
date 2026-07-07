<nav x-data="{ open: false }" class="bg-blue-600 border-b border-blue-700 shadow-md">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('carriers') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-6 pb-5 border-b-2 border-transparent text-sm font-medium leading-5 text-white hover:text-blue-100 hover:border-blue-300 focus:outline-none focus:text-blue-100 focus:border-blue-300 transition duration-150 ease-in-out">
                                <div>Opérations</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('loads')">Chargements</x-dropdown-link>
                            <x-dropdown-link :href="route('deliveries')">Livraisons</x-dropdown-link>
                            <x-dropdown-link :href="route('fuel-purchases')">Achats Carburant</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-nav-link :href="route('invoices')" :active="request()->routeIs('invoices')" class="text-white hover:text-blue-100 active:text-white">
                        Facturation
                    </x-nav-link>

                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-6 pb-5 border-b-2 border-transparent text-sm font-medium leading-5 text-white hover:text-blue-100 hover:border-blue-300 focus:outline-none focus:text-blue-100 focus:border-blue-300 transition duration-150 ease-in-out">
                                <div>Rapports</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('reports.loads')">Rapport Chargement</x-dropdown-link>
                            <x-dropdown-link :href="route('reports.deliveries')">Rapport Livraison</x-dropdown-link>
                            <x-dropdown-link :href="route('reports.stocks')">Suivi Stock</x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <!-- Settings & Configuration Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                            <div>Configuration</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('carriers')">Transporteurs</x-dropdown-link>
                        <x-dropdown-link :href="route('clients')">Clients</x-dropdown-link>
                        <x-dropdown-link :href="route('depots')">Dépots</x-dropdown-link>
                    </x-slot>
                </x-dropdown>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Se déconnecter') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-blue-700">
        <!-- Navigation Links -->
        <div class="space-y-1 pt-2 pb-3">
            <div class="px-4 py-2 text-xs font-semibold text-blue-100 uppercase tracking-wider">Opérations</div>
            <x-responsive-nav-link :href="route('loads')" :active="request()->routeIs('loads')" class="text-white">
                Chargements
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('deliveries')" :active="request()->routeIs('deliveries')" class="text-white">
                Livraisons
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('fuel-purchases')" :active="request()->routeIs('fuel-purchases')" class="text-white">
                Achats Carburant
            </x-responsive-nav-link>

            <div class="pt-4 border-t border-blue-500"></div>
            <x-responsive-nav-link :href="route('invoices')" :active="request()->routeIs('invoices')" class="text-white">
                Facturation
            </x-responsive-nav-link>

            <div class="pt-4 border-t border-blue-500">
                <div class="px-4 py-2 text-xs font-semibold text-blue-100 uppercase tracking-wider">Rapports</div>
                <x-responsive-nav-link :href="route('reports.loads')" :active="request()->routeIs('reports.loads')" class="text-white">
                    Rapport Chargement
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.deliveries')" :active="request()->routeIs('reports.deliveries')" class="text-white">
                    Rapport Livraison
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('reports.stocks')" :active="request()->routeIs('reports.stocks')" class="text-white">
                    Suivi Stock
                </x-responsive-nav-link>
            </div>
        </div>

        <div class="pt-4 pb-1 border-t border-blue-500">
            <div class="px-4 text-xs font-semibold text-blue-100 uppercase tracking-wider">Configuration</div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('carriers')" class="text-white">Transporteurs</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('clients')" class="text-white">Clients</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('depots')" class="text-white">Dépots</x-responsive-nav-link>
            </div>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-blue-500">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-blue-100">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-white">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" class="text-white"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Se déconnecter') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
