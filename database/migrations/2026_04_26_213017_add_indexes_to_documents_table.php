<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->index('status');
            $table->index('current_office_id');
            $table->index('document_type_id');
            $table->index('user_id');
            $table->index('gestion_id');
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->index('document_id');
            $table->index('user_id');
            $table->index('from_office_id');
            $table->index('to_office_id');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['current_office_id']);
            $table->dropIndex(['document_type_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['gestion_id']);
        });

        Schema::table('movements', function (Blueprint $table) {
            $table->dropIndex(['document_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['from_office_id']);
            $table->dropIndex(['to_office_id']);
        });
    }
};
