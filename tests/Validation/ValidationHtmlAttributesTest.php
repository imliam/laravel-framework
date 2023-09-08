<?php

namespace Illuminate\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\HtmlValidationRule;
use Illuminate\Validation\HtmlValidator;
use Illuminate\Validation\Rules\Password;

class ValidationHtmlAttributesTest extends TestCase
{
    public function testHtmlObjectRule()
    {
        $rule = new FakeValidationRule();
        $this->assertEquals('foo="bar"', (new HtmlValidator)->toHtmlAttributeString([$rule]));
    }

    public function testHtmlPasswordRule()
    {
        $rule = (new Password(6))
            ->letters()
            ->numbers()
            ->symbols();

        $this->assertEquals('passwordrules="minlength: 6; required: digit; required: letters; required: special;" minlength="6"', (new HtmlValidator)->toHtmlAttributeString([$rule]));
    }

    public function testHtmlAcceptedRule()
    {
        $this->assertEquals('pattern="(yes|on|1|true)"', (new HtmlValidator)->toHtmlAttributeString(['accepted']));
    }

    public function testHtmlDeclinedRule()
    {
        $this->assertEquals('pattern="(no|off|0|false)"', (new HtmlValidator)->toHtmlAttributeString(['declined']));
    }

    public function testHtmlAlphaRule()
    {
        $this->assertEquals('pattern="[a-zA-Z]+"', (new HtmlValidator)->toHtmlAttributeString(['alpha']));
    }

    public function testHtmlAlphaDashRule()
    {
        $this->assertEquals('pattern="[a-zA-Z0-9_-]+"', (new HtmlValidator)->toHtmlAttributeString(['alpha-dash']));
    }

    public function testHtmlAlphaNumRule()
    {
        $this->assertEquals('pattern="[a-zA-Z0-9]+"', (new HtmlValidator)->toHtmlAttributeString(['alpha-num']));
    }

    public function testHtmlBooleanRule()
    {
        $this->assertEquals('pattern="(0|1|true|false)"', (new HtmlValidator)->toHtmlAttributeString(['boolean']));
    }

    public function testHtmlDecimalRule()
    {
        $this->assertEquals('type="number" pattern="[+-]?\d*.(\d*){3}"', (new HtmlValidator)->toHtmlAttributeString(['decimal:3']));
        $this->assertEquals('type="number" pattern="[+-]?\d*.(\d*){3,5}"', (new HtmlValidator)->toHtmlAttributeString(['decimal:3,5']));
    }

    public function testHtmlDigitsRule()
    {
        $this->assertEquals('type="number" pattern="[^0-9]{3}"', (new HtmlValidator)->toHtmlAttributeString(['digits:3']));
    }

    public function testHtmlDigitsBetweenRule()
    {
        $this->assertEquals('type="number" pattern="[^0-9]{3,5}"', (new HtmlValidator)->toHtmlAttributeString(['digits-between:3,5']));
    }

    public function testHtmlEmailRule()
    {
        $this->assertEquals('type="email"', (new HtmlValidator)->toHtmlAttributeString(['email']));
    }

    public function testHtmlFileRule()
    {
        $this->assertEquals('type="file"', (new HtmlValidator)->toHtmlAttributeString(['file']));
    }

    public function testHtmlFilledRule()
    {
        $this->assertEquals('required="required"', (new HtmlValidator)->toHtmlAttributeString(['filled']));
    }

    public function testHtmlInRule()
    {
        $this->assertEquals('pattern="(one|two|three)"', (new HtmlValidator)->toHtmlAttributeString(['in:one,two,three']));
    }

    public function testHtmlIntegerRule()
    {
        $this->assertEquals('type="number" pattern="[+-]?\d+"', (new HtmlValidator)->toHtmlAttributeString(['integer']));
    }

    public function testHtmlIpv4Rule()
    {
    //     $this->assertEquals('', (new HtmlValidator)->toHtmlAttributeString(['ipv4']));
    }

    public function testHtmlIpv6Rule()
    {
    //     $this->assertEquals('', (new HtmlValidator)->toHtmlAttributeString(['ipv6']));
    }

    public function testHtmlMacAddressRule()
    {
    //     $this->assertEquals('', (new HtmlValidator)->toHtmlAttributeString(['mac-address']));
    }

    public function testHtmlMaxRule()
    {
        $this->assertEquals('max="3"', (new HtmlValidator)->toHtmlAttributeString(['max:3']));
    }

    public function testHtmlMaxDigitsRule()
    {
        $this->assertEquals('type="number" pattern="[^0-9]{1,3}"', (new HtmlValidator)->toHtmlAttributeString(['max-digits:3']));
    }

    public function testHtmlMimesRule()
    {
        $this->assertEquals('type="file" accept="image/jpeg"', (new HtmlValidator)->toHtmlAttributeString(['mimes:image/jpeg']));
    }

    public function testHtmlMinRule()
    {
        $this->assertEquals('min="3"', (new HtmlValidator)->toHtmlAttributeString(['min:3']));
    }

    public function testHtmlMinDigitsRule()
    {
        $this->assertEquals('type="number" pattern="[^0-9]{3,}"', (new HtmlValidator)->toHtmlAttributeString(['min-digits:3']));
    }

    public function testHtmlNumericRule()
    {
        $this->assertEquals('type="number"', (new HtmlValidator)->toHtmlAttributeString(['numeric']));
    }

    public function testHtmlPresentRule()
    {
        $this->assertEquals('required="required"', (new HtmlValidator)->toHtmlAttributeString(['present']));
    }

    public function testHtmlRegexRule()
    {
        $this->assertEquals('pattern="example"', (new HtmlValidator)->toHtmlAttributeString(['regex:example']));
    }

    public function testHtmlRequiredRule()
    {
        $this->assertEquals('required="required"', (new HtmlValidator)->toHtmlAttributeString(['required']));
    }

    public function testHtmlStartsWithRule()
    {
        $this->assertEquals('pattern="foo.*"', (new HtmlValidator)->toHtmlAttributeString(['starts-with:foo']));
    }

    public function testHtmlDoesntStartWithRule()
    {
        $this->assertEquals('pattern="^(?!foo)"', (new HtmlValidator)->toHtmlAttributeString(['doesnt-start-with:foo']));
    }

    public function testHtmlEndsWithRule()
    {
        $this->assertEquals('pattern="foo$"', (new HtmlValidator)->toHtmlAttributeString(['ends-with:foo']));
    }

    public function testHtmlDoesntEndWithRule()
    {
        $this->assertEquals('pattern="(?!foo)$"', (new HtmlValidator)->toHtmlAttributeString(['doesnt-end-with:foo']));
    }

    public function testHtmlStringRule()
    {
        $this->assertEquals('type="text"', (new HtmlValidator)->toHtmlAttributeString(['string']));
    }

    public function testHtmlUuidRule()
    {
        // $this->assertEquals('', (new HtmlValidator)->toHtmlAttributeString(['uuid']));
    }
}

class FakeValidationRule implements Rule, HtmlValidationRule
{
    public function passes($attribute, $value)
    {
        return true;
    }

    public function message()
    {
        return 'Fake validation rule.';
    }

    public function toHtmlAttributes(): array
    {
        return ['foo' => 'bar'];
    }
}