<x-layout>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Tambah Buku</h3>
            <p class="mt-1 text-sm text-gray-500">Masukkan data buku baru.</p>
        </div>
        <div class="mt-5 md:col-span-2 md:mt-0">
            <form action="{{ route('buku.store') }}" method="POST">
                @csrf
                <div class="overflow-hidden shadow sm:rounded-md">
                    <div class="bg-white px-4 py-5 sm:p-6">
                        @include('buku.form')
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layout>
