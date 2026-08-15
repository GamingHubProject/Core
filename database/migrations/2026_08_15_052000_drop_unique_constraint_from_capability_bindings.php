<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A (capability, subject) pair can now have more than one binding — see
 * CapabilityRouter::findBindings() and CapabilityGateway::probe(). The old
 * uniqueness assumption (one binding per pair) no longer holds now that
 * several providers can each contribute different fields to the same
 * capability, merged by priority.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->dropUnique('capability_bindings_subject_unique');
            $table->index(['capability', 'subject_type', 'subject_id'], 'capability_bindings_subject_index');
        });
    }

    public function down(): void
    {
        Schema::table('capability_bindings', function (Blueprint $table) {
            $table->dropIndex('capability_bindings_subject_index');
            $table->unique(['capability', 'subject_type', 'subject_id'], 'capability_bindings_subject_unique');
        });
    }
};
