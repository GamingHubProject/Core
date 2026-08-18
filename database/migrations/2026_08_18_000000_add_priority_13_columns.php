<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * supported_features is a per-server, per-poll declaration of which
 * Pelican Client API endpoints this server can actually answer (backups/
 * databases gated by feature_limits, everything else universal) — kept
 * distinct from the existing Capability/CapabilityGateway system, which
 * means something unrelated (a data domain resolved through the Provider
 * stack, e.g. "server-status").
 *
 * last_raw_response holds the latest unsanitized connector payload for
 * exactly one Provider — overwritten each poll, not appended, since
 * there's no stated need for history and an unbounded log would grow
 * forever. Admin-only by construction: it's only ever read from the
 * already-authenticated Filament panel, never from any public route.
 *
 * polling_cadence_seconds lets a Provider refresh slower than its
 * ConnectorInstance's own poll_interval_seconds allows (never faster —
 * the instance-level interval is a ceiling shared by every Provider that
 * uses it). Minimum of 5 is enforced in the admin form, not here, since a
 * DB CHECK constraint would need to be duplicated across every driver
 * this app might run on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->json('supported_features')->nullable();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->json('last_raw_response')->nullable();
            $table->unsignedInteger('polling_cadence_seconds')->default(30);
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('supported_features');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['last_raw_response', 'polling_cadence_seconds']);
        });
    }
};
