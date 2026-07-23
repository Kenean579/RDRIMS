<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
            
            // Optional fields (nullable in database)
            'department_id' => null,
            'university_id' => null,
            'research_center_id' => null,
            'center_role_id' => null,
            'profile_image_id' => null,
            'orcid_id' => null,
            'google_scholar_id' => null,
            'scopus_id' => null,
            'linkedin_url' => null,
            'expertise_keywords' => null,
            'bio' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the user has a university.
     */
    public function withUniversity(int $universityId): static
    {
        return $this->state(fn (array $attributes) => [
            'university_id' => $universityId,
        ]);
    }

    /**
     * Indicate that the user has a department.
     */
    public function withDepartment(int $departmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => $departmentId,
        ]);
    }

    /**
     * Indicate that the user has a research center.
     */
    public function withResearchCenter(int $researchCenterId, ?int $centerRoleId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'research_center_id' => $researchCenterId,
            'center_role_id' => $centerRoleId,
        ]);
    }

    /**
     * Indicate that the user has expertise keywords.
     */
    public function withExpertise(string $keywords): static
    {
        return $this->state(fn (array $attributes) => [
            'expertise_keywords' => $keywords,
        ]);
    }
}
