<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\ValidationException;
use App\Core\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testItReturnsTrimmedValidatedData(): void
    {
        $validated = Validator::validate(
            ['name' => '  Youssef  ', 'email' => 'youssef@example.com'],
            ['name' => 'required|min:3', 'email' => 'required|email']
        );

        $this->assertSame([
            'name' => 'Youssef',
            'email' => 'youssef@example.com',
        ], $validated);
    }

    public function testItRejectsMissingRequiredFields(): void
    {
        try {
            Validator::validate([], ['email' => 'required|email']);
            $this->fail('Expected validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame([
                'email' => ['Email is required.'],
            ], $exception->errors());
        }
    }

    public function testItRejectsInvalidEmail(): void
    {
        try {
            Validator::validate(['email' => 'not-an-email'], ['email' => 'required|email']);
            $this->fail('Expected validation to fail.');
        } catch (ValidationException $exception) {
            $this->assertSame([
                'email' => ['Email must be a valid email address.'],
            ], $exception->errors());
        }
    }

    public function testItAllowsOptionalEmptyFields(): void
    {
        $validated = Validator::validate(
            ['phone' => ''],
            ['phone' => 'min:10']
        );

        $this->assertSame([], $validated);
    }
}
