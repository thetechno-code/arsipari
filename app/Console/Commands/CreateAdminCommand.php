<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature   = 'arsipari:create-admin';
    protected $description = 'Buat akun Administrator baru secara interaktif melalui CLI';

    public function handle(): int
    {
        $this->info("==================================================");
        $this->info("  ARSIPARI - PEMBUATAN AKUN ADMINISTRATOR PERTAMA ");
        $this->info("==================================================");

        $name = $this->ask("Nama Lengkap Administrator:");
        while (empty(trim($name))) {
            $this->error("Nama tidak boleh kosong!");
            $name = $this->ask("Nama Lengkap Administrator:");
        }

        $email = $this->ask("Email Administrator:");
        $validator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);
        while ($validator->fails()) {
            $this->error($validator->errors()->first('email'));
            $email = $this->ask("Email Administrator:");
            $validator = Validator::make(['email' => $email], ['email' => 'required|email|unique:users,email']);
        }

        $password = $this->secret("Password (minimal 8 karakter):");
        while (strlen($password) < 8) {
            $this->error("Password minimal 8 karakter!");
            $password = $this->secret("Password (minimal 8 karakter):");
        }

        $departments = Department::all();
        $departmentId = null;
        if ($departments->isNotEmpty()) {
            $deptChoices = $departments->pluck('name', 'id')->toArray();
            $selectedDeptName = $this->choice("Pilih Unit / Bidang Kerja:", array_values($deptChoices), 0);
            $departmentId = $departments->firstWhere('name', $selectedDeptName)?->id;
        }

        $user = User::create([
            'name'          => trim($name),
            'email'         => trim(strtolower($email)),
            'password'      => Hash::make($password),
            'role'          => UserRole::ADMIN,
            'department_id' => $departmentId,
            'is_active'     => true,
        ]);

        $this->newLine();
        $this->info("🎉 AKUN ADMINISTRATOR BERHASIL DIBUAT!");
        $this->line("ID       : {$user->id}");
        $this->line("Nama     : {$user->name}");
        $this->line("Email    : {$user->email}");
        $this->line("Role     : " . $user->role_label);
        $this->line("Unit     : " . ($user->department?->name ?? '—'));
        $this->newLine();

        return Command::SUCCESS;
    }
}
