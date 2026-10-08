<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn' => $this->faker->unique()->numerify('#############'),
            'judul' => $this->faker->sentence(3),
            'penulis' => $this->faker->name(),
            'penerbit' => $this->faker->company(),
            'tahun_terbit' => $this->faker->numberBetween(2000, date('Y')),
            'kategori_id' => \App\Models\Kategori::inRandomOrder()->first()->id ?? 1,
            'stok' => $this->faker->numberBetween(1, 10),
            'sinopsis' => $this->faker->paragraph(),
        ];
    }
}
