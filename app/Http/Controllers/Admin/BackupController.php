<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BackupController extends Controller
{
    protected string $backupDir = 'backups';

    public function index(): View
    {
        abort_unless(auth()->user()?->role === 'admin', 403, 'Hanya admin yang dapat mengakses fitur backup.');

        $backups = collect();

        if (Storage::disk('local')->exists($this->backupDir)) {
            $files = Storage::disk('local')->files($this->backupDir);

            $backups = collect($files)
                ->filter(fn($f) => str_ends_with($f, '.sql.gz'))
                ->map(function ($path) {
                    $fullPath = Storage::disk('local')->path($path);
                    return (object) [
                        'filename' => basename($path),
                        'path' => $path,
                        'size' => $this->formatBytes(filesize($fullPath)),
                        'size_bytes' => filesize($fullPath),
                        'date' => date('Y-m-d H:i:s', filemtime($fullPath)),
                        'timestamp' => filemtime($fullPath),
                    ];
                })
                ->sortByDesc('timestamp')
                ->values();
        }

        $dbSize = $this->getDatabaseSize();
        $stats = [
            'total_backups' => $backups->count(),
            'latest_backup' => $backups->first()?->date ?? 'Belum pernah',
            'total_size' => $this->formatBytes($backups->sum('size_bytes')),
            'db_size' => $dbSize,
        ];

        return view('settings.backup', compact('backups', 'stats'));
    }

    public function create(): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $db = config('database.connections.mysql');
        $filename = 'aplikasi_ketertiban_' . now()->format('Y-m-d_His') . '.sql.gz';
        $outputPath = Storage::disk('local')->path($this->backupDir . '/' . $filename);

        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s --routines --events --single-transaction --quick | gzip > %s 2>&1',
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['database']),
            escapeshellarg($outputPath)
        );

        $output = null;
        $exitCode = null;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath)) {
            return redirect()->route('settings.backup')
                ->with('error', 'Backup gagal: ' . implode("\n", $output));
        }

        return redirect()->route('settings.backup')
            ->with('success', 'Backup berhasil: ' . $filename);
    }

    public function download(string $filename)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $path = $this->backupDir . '/' . basename($filename);

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return Storage::disk('local')->download($path, $filename);
    }

    public function destroy(string $filename)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $path = $this->backupDir . '/' . basename($filename);

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('settings.backup')
                ->with('error', 'File backup tidak ditemukan.');
        }

        Storage::disk('local')->delete($path);

        return redirect()->route('settings.backup')
            ->with('success', 'Backup ' . $filename . ' berhasil dihapus.');
    }

    public function restore(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $filename = basename($request->input('filename'));
        $path = $this->backupDir . '/' . $filename;

        if (!Storage::disk('local')->exists($path)) {
            return redirect()->route('settings.backup')
                ->with('error', 'File backup tidak ditemukan.');
        }

        $fullPath = Storage::disk('local')->path($path);
        $db = config('database.connections.mysql');

        // Backup otomatis sebelum restore
        $autoBackup = 'pre_restore_' . now()->format('Y-m-d_His') . '.sql.gz';
        $autoPath = Storage::disk('local')->path($this->backupDir . '/' . $autoBackup);
        exec(sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s --routines --events --single-transaction --quick | gzip > %s',
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['database']),
            escapeshellarg($autoPath)
        ));

        // Restore
        $command = sprintf(
            'gunzip -c %s | mysql --host=%s --port=%s --user=%s --password=%s %s 2>&1',
            escapeshellarg($fullPath),
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['database'])
        );

        $output = null;
        $exitCode = null;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return redirect()->route('settings.backup')
                ->with('error', 'Restore gagal: ' . implode("\n", $output));
        }

        return redirect()->route('settings.backup')
            ->with('success', 'Database berhasil direstore dari ' . $filename . '. Backup otomatis sebelum restore: ' . $autoBackup);
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getDatabaseSize(): string
    {
        $db = config('database.connections.mysql');
        $command = sprintf(
            "mysql --host=%s --port=%s --user=%s --password=%s -e \"SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = '%s'\" -sN 2>/dev/null",
            escapeshellarg($db['host']),
            escapeshellarg($db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['database'])
        );
        $size = trim(shell_exec($command));
        return $size ? $size . ' MB' : '—';
    }
}
