<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="/logo.png" class="h-8" alt="Logo PST">
                    <span class="font-bold text-green-700">Portal Dokumen PST</span>
                </a>
            </div>

            <!-- Navigation Menu -->
            <div class="flex space-x-6 items-center">

                <!-- Menu Dokumen -->
                <a href="{{ route('documents.index') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    Dokumen
                </a>

                <!-- Menu User Management (Hanya untuk Admin) -->
                @if (auth()->user()->isAdmin())
                <a href="{{ route('admin.users.index') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    User Management
                </a>
                @endif

                <!-- Dropdown User -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center text-gray-600 hover:text-gray-900">
                            {{ auth()->user()->name }}
                            <svg class="ml-1 h-5 w-5" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </div>
</nav>
