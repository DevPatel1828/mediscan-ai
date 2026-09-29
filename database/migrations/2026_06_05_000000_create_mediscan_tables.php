<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Permissions
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Role User Pivot
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });

        // 4. Permission Role Pivot
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // 5. Prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->integer('credits_spent')->default(20);
            $table->timestamps();
        });

        // 6. Prescription Results
        Schema::create('prescription_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->onDelete('cascade');
            $table->longText('raw_ocr_text')->nullable();
            $table->longText('extracted_json')->nullable(); // JSON data stored as text
            $table->float('confidence_score')->default(0.0);
            $table->string('language')->default('English');
            $table->timestamps();
        });

        // 7. Medicines
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->text('composition')->nullable();
            $table->text('uses')->nullable();
            $table->text('dosage')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('warnings')->nullable();
            $table->text('pregnancy_safety')->nullable();
            $table->text('storage_conditions')->nullable();
            $table->string('manufacturer')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('category')->nullable();
            $table->text('food_interactions')->nullable();
            $table->text('alcohol_interactions')->nullable();
            $table->timestamps();

            // Indexes for fast searching
            $table->index('medicine_name');
            $table->index('generic_name');
            $table->index('brand_name');
        });

        // 8. Medicine Interactions
        Schema::create('medicine_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id_1')->constrained('medicines')->onDelete('cascade');
            $table->foreignId('medicine_id_2')->constrained('medicines')->onDelete('cascade');
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->string('severity')->nullable();
            $table->text('description')->nullable();
            $table->text('recommended_action')->nullable();
            $table->timestamps();
        });

        // 9. Translations
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('language_code');
            $table->longText('translated_content');
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id']);
        });

        // 10. Voice Records
        Schema::create('voice_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('text_content')->nullable();
            $table->string('voice_file_path');
            $table->string('language_code')->default('en');
            $table->timestamps();
        });

        // 11. Credit Transactions
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // e.g. -20, +5000
            $table->string('action_type'); // scan, search, translation, voice, chat, export, daily_reset
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 12. Subscriptions
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_name'); // Free, Premium
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 13. Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        // 14. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamps();
        });

        // 15. Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // 16. Chat Histories
        Schema::create('chat_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->text('response')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->timestamps();
        });

        // 17. Saved Medicines
        Schema::create('saved_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 18. Reports
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained()->onDelete('set null');
            $table->string('report_type'); // pdf, csv, xlsx
            $table->string('file_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('saved_medicines');
        Schema::dropIfExists('chat_histories');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('voice_records');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('medicine_interactions');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('prescription_results');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
