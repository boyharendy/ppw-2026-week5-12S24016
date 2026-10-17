<x-layout>
    <div class="max-w-2xl mx-auto">
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold leading-7 text-gray-900 sm:text-2xl sm:truncate">Form Peminjaman Buku</h2>
                <p class="mt-1 text-sm text-gray-500">Catat transaksi peminjaman buku baru secara atomik.</p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('peminjaman.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Kembali
                </a>
            </div>
        </div>

        <form action="{{ route('peminjaman.store') }}" method="POST" class="bg-white shadow rounded p-6 border border-gray-200">
            @csrf

            <!-- Pilihan Buku -->
            <div class="mb-4">
                <label for="buku_id" class="block text-sm font-medium text-gray-700">Pilih Buku <span class="text-red-500">*</span></label>
                <select name="buku_id" id="buku_id" required class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm @error('buku_id') border-red-500 @enderror">
                    <option value="">-- Pilih Buku yang Tersedia --</option>
                    @foreach($bukus as $buku)
                        <option value="{{ $buku->id }}" {{ (old('buku_id', $selectedBukuId) == $buku->id) ? 'selected' : '' }}>
                            {{ $buku->judul }} (Sisa Stok: {{ $buku->stok }})
                        </option>
                    @endforeach
                </select>
                @error('buku_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- NIM Peminjam -->
            <div class="mb-4">
                <label for="nim_peminjam" class="block text-sm font-medium text-gray-700">NIM Mahasiswa <span class="text-red-500">*</span></label>
                <input type="text" name="nim_peminjam" id="nim_peminjam" value="{{ old('nim_peminjam') }}" placeholder="Contoh: 12S24016" required class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm @error('nim_peminjam') border-red-500 @enderror">
                @error('nim_peminjam')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nama Peminjam -->
            <div class="mb-4">
                <label for="nama_peminjam" class="block text-sm font-medium text-gray-700">Nama Lengkap Mahasiswa <span class="text-red-500">*</span></label>
                <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}" placeholder="Masukkan nama peminjam..." required class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm @error('nama_peminjam') border-red-500 @enderror">
                @error('nama_peminjam')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- Tanggal Pinjam -->
                <div>
                    <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700">Tanggal Pinjam <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm @error('tanggal_pinjam') border-red-500 @enderror">
                    @error('tanggal_pinjam')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Jatuh Tempo -->
                <div>
                    <label for="tanggal_jatuh_tempo" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}" required class="mt-1 block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm @error('tanggal_jatuh_tempo') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Default peminjaman: 7 hari</p>
                    @error('tanggal_jatuh_tempo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('peminjaman.index') }}" class="px-4 py-2 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 bg-del-purple hover:bg-del-purple-hover text-white rounded text-sm font-medium shadow-sm">
                    Simpan Transaksi Pinjam
                </button>
            </div>
        </form>
    </div>
</x-layout>
