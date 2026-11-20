<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory UserFactory
 *
 * Menyediakan generator data tiruan (dummy/fake) untuk model `User` bawaan Laravel
 * pada skenario pengujian otomatis (unit/feature testing) maupun seeding awal.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Kata sandi ter-hash yang disimpan sementara agar efisien saat membuat banyak data factory.
     *
     * @var string|null
     */
    protected static ?string $password;

    /**
     * Mendefinisikan nilai atribut bawaan (*default state*) untuk instansi model yang dihasilkan.
     *
     * @return array<string, mixed> Peta atribut default pengguna (nama, email, status verifikasi, sandi, token).
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Menandai state model agar alamat email berstatus belum terverifikasi (`email_verified_at = null`).
     *
     * @return static Instansi factory dengan state email belum terverifikasi.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
