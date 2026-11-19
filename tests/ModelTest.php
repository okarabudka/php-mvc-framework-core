<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use InvalidArgumentException;
use Okarabudka\PhpMvcCore\Model;
use Okarabudka\PhpMvcCore\Tests\Fixtures\User;

final class ModelTest extends TestCase
{
    public function testValidModelHasNoErrors(): void
    {
        $this->createApplication();
        $user = $this->user(['email' => 'ann@example.com', 'name' => 'Ann']);

        self::assertTrue($user->validate());
        self::assertSame([], $user->errors);
    }

    public function testRequired(): void
    {
        $this->createApplication();
        $user = $this->user(['email' => '', 'name' => '']);

        self::assertFalse($user->validate());
        self::assertSame('This field is required', $user->getFirstError('email'));
        self::assertSame('This field is required', $user->getFirstError('name'));
    }

    public function testEmail(): void
    {
        $this->createApplication();
        $user = $this->user(['email' => 'not-an-email', 'name' => 'Ann']);

        self::assertFalse($user->validate());
        self::assertSame(['This field must be a valid email address'], $user->errors['email']);
    }

    public function testMinAndMaxReplacePlaceholders(): void
    {
        $this->createApplication();

        $short = $this->user(['email' => 'a@b.cd', 'name' => 'A']);
        self::assertFalse($short->validate());
        self::assertSame('Min length of this field must be 2', $short->getFirstError('name'));

        $long = $this->user(['email' => 'a@b.cd', 'name' => 'Bartholomew']);
        self::assertFalse($long->validate());
        self::assertSame('Max length of this field must be 10', $long->getFirstError('name'));
    }

    public function testMatchUsesTheOtherFieldsLabel(): void
    {
        $this->createApplication();
        $user = $this->user(['email' => 'a@b.cd', 'name' => 'Ann', 'password' => 'secret1', 'confirmPassword' => 'secret2']);

        self::assertFalse($user->validate());
        self::assertSame('This field must be the same as Password', $user->getFirstError('confirmPassword'));
    }

    public function testUniqueChecksTheTable(): void
    {
        $this->createApplication();
        $this->createUser('taken@example.com');

        $user = $this->user(['email' => 'taken@example.com', 'name' => 'Ann']);

        self::assertFalse($user->validate());
        self::assertSame('Record with this Email already exists', $user->getFirstError('email'));
        self::assertTrue($this->user(['email' => 'free@example.com', 'name' => 'Ann'])->validate());
    }

    public function testUniqueNeedsADbModelClass(): void
    {
        $model = new class extends Model {
            public string $code = 'x';

            public function rules(): array
            {
                return ['code' => [[self::RULE_UNIQUE, 'class' => \stdClass::class]]];
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $model->validate();
    }

    public function testLoadDataIgnoresUnknownAndInternalKeys(): void
    {
        $user = new User();
        $user->loadData(['email' => 'a@b.cd', 'unknown' => 'x', 'errors' => 'oops']);

        self::assertSame('a@b.cd', $user->email);
        self::assertSame([], $user->errors);
        self::assertArrayNotHasKey('unknown', get_object_vars($user));
    }

    public function testLoadDataConvertsScalarsAndSkipsWrongTypes(): void
    {
        $user = new User();
        $user->loadData(['status' => '3', 'id' => 'abc', 'name' => ['array'], 'email' => 42]);

        self::assertSame(3, $user->status);
        self::assertSame(0, $user->id);
        self::assertSame('', $user->name);
        self::assertSame('42', $user->email);
    }

    public function testErrorHelpers(): void
    {
        $user = new User();

        self::assertFalse($user->hasError('email'));
        self::assertSame('', $user->getFirstError('email'));

        $user->addError('email', 'first');
        $user->addError('email', 'second');

        self::assertTrue($user->hasError('email'));
        self::assertSame('first', $user->getFirstError('email'));
        self::assertSame('Email', $user->getLabel('email'));
        self::assertSame('name', $user->getLabel('name'));
    }

    /**
     * @param array<string, string> $data
     */
    private function user(array $data): User
    {
        $user = new User();
        $user->loadData($data);

        return $user;
    }
}
