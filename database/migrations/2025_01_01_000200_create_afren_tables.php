<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('iso2', 2)->unique();
            $t->string('dial_code', 8);
            $t->string('currency', 3);
        });

        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });

        Schema::create('tax_rules', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('country_code', 2)->nullable(); // null = default rule
            $t->decimal('rate', 5, 2);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('scoring_criteria', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('label');
            $t->string('description')->nullable();
            $t->unsignedSmallInteger('weight')->default(10);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort')->default(0);
        });

        Schema::create('professionals', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete(); // login account
            $t->string('full_name');
            $t->string('email')->unique();
            $t->string('dial_code', 8);
            $t->string('phone', 30);
            $t->string('country_code', 2)->index();
            $t->string('location')->nullable();
            $t->string('category')->index(); // researcher|enumerator|both
            $t->string('education_level')->default('none');
            $t->string('field_of_study')->nullable();
            $t->text('qualifications')->nullable();
            $t->unsignedTinyInteger('years_experience')->default(0);
            $t->unsignedTinyInteger('research_years')->default(0);
            $t->unsignedTinyInteger('fieldwork_years')->default(0);
            $t->text('experience_summary')->nullable();
            $t->json('skills')->nullable();
            $t->json('tools')->nullable();
            $t->json('languages')->nullable();
            $t->text('certifications')->nullable();
            $t->string('availability')->default('available'); // available|limited|unavailable
            $t->boolean('willing_to_travel')->default(false);
            $t->json('preferred_countries')->nullable();
            $t->string('cv_path')->nullable();
            $t->json('documents')->nullable();
            $t->string('verification_status')->default('pending')->index(); // pending|verified|rejected
            $t->timestamp('verified_at')->nullable();
            $t->decimal('profile_score', 5, 2)->default(0)->index();
            $t->json('score_breakdown')->nullable();
            $t->timestamp('consent_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->text('admin_notes')->nullable();
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('tagline')->nullable();
            $t->text('description')->nullable();
            $t->decimal('price', 12, 2);
            $t->string('currency', 3);
            $t->string('billing_interval')->default('monthly');
            $t->json('features')->nullable();
            $t->unsignedInteger('max_requests_per_month')->nullable(); // null = not capped
            $t->unsignedInteger('max_candidates_per_request')->nullable();
            $t->boolean('is_active')->default(true);
            $t->boolean('is_featured')->default(false);
            $t->unsignedSmallInteger('sort')->default(0);
            $t->timestamps();
        });

        Schema::create('organizations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete(); // login account
            $t->string('name');
            $t->string('type')->nullable();
            $t->string('country_code', 2)->nullable();
            $t->string('location')->nullable();
            $t->string('contact_name');
            $t->string('email')->index();
            $t->string('dial_code', 8)->nullable();
            $t->string('phone', 30)->nullable();
            $t->string('website')->nullable();
            $t->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $t->foreignId('package_id')->constrained();
            $t->foreignId('pending_package_id')->nullable()->constrained('packages');
            $t->string('status')->default('pending')->index(); // pending|active|past_due|cancelled|expired
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('renews_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->boolean('cancel_at_period_end')->default(false);
            $t->timestamp('cancelled_at')->nullable();
            $t->timestamps();
        });

        Schema::create('subscription_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $t->string('event');
            $t->string('details')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });

        Schema::create('recruitment_requests', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $t->string('source')->default('public'); // public|subscriber
            $t->string('status')->default('new')->index();
            $t->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            // organization
            $t->string('org_name');
            $t->string('org_type');
            $t->string('org_country', 2);
            $t->string('org_location')->nullable();
            $t->string('contact_name');
            $t->string('contact_email');
            $t->string('contact_dial_code', 8);
            $t->string('contact_phone', 30);
            $t->string('website')->nullable();
            // project
            $t->string('project_title');
            $t->text('project_description');
            $t->text('objectives');
            $t->string('sector');
            $t->json('project_countries');
            $t->text('project_locations')->nullable();
            $t->string('duration')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->date('recruitment_deadline')->nullable();
            // personnel
            $t->string('personnel_type'); // researcher|enumerator|both
            $t->unsignedInteger('headcount');
            $t->text('qualifications_required')->nullable();
            $t->string('min_education')->default('none');
            $t->unsignedTinyInteger('min_years')->default(0);
            $t->text('experience_required')->nullable();
            $t->text('fieldwork_required')->nullable();
            $t->json('technical_skills')->nullable();
            $t->json('languages')->nullable();
            $t->json('tools')->nullable();
            $t->text('local_knowledge')->nullable();
            $t->boolean('travel_required')->default(false);
            $t->text('availability_requirements')->nullable();
            $t->text('additional_criteria')->nullable();
            // commercial
            $t->decimal('budget_min', 14, 2)->nullable();
            $t->decimal('budget_max', 14, 2)->nullable();
            $t->string('budget_currency', 3)->nullable();
            $t->string('arrangement')->nullable();
            $t->text('additional_services')->nullable();
            $t->string('document_path')->nullable();
            $t->timestamp('consent_at')->nullable();
            $t->text('admin_notes')->nullable();
            $t->timestamps();
        });

        Schema::create('shortlists', function (Blueprint $t) {
            $t->id();
            $t->foreignId('recruitment_request_id')->constrained()->cascadeOnDelete();
            $t->foreignId('professional_id')->constrained()->cascadeOnDelete();
            $t->decimal('job_score', 5, 2)->default(0);
            $t->string('status')->default('shortlisted'); // shortlisted|contacted|accepted|declined|hired
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->unique(['recruitment_request_id', 'professional_id']);
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('number')->nullable()->unique();
            $t->string('type')->index(); // subscription|direct
            $t->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('recruitment_request_id')->nullable()->constrained()->nullOnDelete();
            $t->string('bill_to_name');
            $t->string('bill_to_email');
            $t->string('bill_to_country', 2)->nullable();
            $t->string('currency', 3);
            $t->decimal('subtotal', 14, 2)->default(0);
            $t->decimal('tax_rate', 5, 2)->default(0);
            $t->decimal('tax_total', 14, 2)->default(0);
            $t->decimal('total', 14, 2)->default(0);
            $t->date('issue_date')->nullable();
            $t->date('due_date')->nullable();
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->string('status')->default('draft')->index(); // draft|sent|paid|void
            $t->text('notes')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->string('description');
            $t->decimal('quantity', 10, 2)->default(1);
            $t->decimal('unit_price', 14, 2);
            $t->decimal('amount', 14, 2);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 14, 2);
            $t->string('method');
            $t->string('reference')->nullable();
            $t->date('paid_on');
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('organization')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->string('status')->default('new'); // new|handled
            $t->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action');
            $t->string('subject_type')->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->json('meta')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages','audit_logs','payments','invoice_items','invoices','shortlists','recruitment_requests','subscription_events','subscriptions','organizations','packages','professionals','scoring_criteria','tax_rules','settings','countries'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
