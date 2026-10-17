<x-layout>
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-gray-900">Sirkulasi Peminjaman Buku</h1>
            <p class="mt-2 text-sm text-gray-700">Daftar seluruh riwayat transaksi peminjaman dan pengembalian buku.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            @auth
                <a href="{{ route('peminjaman.create') }}" class="inline-flex items-center justify-center rounded border border-transparent bg-del-purple px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-del-purple-hover">
                    + Pinjam Buku Baru
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Login Pustakawan
                </a>
            @endauth
        </div>
    </div>

    <!-- Filter & Pencarian -->
    <div class="mt-6 mb-4">
        <form action="{{ route('peminjaman.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-1/3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIM, Nama, atau Kode Pinjam..." class="block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm">
            </div>
            <div class="w-full sm:w-1/4">
                <select name="status" class="block w-full rounded border-gray-300 shadow-sm p-2 border sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full inline-flex justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">Filter</button>
            </div>
            @if(request()->has('search') || request()->has('status'))
                <div class="w-full sm:w-auto">
                    <a href="{{ route('peminjaman.index') }}" class="w-full inline-flex justify-center rounded border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm hover:bg-gray-50">Reset</a>
                </div>
            @endif
        </form>
    </div>

    <!-- Tabel Peminjaman -->
    <div class="mt-4 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Kode Pinjam</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Buku</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Peminjam</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tgl Pinjam</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jatuh Tempo</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right text-sm font-semibold text-gray-900">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($peminjamans as $pinjam)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-900 sm:pl-6">{{ $pinjam->kode_pinjam }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 font-medium">
                                        {{ $pinjam->buku->judul ?? 'Buku Dihapus' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-700">
                                        <div class="font-medium">{{ $pinjam->nama_peminjam }}</div>
                                        <div class="text-xs text-gray-500">NIM: {{ $pinjam->nim_peminjam }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $pinjam->tanggal_pinjam }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $pinjam->tanggal_jatuh_tempo }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($pinjam->status === 'dipinjam')
                                            <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-800">Sedang Dipinjam</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                                Dikembalikan ({{ $pinjam->tanggal_kembali }})
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        @if($pinjam->status === 'dipinjam')
                                            @auth
                                                <form action="{{ route('peminjaman.kembalikan', $pinjam) }}" method="POST" class="inline" onsubmit="return confirm('Konfirmasi pengembalian buku ini? Stok akan otomatis bertambah.');">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 font-semibold bg-green-50 hover:bg-green-100 px-3 py-1 rounded border border-green-200">
                                                        Kembalikan
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-yellow-700 bg-yellow-50 px-2 py-1 rounded border border-yellow-200">Perlu Login</span>
                                            @endauth
                                        @else
                                            <span class="text-gray-400 text-xs">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada data peminjaman buku.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $peminjamans->links() }}
    </div>
</x-layout>
