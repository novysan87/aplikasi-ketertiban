<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->string('handling_status', 20)->default('unhandled')->after('recorded_by')->comment('unhandled|in_progress|resolved');
            $table->timestamp('handled_at')->nullable()->after('handling_status');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete()->after('handled_at');
        });
    }

    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handled_by');
            $table->dropColumn(['handling_status', 'handled_at']);
        });
    }
};
