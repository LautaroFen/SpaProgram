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

        $adminPassword = $this->normalizePassword(env('SPA_DEMO_ADMIN_PASSWORD'));
        $receptionPassword = $this->normalizePassword(env('SPA_DEMO_RECEPTION_PASSWORD'));
        $professionalPassword = $this->normalizePassword(env('SPA_DEMO_PROFESSIONAL_PASSWORD'));

        $adminUser = User::query()->where('email', $adminEmail)->first();
        $receptionUser = User::query()->where('email', $receptionEmail)->first();
        $professionalUser = User::query()->where('email', $professionalEmail)->first();

        $generatedAdminPassword = null;
        $generatedReceptionPassword = null;
        $generatedProfessionalPassword = null;

        if (! $adminUser && ! $adminPassword) {
            $generatedAdminPassword = Str::password(16);
            $adminPassword = $generatedAdminPassword;
        }
        if (! $receptionUser && ! $receptionPassword) {
            $generatedReceptionPassword = Str::password(16);
            $receptionPassword = $generatedReceptionPassword;
        }
        if (! $professionalUser && ! $professionalPassword) {
            $generatedProfessionalPassword = Str::password(16);
            $professionalPassword = $generatedProfessionalPassword;
        }

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
}
