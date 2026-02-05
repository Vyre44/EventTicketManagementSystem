<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}">
                        <!-- Logo component kaldırıldı -->
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @auth
                        @if(auth()->user()->role === \App\Enums\UserRole::ADMIN)
                            <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900">Dashboard</a>
                            <a href="{{ route('admin.events.index') }}" class="text-gray-700 hover:text-gray-900">Events</a>
                            <a href="{{ route('admin.orders.index') }}" class="text-gray-700 hover:text-gray-900">Orders</a>
                            <a href="{{ route('admin.users.index') }}" class="text-gray-700 hover:text-gray-900">Users</a>
                            <a href="{{ route('admin.reports.index') }}" class="text-gray-700 hover:text-gray-900">Reports</a>
                        @elseif(auth()->user()->role === \App\Enums\UserRole::ORGANIZER)
                            <a href="{{ route('organizer.events.index') }}" class="text-gray-700 hover:text-gray-900">My Events</a>
                            <a href="{{ route('organizer.orders.index') }}" class="text-gray-700 hover:text-gray-900">Orders</a>
                            <a href="{{ route('organizer.tickets.index') }}" class="text-gray-700 hover:text-gray-900">Tickets</a>
                            <a href="{{ route('organizer.events.index') }}" class="text-gray-700 hover:text-gray-900">Reports</a>
                        @else
                            <a href="{{ route('attendee.events.index') }}" class="text-gray-700 hover:text-gray-900">Events</a>
                            <a href="{{ route('attendee.orders.index') }}" class="text-gray-700 hover:text-gray-900">My Orders</a>
                        @endif
                    @else
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-gray-900">{{ __('Home') }}</a>
                    @endauth
                </div>
            </div>

            <!-- Desktop: Direct Logout Button + User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:space-x-4">
                @auth
                    <!-- Visible Logout Button (red, prominent) -->
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 hover:text-red-800 transition ease-in-out">
                            {{ __('Çıkış') }}
                        </button>
                    </form>

                    <!-- User Info Dropdown -->
                    <div class="relative group">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <div class="absolute right-0 mt-2 w-48 bg-white border rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">{{ __('Profile') }}</a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 text-sm font-medium">{{ __('Login') }}</a>
                @endauth
            </div>

        </div>
    </div>
</nav>
