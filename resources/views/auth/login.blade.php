<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">

        <!-- Box -->
        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-10">

            <!-- Logo -->
            <div class="flex flex-col items-center mb-8">
                <img src="/logo.png" class="h-16 mb-3" alt="Logo PST">
                <h1 class="text-2xl font-bold text-gray-800">Portal Dokumen PST</h1>
                <p class="text-gray-500 text-sm">Silakan login untuk melanjutkan</p>
            </div>

            <!-- Error -->
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-700 p-3 rounded-lg">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label class="text-gray-700 font-medium">Email</label>
                    <input type="email" name="email"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-600 focus:border-green-600"
                        required autofocus>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label class="text-gray-700 font-medium">Password</label>
                    <input type="password" name="password"
                        class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-600 focus:border-green-600"
                        required>
                </div>

                <!-- Remember -->
                <div class="flex items-center mb-6">
                    <input id="remember" type="checkbox" name="remember"
                        class="rounded text-green-600 focus:ring-green-500">
                    <label for="remember" class="ml-2 text-gray-600">
                        Remember me
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg text-lg font-semibold transition">
                    Login
                </button>

            </form>

        </div>

    </div>
</x-guest-layout>
