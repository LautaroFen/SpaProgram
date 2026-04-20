<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        if (! filter_var(env('SPA_SEED_DEMO_USERS', false), FILTER_VALIDATE_BOOL)) {
            return;
        }

        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();
        $receptionRole = Role::query()->where('name', 'recepcion')->firstOrFail();
        $professionalRole = Role::query()->where('name', 'profesional')->firstOrFail();

        $adminEmail = env('SPA_DEMO_ADMIN_EMAIL', 'admin@local.test');
        $receptionEmail = env('SPA_DEMO_RECEPTION_EMAIL', 'recepcion@local.test');
        $professionalEmail = env('SPA_DEMO_PROFESSIONAL_EMAIL', 'profesional@local.test');

        $adminPasswordRaw = env('SPA_DEMO_ADMIN_PASSWORD');
        $receptionPasswordRaw = env('SPA_DEMO_RECEPTION_PASSWORD');
        $professionalPasswordRaw = env('SPA_DEMO_PROFESSIONAL_PASSWORD');

        $adminPasswordWasProvided = $this->wasProvided($adminPasswordRaw);
        $receptionPasswordWasProvided = $this->wasProvided($receptionPasswordRaw);
        $professionalPasswordWasProvided = $this->wasProvided($professionalPasswordRaw);

        $adminPassword = $this->normalizePassword($adminPasswordRaw);
        $receptionPassword = $this->normalizePassword($receptionPasswordRaw);
        $professionalPassword = $this->normalizePassword($professionalPasswordRaw);

        $adminUser = User::query()->where('email', $adminEmail)->first();
        $receptionUser = User::query()->where('email', $receptionEmail)->first();
        $professionalUser = User::query()->where('email', $professionalEmail)->first();

        $generatedAdminPassword = null;
        $generatedReceptionPassword = null;
        $generatedProfessionalPassword = null;

        [$adminPassword, $generatedAdminPassword] = $this->resolvePassword(
            $adminUser,
            $adminPassword,
            $adminPasswordWasProvided
        );
        [$receptionPassword, $generatedReceptionPassword] = $this->resolvePassword(
            $receptionUser,
            $receptionPassword,
            $receptionPasswordWasProvided
        );
        [$professionalPassword, $generatedProfessionalPassword] = $this->resolvePassword(
            $professionalUser,
            $professionalPassword,
            $professionalPasswordWasProvided
        );

        User::query()->updateOrCreate(
            ['email' => $adminEmail],
            array_filter([
                'role_id' => $adminRole->id,
                'first_name' => 'Admin',
                'last_name' => 'Spa',
                'job_title' => 'Administrador',
                'password' => $adminPassword,
            ], fn ($value) => $value !== null)
        );

        User::query()->updateOrCreate(
            ['email' => $receptionEmail],
            array_filter([
                'role_id' => $receptionRole->id,
                'first_name' => 'Recepción',
                'last_name' => 'Spa',
                'job_title' => 'Recepcionista',
                'password' => $receptionPassword,
            ], fn ($value) => $value !== null)
        );

        User::query()->updateOrCreate(
            ['email' => $professionalEmail],
            array_filter([
                'role_id' => $professionalRole->id,
                'first_name' => 'Profesional',
                'last_name' => 'Spa',
                'job_title' => 'Profesional',
                'password' => $professionalPassword,
            ], fn ($value) => $value !== null)
        );

        if ($this->command) {
            $this->command->warn('Demo users creados (solo local/testing).');

            if ($generatedAdminPassword) {
                $this->command->line("Admin: {$adminEmail} / {$generatedAdminPassword}");
            } else {
                $this->command->line("Admin: {$adminEmail} / [password no mostrado]");
            }

            if ($generatedReceptionPassword) {
                $this->command->line("Recepción: {$receptionEmail} / {$generatedReceptionPassword}");
            } else {
                $this->command->line("Recepción: {$receptionEmail} / [password no mostrado]");
            }

            if ($generatedProfessionalPassword) {
                $this->command->line("Profesional: {$professionalEmail} / {$generatedProfessionalPassword}");
            } else {
                $this->command->line("Profesional: {$professionalEmail} / [password no mostrado]");
            }
        }
    }

    private function normalizePassword(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Avoid seeding trivial/weak demo passwords like "1234".
        // If the password is too short, force auto-generation.
        if (mb_strlen($value) < 12) {
            return null;
        }

        return $value;
    }

    private function wasProvided(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    /**
     * @return array{0: string|null, 1: string|null} [passwordToSet, generatedPlainPasswordForOutput]
     */
    private function resolvePassword(?User $user, ?string $normalizedPassword, bool $wasProvided): array
    {
        if ($normalizedPassword) {
            return [$normalizedPassword, null];
        }

        // If no valid password was provided:
        // - Create new users with an auto-generated password.
        // - If a password WAS provided but was invalid/weak (e.g. "1234"), reset to a generated one.
        // - Otherwise keep existing password unchanged.
        if (! $user || $wasProvided) {
            $generated = Str::password(16);
            return [$generated, $generated];
        }

        return [null, null];
    }
}
