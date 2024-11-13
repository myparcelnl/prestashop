<?php

/** @noinspection NullPointerExceptionInspection,PhpUnhandledExceptionInspection,PhpIllegalPsrClassPathInspection,AutoloadingIssuesInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\PrestaShop\Service;

use Customer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\PrestaShop\Tests\Uses\UsesMockPsPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\PrestaShop\psFactory;

usesShared(new UsesMockPsPdkInstance());

/**
 * @template T of Customer
 * @private
 */
class MockPsCustomerService extends PsSpecificObjectModelService
{
    protected function getClass(): string
    {
        return Customer::class;
    }
}

it('adds and gets a model', function () {
    $service = Pdk::get(MockPsCustomerService::class);

    $model = psFactory(Customer::class)
        ->withFirstname('Boer')
        ->withLastname('Harms')
        ->make();

    // Add a new model
    $success = $service->add($model);

    expect($success)->toBeTrue();

    // Get model by model
    $result = $service->get($model);

    expect($result)
        ->toBeInstanceOf(Customer::class)
        ->and($result->firstname)
        ->toBe('Boer')
        ->and($result->lastname)
        ->toBe('Harms');

    // Get model by id
    $resultById = $service->get($result->id);

    expect($resultById)
        ->toBeInstanceOf(Customer::class)
        ->and($resultById->firstname)
        ->toBe('Boer')
        ->and($resultById->lastname)
        ->toBe('Harms');
});

it('deletes a model', function () {
    $service = Pdk::get(MockPsCustomerService::class);
    $model   = psFactory(Customer::class)->make();

    $service->add($model);

    // Delete model by model
    $result = $service->delete($model);

    expect($result)->toBeTrue();

    // Try to get just deleted model
    $result = $service->get($model->id);

    expect($result)->toBeNull();
});

it('soft deletes a model', function () {
    $service = Pdk::get(MockPsCustomerService::class);
    $model   = psFactory(Customer::class)->make();

    $service->add($model);

    // Delete model by model
    $result = $service->delete($model, true);

    expect($result)->toBeTrue();

    // Try to get just deleted model
    $result = $service->get($model->id);

    expect($result)->toBeNull();
});

it('updates a model', function () {
    $service = Pdk::get(MockPsCustomerService::class);
    $model   = psFactory(Customer::class)->make();

    $service->add($model);

    $result = $service->get($model->id);

    $result->firstname = 'Boer';
    $result->lastname  = 'Harms';

    $service->update($result);

    $updatedResult = $service->get($model->id);

    expect($updatedResult)
        ->toBeInstanceOf(Customer::class)
        ->and($updatedResult->firstname)
        ->toBe('Boer')
        ->and($updatedResult->lastname)
        ->toBe('Harms');
});

