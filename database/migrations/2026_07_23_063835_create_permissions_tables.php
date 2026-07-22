<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('group', 50);
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role', 50);
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role', 'permission_id']);
        });

        // Seed default permissions
        $permissions = [
            // Master Data (admin + bk default)
            ['categories-manage', 'master-data', 'Kelola Kategori Pelanggaran'],
            ['violation-types-manage', 'master-data', 'Kelola Jenis Pelanggaran'],
            ['thresholds-manage', 'master-data', 'Kelola Ambang SP'],
            ['violations-export', 'master-data', 'Export Data Pelanggaran'],

            // Administrasi (admin only default)
            ['settings-manage', 'administrasi', 'Pengaturan Aplikasi'],
            ['users-manage', 'administrasi', 'Manajemen User'],
            ['sync-data', 'administrasi', 'Sinkronisasi Data'],
            ['backup-database', 'administrasi', 'Backup Database'],
            ['reset-application', 'administrasi', 'Reset Aplikasi'],
            ['import-data', 'administrasi', 'Import Data'],
            ['export-master', 'administrasi', 'Export Master Data'],
        ];

        $now = now();
        foreach ($permissions as [$key, $group, $label]) {
            DB::table('permissions')->insert([
                'key' => $key,
                'group' => $group,
                'label' => $label,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Default: admin gets all permissions, bk gets master-data
        $adminRole = 'admin';
        $bkRole = 'bk';
        $masterKeys = ['categories-manage', 'violation-types-manage', 'thresholds-manage', 'violations-export'];

        foreach ($permissions as [$key, $group, $label]) {
            DB::table('role_permissions')->insert([
                'role' => $adminRole,
                'permission_id' => DB::table('permissions')->where('key', $key)->value('id'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if (in_array($key, $masterKeys)) {
                DB::table('role_permissions')->insert([
                    'role' => $bkRole,
                    'permission_id' => DB::table('permissions')->where('key', $key)->value('id'),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
