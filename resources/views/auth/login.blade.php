<x-guest-layout>

    {{-- FORCE FULLSCREEN OVERRIDE --}}
    <style>
        html,
        body {
            height: 100%;
            width: 100%;
            padding: 0 !important;
            margin: 0 !important;
            overflow: hidden;
            background: white;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
    </style>

    <div class="h-screen w-screen grid grid-cols-1 lg:grid-cols-2">

        {{-- LEFT PANEL (WHITE VERSION) --}}
        <div class="bg-white text-gray-900 flex flex-col justify-center px-6 sm:px-10 lg:px-20 relative">

            {{-- Logo & Header --}}
            <div class="hidden lg:flex absolute top-10 left-10 items-center gap-3">
                <img src="/images/logo.png" class="h-12" alt="Logo PST">
                <div>
                    <h1 class="text-xl font-bold tracking-wide">PT. PUTRA SARANA TRANSBORNEO</h1>
                    <p class="text-gray-500 text-xs">
                        Developed by Corporate Planning System Development
                    </p>
                </div>
            </div>

            <div class="lg:hidden mb-8 flex items-center gap-3">
                <img src="/images/logo.png" class="h-10" alt="Logo PST">
                <div>
                    <h1 class="text-base font-bold tracking-wide">PT. PUTRA SARANA TRANSBORNEO</h1>
                    <p class="text-gray-500 text-xs">
                        Corporate Planning System Development
                    </p>
                </div>
            </div>

            {{-- Title --}}
            <div class="mt-8 sm:mt-12 lg:mt-24 mb-6">
                <h1 class="text-5xl lg:text-6xl font-extrabold text-[#0AA03A] leading-tight">
                    PORTAL<br>DOKUMEN
                </h1>
            </div>

            {{-- Error --}}
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('login') }}" class="space-y-6 w-full max-w-md">
                @csrf

                {{-- EMAIL --}}
                <div>
                    <label class="text-gray-700 text-sm font-medium">Email Address</label>
                    <input type="email" name="email"
                        class="w-full mt-1 px-4 py-3 bg-gray-100 border border-gray-300
                               rounded-lg text-gray-900 focus:ring-2 focus:ring-[#0AA03A]"
                        required autofocus>
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label class="text-gray-700 text-sm font-medium">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                            class="w-full mt-1 px-4 py-3 bg-gray-100 border border-gray-300
                                   rounded-lg text-gray-900 focus:ring-2 focus:ring-[#0AA03A]"
                            required>

                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-3 text-gray-600 hover:text-gray-900"></button>
                    </div>
                </div>

                {{-- REMEMBER --}}
                <label class="flex items-center gap-2 text-gray-700 text-sm">
                    <input type="checkbox" name="remember" class="rounded text-green-600 focus:ring-green-500">
                    Remember me
                </label>

                {{-- LOGIN BUTTON --}}
                <button type="submit"
                    class="w-full bg-[#0AA03A] hover:bg-[#087C2D] text-white font-bold py-3 rounded-lg text-lg transition">
                    LOGIN
                </button>
            </form>

        </div>

        {{-- RIGHT PANEL --}}
        {{-- RIGHT PANEL (VIDEO BACKGROUND) --}}
        <div class="relative hidden lg:block">

            <video autoplay muted loop playsinline class="absolute inset-0 w-full h-full object-cover brightness-[0.8]">
                <source src="/videos/bg-videos.mp4" type="video/mp4">
            </video>

            {{-- Soft shading left-to-right --}}
            <div class="absolute inset-0 bg-gradient-to-l from-black/60 to-transparent"></div>

            <div class="absolute bottom-10 right-10 text-white text-right">
                <h2 class="text-3xl font-bold drop-shadow">PT. PUTRA SARANA TRANSBORNEO</h2>
                <p class="text-sm opacity-80">Corporate Planning System Development</p>
            </div>

        </div>


    </div>

    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            field.type = field.type === 'password' ? 'text' : 'password';
        }
    </script>

</x-guest-layout>
