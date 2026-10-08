<div>
    <label for="kode_kategori" class="block text-sm font-medium text-gray-700">Kode Kategori</label>
    <div class="mt-1">
        <input type="text" name="kode_kategori" id="kode_kategori" value="{{ old('kode_kategori', $kategori->kode_kategori ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
    </div>
    @error('kode_kategori')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mt-4">
    <label for="nama_kategori" class="block text-sm font-medium text-gray-700">Nama Kategori</label>
    <div class="mt-1">
        <input type="text" name="nama_kategori" id="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
    </div>
    @error('nama_kategori')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mt-6 flex items-center justify-end">
    <a href="{{ route('kategori.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 mr-4">Batal</a>
    <button type="submit" class="inline-flex justify-center rounded border border-transparent bg-del-purple py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-del-purple-hover">Simpan</button>
</div>
