<x-layout>
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Ubah Kategori</h3>
            <p class="mt-1 text-sm text-gray-500">Ubah data kategori ini.</p>
        </div>
        <div class="mt-5 md:col-span-2 md:mt-0">
            <form action="{{ route('kategori.update', $kategori) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="overflow-hidden shadow sm:rounded-md">
                    <div class="bg-white px-4 py-5 sm:p-6">
                        @include('kategori.form')
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layout>
