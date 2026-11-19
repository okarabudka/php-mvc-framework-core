<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore\Tests;

use Okarabudka\PhpMvcCore\Form\Form;
use Okarabudka\PhpMvcCore\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

final class FormTest extends TestCase
{
    public function testBeginPrintsOpeningTagAndEndClosesIt(): void
    {
        $this->expectOutputString('<form action="/login?a=1&amp;b=2" method="post">');

        $form = Form::begin('/login?a=1&b=2', 'post');

        self::assertSame('</form>', Form::end());
        self::assertSame('email', $form->field(new User(), 'email')->attribute);
    }

    public function testInputFieldRendersLabelValueAndNoError(): void
    {
        $user = new User();
        $user->email = 'ann@example.com';

        $html = (string) new Form()->field($user, 'email');

        self::assertStringContainsString('<label class="form-label">Email</label>', $html);
        self::assertStringContainsString('<input type="text" name="email" value="ann@example.com" class="form-control">', $html);
    }

    public function testInputFieldShowsFirstErrorAndInvalidClass(): void
    {
        $user = new User();
        $user->addError('email', 'Wrong <email>');

        $html = (string) new Form()->field($user, 'email');

        self::assertStringContainsString('class="form-control is-invalid"', $html);
        self::assertStringContainsString('Wrong &lt;email&gt;', $html);
    }

    public function testPasswordField(): void
    {
        $user = new User();

        $html = new Form()->field($user, 'password')->passwordField()->renderInput();

        self::assertStringStartsWith('<input type="password" name="password"', $html);
    }

    public function testValuesAreEscaped(): void
    {
        $user = new User();
        $user->name = '"><script>alert(1)</script>';

        $html = new Form()->field($user, 'name')->renderInput();

        self::assertStringContainsString('value="&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    public function testTextareaKeepsValue(): void
    {
        $user = new User();
        $user->name = "line 1\n<line 2>";
        $user->addError('name', 'Too long');

        $html = new Form()->textareaField($user, 'name')->renderInput();

        self::assertSame("<textarea name=\"name\" class=\"form-control is-invalid\">line 1\n&lt;line 2&gt;</textarea>", $html);
    }
}
