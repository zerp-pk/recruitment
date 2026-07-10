<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'profile_media_id')) {
                $table->foreignId('profile_media_id')->nullable()->after('profile_path')
                    ->constrained('media')->nullOnDelete();
            }
            if (!Schema::hasColumn('candidates', 'resume_media_id')) {
                $table->foreignId('resume_media_id')->nullable()->after('resume_path')
                    ->constrained('media')->nullOnDelete();
            }
            if (!Schema::hasColumn('candidates', 'cover_letter_media_id')) {
                $table->foreignId('cover_letter_media_id')->nullable()->after('cover_letter_path')
                    ->constrained('media')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            foreach (['profile_media_id', 'resume_media_id', 'cover_letter_media_id'] as $column) {
                if (Schema::hasColumn('candidates', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }
        });
    }
};
