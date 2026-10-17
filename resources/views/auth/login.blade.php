<x-layout>
    <div class="max-w-md mx-auto my-8 bg-white p-8 border border-gray-200 rounded-lg shadow-sm">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-del-purple">Login Pustakawan</h1>
            <p class="mt-1 text-sm text-gray-600">Masuk untuk mengelola data buku, kategori, dan transaksi sirkulasi.</p>
        </div>

        <!-- Kartu Kredensial Uji Modul -->
        <div class="mb-6 bg-purple-50 border border-purple-200 rounded-md p-4 text-xs text-del-purple">
            <p class="font-bold mb-1">Akun Pustakawan Uji (Sesuai Modul Praktikum):</p>
            <p>Email: <code class="font-mono bg-purple-100 px-1 py-0.5 rounded text-gray-800">pustakawan@del.ac.id</code></p>
            <p>Password: <code class="font-mono bg-purple-100 px-1 py-0.5 rounded text-gray-800">password123</code></p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                <input type="email" name="email" id="email" required
                       value="{{ old('email', request()->cookie('del_last_user_email')) }}"
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2.5 border focus:ring-del-purple focus:border-del-purple sm:text-sm"
                       placeholder="pustakawan@del.ac.id">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
                @if(request()->cookie('del_last_user_email'))
                    <p class="mt-1 text-xs text-gray-500">Email diisi otomatis dari Cookie tersimpan.</p>
                @endif
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                <input type="password" name="password" id="password" required
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2.5 border focus:ring-del-purple focus:border-del-purple sm:text-sm"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                           class="h-4 w-4 rounded border-gray-300 text-del-purple focus:ring-del-purple">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Ingat saya</label>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded shadow-sm text-sm font-medium text-white bg-del-purple hover:bg-del-purple-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-del-purple">
                    Masuk ke Sistem
                </button>
            </div>
        </form>
    </div>
</x-layout>
