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
        //  Schema::create('subscription_plans', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name', 100);
        //     $table->unsignedMediumInteger('price');
        //     $table->unsignedSmallInteger('duration_value');
        //     $table->enum('duration_unit', ['days', 'months', 'years']);
        //     $table->softDeletes();
        //     $table->timestamps();
        // });

        // Schema::create('subscriptions', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('user_id')
        //         ->constrained()
        //         ->cascadeOnDelete();
        //     $table->foreignId('subscription_plan_id')
        //         ->constrained()
        //         ->cascadeOnDelete();
        //     $table->dateTime('starts_at');
        //     $table->dateTime('ends_at');
        //     $table->dateTime('cancelled_at')->nullable();
        //     $table->timestamps();
        // });

        // Schema::create('newspapers', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name', 80);
        //     $table->unsignedMediumInteger('price');
        //     $table->softDeletes();
        //     $table->timestamps();
        // });

        // Schema::create('newspaper_items', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('newspaper_id')
        //     ->constrained()
        //     ->cascadeOnDelete();
        //     $table->string('name', 80);
        //     $table->text('details');
        //     $table->boolean('virtual')->default(false);
        //     $table->json('metadata')->nullable();
        //     $table->softDeletes();
        //     $table->timestamps();
        // });

        // Schema::create('newspaper_subscription_plan', function (Blueprint $table) {
        //     $table->foreignId('newspaper_id')
        //         ->constrained()
        //         ->cascadeOnDelete();

        //     $table->foreignId('subscription_plan_id')
        //         ->constrained()
        //         ->cascadeOnDelete();

        //     $table->primary([
        //         'newspaper_id',
        //         'subscription_plan_id',
        //     ]);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
