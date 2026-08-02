<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Violation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id', 'student_class', 'violation_type_id', 'recorded_by',
        'description', 'points', 'sanction', 'location',
        'violation_date', 'violation_time',
        'is_verified', 'verified_by', 'verified_at',
        'notes', 'handling_status', 'handled_at', 'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'violation_date' => 'date',
            'violation_time' => 'datetime:H:i',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'handled_at' => 'datetime',
            'points' => 'integer',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function violationType(): BelongsTo
    {
        return $this->belongsTo(ViolationType::class, 'violation_type_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(ViolationEvidence::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ViolationNotification::class);
    }

    /**
     * Bangun notifikasi WA untuk orang tua/wali.
     *
     * @return array{0: string, 1: string}|null  [nomor_wa, pesan] — null bila HP ortu belum diisi
     */
    public function buildWaNotification(): ?array
    {
        $student = $this->student;
        if (! $student || ! $student->parent_phone) {
            return null;
        }

        // Normalisasi nomor Indonesia: 08xx / 8xx → 628xx
        $phone = preg_replace('/\D/', '', $student->parent_phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }
        if (strlen($phone) < 10) {
            return null;
        }

        $template = trim((string) \App\Models\Setting::getValue('wa_notification_template', ''));
        if ($template === '') {
            $template = "Yth. Bapak/Ibu Wali dari {nama_siswa} ({kelas}),\n\n"
                ."Dengan hormat, kami informasikan bahwa ananda tercatat melakukan pelanggaran:\n"
                ."• Jenis: {jenis}\n"
                ."• Tanggal: {tanggal}\n"
                ."• Poin: {poin}\n\n"
                ."Total poin ananda saat ini: {total_poin}.\n\n"
                ."Demikian disampaikan, atas perhatian dan kerja samanya kami ucapkan terima kasih.\n\n"
                ."Tim Ketertiban\n{sekolah}";
        }

        $placeholders = [
            '{nama_siswa}' => $student->full_name,
            '{nisn}' => $student->nisn,
            '{kelas}' => $student->class_name,
            '{jenis}' => $this->violationType?->name ?? '-',
            '{tanggal}' => $this->violation_date ? $this->violation_date->format('d/m/Y') : '-',
            '{waktu}' => $this->violation_time ? $this->violation_time->format('H:i') : '-',
            '{poin}' => (string) $this->points,
            '{total_poin}' => (string) ($student->total_points ?? 0),
            '{lokasi}' => $this->location ?: '-',
            '{deskripsi}' => $this->description ?: '-',
            '{sekolah}' => (string) \App\Models\Setting::getValue('school_name', 'SMKN 1 Wonorejo'),
        ];

        return [$phone, strtr($template, $placeholders)];
    }

    public function handlings(): HasMany
    {
        return $this->hasMany(ViolationHandling::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function isUnhandled(): bool
    {
        return $this->handling_status === 'unhandled';
    }

    public function isInProgress(): bool
    {
        return $this->handling_status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->handling_status === 'resolved';
    }
}
