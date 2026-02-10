<nav class="bg-white border-b border-gray-200" x-data="{ open: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="/logo.png" class="h-8" alt="Logo PST">
                    <span class="font-bold text-green-700">Portal Dokumen PST</span>
                </a>
            </div>

            <!-- Desktop Navigation Menu -->
            <div class="hidden md:flex space-x-6 items-center">

                <!-- Menu Dashboard -->
                <a href="{{ route('dashboard') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    Dashboard
                </a>

                <!-- Menu Dokumen -->
                <a href="{{ route('documents.index') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    Dokumen
                </a>
                <a href="{{ route('documents.review') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    Review
                </a>
                <a href="{{ route('documents.numbers') }}"
                    class="text-gray-700 hover:text-green-700 font-medium">
                    Nomor Dokumen
                </a>

                @can('document.manage')
                    <a href="{{ route('documents.export_page') }}"
                        class="text-gray-700 hover:text-green-700 font-medium">
                        Export
                    </a>
                @endcan

                @if (auth()->user()->isAdmin())
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center text-gray-700 hover:text-green-700 font-medium">
                                Admin
                                <svg class="ml-1 h-5 w-5" fill="currentColor"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('admin.users.index')">
                                User Management
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.departments.index')">
                                Departemen
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.sites.index')">
                                Site
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.document-types.index')">
                                Tipe Dokumen
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.audits.index')">
                                Audit Log
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
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
                        <form method="POST" action="{{ route('logout') }}"
                            data-confirm="logout"
                            data-confirm-message="Yakin ingin logout?">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            <!-- Mobile button -->
            <div class="flex items-center md:hidden">
                <button type="button" @click="open = !open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-green-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <span class="sr-only">Open menu</span>
                    <svg class="h-6 w-6" x-show="!open" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg class="h-6 w-6" x-show="open" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden" x-show="open" x-cloak>
            <div class="pt-3 pb-4 space-y-2">
                <a href="{{ route('dashboard') }}"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                    Dashboard
                </a>
                <a href="{{ route('documents.index') }}"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                    Dokumen
                </a>
                <a href="{{ route('documents.review') }}"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                    Review
                </a>
                <a href="{{ route('documents.numbers') }}"
                    class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                    Nomor Dokumen
                </a>
                @can('document.manage')
                    <a href="{{ route('documents.export_page') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        Export
                    </a>
                @endcan
                @if (auth()->user()->isAdmin())
                    <div class="px-3 pt-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Admin
                    </div>
                    <a href="{{ route('admin.users.index') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        User Management
                    </a>
                    <a href="{{ route('admin.departments.index') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        Departemen
                    </a>
                    <a href="{{ route('admin.sites.index') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        Site
                    </a>
                    <a href="{{ route('admin.document-types.index') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        Tipe Dokumen
                    </a>
                    <a href="{{ route('admin.audits.index') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 hover:text-green-700 font-medium">
                        Audit Log
                    </a>
                @endif
            </div>

            <div class="border-t border-gray-200 pt-3 pb-4">
                <div class="px-3 text-sm text-gray-600">{{ auth()->user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2"
                    data-confirm="logout" data-confirm-message="Yakin ingin logout?">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-3 py-2 rounded-md text-red-600 hover:bg-red-50 font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
