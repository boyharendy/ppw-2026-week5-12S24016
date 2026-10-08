<x-layout>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Katalog Buku</h1>
            <p class="mt-2 text-sm text-gray-700">Daftar semua koleksi buku perpustakaan.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('buku.create') }}" class="inline-flex items-center justify-center rounded border border-transparent bg-del-purple px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-del-purple-hover">Tambah Buku</a>
        </div>
    </div>
    
    <div class="mt-6 mb-4">
        <form action="{{ route('buku.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-1/3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, atau ISBN..." class="block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm">
            </div>
            <div class="w-full sm:w-1/3">
                <select name="kategori_id" class="block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full inline-flex justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Cari</button>
            </div>
            @if(request()->has('search') || request()->has('kategori_id'))
                <div class="w-full sm:w-auto">
                    <a href="{{ route('buku.index') }}" class="w-full inline-flex justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-gray-50">Hapus Filter</a>
                </div>
            @endif
        </form>
    </div>

    <div class="mt-4 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">ISBN</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Judul</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Penulis</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kategori</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tahun</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($bukus as $buku)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ $buku->isbn }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                        <a href="{{ route('buku.show', $buku) }}" class="text-del-purple hover:text-del-purple-hover">{{ $buku->judul }}</a>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $buku->penulis }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $buku->kategori->nama_kategori }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $buku->tahun_terbit }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        @if($buku->status_stok == 'Tersedia')
                                            <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Tersedia</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800">Habis</span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="{{ route('buku.edit', $buku) }}" class="text-del-purple hover:text-del-purple-hover">Ubah</a>
                                        <form action="{{ route('buku.destroy', $buku) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-center text-sm text-gray-500">Tidak ada data. <a href="{{ route('buku.create') }}" class="text-del-purple hover:underline">Tambah buku pertama</a>.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        {{ $bukus->links() }}
    </div>
</x-layout>
