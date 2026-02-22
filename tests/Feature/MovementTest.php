<?php

use App\Enum\MovementStatus;
use App\Models\Movement;
use App\Models\Office;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    \Gate::before(function () {
        return true;
    });
});

it('movement belongs to origin office', function () {
    $movement = Movement::factory()->create();

    expect($movement->originOffice)->toBeInstanceOf(Office::class);
});

it('movement belongs to destination office', function () {
    $movement = Movement::factory()->create();

    expect($movement->destinationOffice)->toBeInstanceOf(Office::class);
});

it('movement belongs to origin user', function () {
    $movement = Movement::factory()->create();

    expect($movement->originUser)->toBeInstanceOf(User::class);
});

it('movement belongs to destination user', function () {
    $movement = Movement::factory()->create();

    expect($movement->destinationUser)->toBeInstanceOf(User::class);
});

it('movement can have pending status', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::PENDING]);

    expect($movement->status)->toBe(MovementStatus::PENDING);
});

it('movement can have completed status', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::COMPLETED]);

    expect($movement->status)->toBe(MovementStatus::COMPLETED);
});

it('movement can have rejected status', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::REJECTED]);

    expect($movement->status)->toBe(MovementStatus::REJECTED);
});

it('movement isActive returns true for pending', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::PENDING]);

    expect($movement->status->isActive())->toBeTrue();
});

it('movement isActive returns false for completed', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::COMPLETED]);

    expect($movement->status->isActive())->toBeFalse();
});

it('movement isFinished returns true for completed', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::COMPLETED]);

    expect($movement->status->isFinished())->toBeTrue();
});

it('movement isFinished returns false for pending', function () {
    $movement = Movement::factory()->create(['status' => MovementStatus::PENDING]);

    expect($movement->status->isFinished())->toBeFalse();
});
