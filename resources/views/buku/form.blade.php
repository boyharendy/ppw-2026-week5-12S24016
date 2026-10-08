<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="isbn" class="block text-sm font-medium text-gray-700">ISBN (13 digit)</label>
        <div class="mt-1">
            <input type="text" name="isbn" id="isbn" value="{{ old('isbn', $buku->isbn ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
        </div>
        @error('isbn')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="judul" class="block text-sm font-medium text-gray-700">Judul Buku</label>
        <div class="mt-1">
            <input type="text" name="judul" id="judul" value="{{ old('judul', $buku->judul ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required minlength="5">
        </div>
        @error('judul')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="penulis" class="block text-sm font-medium text-gray-700">Penulis</label>
        <div class="mt-1">
            <input type="text" name="penulis" id="penulis" value="{{ old('penulis', $buku->penulis ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
        </div>
        @error('penulis')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="penerbit" class="block text-sm font-medium text-gray-700">Penerbit</label>
        <div class="mt-1">
            <input type="text" name="penerbit" id="penerbit" value="{{ old('penerbit', $buku->penerbit ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
        </div>
        @error('penerbit')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tahun_terbit" class="block text-sm font-medium text-gray-700">Tahun Terbit</label>
        <div class="mt-1">
            <input type="number" name="tahun_terbit" id="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit ?? '') }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required min="1900" max="{{ date('Y') }}">
        </div>
        @error('tahun_terbit')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kategori_id" class="block text-sm font-medium text-gray-700">Kategori</label>
        <div class="mt-1">
            <select name="kategori_id" id="kategori_id" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required>
                <option value="">Pilih Kategori</option>
                @foreach($kategoris as $kat)
                    <option value="{{ $kat->id }}" {{ old('kategori_id', $buku->kategori_id ?? '') == $kat->id ? 'selected' : '' }}>
                        {{ $kat->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>
        @error('kategori_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="stok" class="block text-sm font-medium text-gray-700">Stok</label>
        <div class="mt-1">
            <input type="number" name="stok" id="stok" value="{{ old('stok', $buku->stok ?? 0) }}" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border" required min="0">
        </div>
        @error('stok')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    
    <div class="sm:col-span-2">
        <label for="sinopsis" class="block text-sm font-medium text-gray-700">Sinopsis</label>
        <div class="mt-1">
            <textarea name="sinopsis" id="sinopsis" rows="3" class="shadow-sm border-gray-300 rounded block w-full sm:text-sm p-2 border">{{ old('sinopsis', $buku->sinopsis ?? '') }}</textarea>
        </div>
        @error('sinopsis')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-end">
    <a href="{{ route('buku.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 mr-4">Batal</a>
    <button type="submit" class="inline-flex justify-center rounded border border-transparent bg-del-purple py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-del-purple-hover">Simpan</button>
</div>
