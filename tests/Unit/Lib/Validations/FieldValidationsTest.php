<?php

namespace Tests\Unit\Lib\Validations;

use App\Models\Production;
use App\Models\User;
use Lib\Validations;
use PHPUnit\Framework\TestCase;

class FieldValidationsTest extends TestCase
{
    public function test_email_should_accept_a_valid_email(): void
    {
        $user = new User(['email' => 'fulano@example.com']);

        $this->assertTrue(Validations::email('email', $user));
        $this->assertNull($user->errors('email'));
    }

    public function test_email_should_reject_an_invalid_email(): void
    {
        $user = new User(['email' => 'fulano.com']);

        $this->assertFalse(Validations::email('email', $user));
        $this->assertEquals('deve ser um e-mail válido!', $user->errors('email'));
    }

    public function test_positive_integer_should_accept_numbers_greater_than_zero(): void
    {
        $production = new Production(['quantity' => '5']);

        $this->assertTrue(Validations::positiveInteger('quantity', $production));
    }

    public function test_positive_integer_should_reject_zero_decimals_and_text(): void
    {
        $values = ['0', '-3', '2.5', 'abc', ''];

        foreach ($values as $value) {
            $production = new Production(['quantity' => $value]);

            $this->assertFalse(Validations::positiveInteger('quantity', $production));
            $this->assertEquals('deve ser um número inteiro maior que zero!', $production->errors('quantity'));
        }
    }

    public function test_positive_number_should_accept_decimals_greater_than_zero(): void
    {
        $production = new Production(['price_per_kg' => '45.90']);

        $this->assertTrue(Validations::positiveNumber('price_per_kg', $production));
    }

    public function test_positive_number_should_reject_zero_negative_and_text(): void
    {
        $values = ['0', '-1', 'abc', ''];

        foreach ($values as $value) {
            $production = new Production(['price_per_kg' => $value]);

            $this->assertFalse(Validations::positiveNumber('price_per_kg', $production));
            $this->assertEquals('deve ser um valor maior que zero!', $production->errors('price_per_kg'));
        }
    }

    public function test_date_should_accept_a_valid_date(): void
    {
        $production = new Production(['production_date' => '2026-11-01']);

        $this->assertTrue(Validations::date('production_date', $production));
    }

    public function test_date_should_reject_invalid_dates(): void
    {
        $values = ['2026-02-30', '01/11/2026', 'amanhã', ''];

        foreach ($values as $value) {
            $production = new Production(['production_date' => $value]);

            $this->assertFalse(Validations::date('production_date', $production));
            $this->assertEquals('deve ser uma data válida!', $production->errors('production_date'));
        }
    }
}
