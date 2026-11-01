<?php

namespace Tests\Unit\Models\Customers;

use App\Models\Customer;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    private Customer $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = new Customer(['name' => 'Maria', 'phone' => '42999990000']);
        $this->customer->save();
    }

    public function test_should_create_new_customer(): void
    {
        $this->assertCount(1, Customer::all());
        $this->assertGreaterThan(0, $this->customer->id);
    }

    public function test_should_not_create_customer_without_name(): void
    {
        $customer = new Customer(['phone' => '42999991111']);

        $this->assertFalse($customer->save());
        $this->assertEquals('não pode ser vazio!', $customer->errors('name'));
    }

    public function test_should_not_create_customer_without_phone(): void
    {
        $customer = new Customer(['name' => 'João']);

        $this->assertFalse($customer->save());
        $this->assertEquals('não pode ser vazio!', $customer->errors('phone'));
    }

    public function test_should_not_create_customer_with_invalid_phone(): void
    {
        $customer = new Customer(['name' => 'João', 'phone' => '(42) 9999-1111']);

        $this->assertFalse($customer->save());
        $this->assertEquals('deve ter 10 ou 11 números, com DDD!', $customer->errors('phone'));
    }

    public function test_should_not_create_customer_with_duplicated_phone(): void
    {
        $customer = new Customer(['name' => 'João', 'phone' => '42999990000']);

        $this->assertFalse($customer->save());
        $this->assertEquals('já existe um registro com esse dado', $customer->errors('phone'));
        $this->assertCount(1, Customer::all());
    }

    public function test_should_update_customer(): void
    {
        $this->customer->name = 'Maria Silva';

        $this->assertTrue($this->customer->save());
        $this->assertEquals('Maria Silva', Customer::findById($this->customer->id)->name);
    }

    public function test_destroy_should_remove_the_customer(): void
    {
        $this->customer->destroy();

        $this->assertCount(0, Customer::all());
    }
}
