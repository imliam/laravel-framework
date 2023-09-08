<?php

namespace Illuminate\Validation;

use Illuminate\View\ComponentAttributeBag;

class HtmlValidator
{
    /**
     * Converts an array of Laravel validation rules to an associative array of HTML input attributes.
     */
    public function toHtmlAttributes(array $rules): array
    {
        $htmlRules = [];

        foreach ($rules as $rawRule) {
            [$rule, $parameters] = ValidationRuleParser::parse($rawRule);

            if ($rule instanceof HtmlValidationRule) {
                $htmlRules[] = $rule->toHtmlAttributes($parameters);

                continue;
            }

            $htmlRules[] = match(true) {
                $rule === 'Accepted' => ['pattern' => '(yes|on|1|true)'],
                $rule === 'Declined' => ['pattern' => '(no|off|0|false)'],
                $rule === 'Alpha' => ['pattern' => '[a-zA-Z]+'],
                $rule === 'AlphaDash' => ['pattern' => '[a-zA-Z0-9_-]+'],
                $rule === 'AlphaNum' => ['pattern' => '[a-zA-Z0-9]+'],
                $rule === 'Boolean' => ['pattern' => '(0|1|true|false)'],
                $rule === 'Decimal' && count($parameters) === 1 => ['pattern' => '[+-]?\d*.(\d*){' . $parameters[0] . '}'],
                $rule === 'Decimal' && count($parameters) === 2 => ['pattern' => '[+-]?\d*.(\d*){' . $parameters[0] . ',' . $parameters[1] . '}'],
                $rule === 'Digits' && count($parameters) === 1 => ['pattern' => '[^0-9]{' . $parameters[0] . '}'],
                $rule === 'DigitsBetween' && count($parameters) === 2 => ['pattern' => '[^0-9]{' . $parameters[0] . ',' . $parameters[1] . '}'],
                $rule === 'Email' => ['type' => 'email'],
                $rule === 'File' => ['type' => 'file'],
                $rule === 'Filled' => ['required' => true],
                $rule === 'In' && count($parameters) === count(array_map(fn ($param) => is_string($param) || is_numeric($param), $parameters)) => ['pattern' => '(' . implode('|', array_map(preg_quote(...), $parameters)) . ')'],
                $rule === 'Integer' => ['pattern' => '[+-]?\d+'],
                $rule === 'Ip' => ['pattern' => '((?:[0-9]{1,3}\.){3}[0-9]{1,3}|(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4})'],
                $rule === 'Ipv4' => ['pattern' => '(?:[0-9]{1,3}\.){3}[0-9]{1,3}'],
                $rule === 'Ipv6' => ['pattern' => '(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}'],
                $rule === 'MacAddress' => ['pattern' => '([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})'],
                $rule === 'Max' && count($parameters) === 1 => ['max' => $parameters[0]],
                $rule === 'MaxDigits' && count($parameters) === 1 => ['pattern' => '[^0-9]{1,' . $parameters[0] . '}'],
                $rule === 'Mimes' => ['type' => 'file', 'accept' => implode(',', $parameters)],
                $rule === 'Min' && count($parameters) === 1 => ['min' => $parameters[0]],
                $rule === 'MinDigits' && count($parameters) === 1 => ['pattern' => '[^0-9]{' . $parameters[0] . ',}'],
                $rule === 'Numeric' => ['pattern' => '[+-]?\d*[.(\d*)]?'],
                $rule === 'Present' => ['required' => true],
                $rule === 'Regex' && count($parameters) === 1 => ['pattern' => $parameters[0]],
                $rule === 'Required' => ['required' => true],
                $rule === 'StartsWith' && count($parameters) === 1 => ['pattern' => static::convertRegexToHtmlRegex('/^' . preg_quote($parameters[0]) . '/')],
                $rule === 'DoesntStartWith' && count($parameters) === 1 => ['pattern' => '(?!' . preg_quote($parameters[0]) . '.*)'],
                $rule === 'EndsWith' && count($parameters) === 1 => ['pattern' => '.*' . preg_quote($parameters[0])],
                $rule === 'DoesntEndWith' && count($parameters) === 1 => ['pattern' => '(?!.*' . preg_quote($parameters[0]) . ')'],
                $rule === 'Uuid' => ['pattern' => '[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}'],
                default => [],
            };
        }

        return $this->combineHtmlAttributes($htmlRules);
    }

    /**
     * Converts an array of Laravel validation rules to a string of HTML input attributes.
     */
    public function toHtmlAttributeString(array $rules): string
    {
        return (string) new ComponentAttributeBag($this->toHtmlAttributes($rules));
    }

    protected function combineHtmlAttributes(array $rules): array
    {
        $combinedRules = [];

        foreach ($rules as $rule) {
            foreach ($rule as $key => $value) {
                if (isset($combinedRules[$key])) {
                    $combinedRules[$key] = array_merge($combinedRules[$key], $value);
                } else {
                    $combinedRules[$key] = $value;
                }
            }
        }

        foreach ($combinedRules as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            if ($key === 'pattern') {
                $combinedRules[$key] = implode('', array_map(fn ($pattern) => '(?=' . $pattern . ')', $value));

                continue;
            }

            if ($key === 'title') {
                $combinedRules[$key] = implode(' ', $value);

                continue;
            }

            if (count(array_count_values($value)) > 1) {
                unset($combinedRules[$key]);

                continue;
            }

            $combinedRules[$key] = implode(' ', $value);
        }

        return $combinedRules;
    }

    /**
     * Converts the htmlPattern to a suitable format for HTML5 pattern.
     * Example: /^[a-z]+$/ would be converted to [a-z]+
     *
     * @see http://dev.w3.org/html5/spec/single-page.html#the-pattern-attribute
     * @see https://github.com/symfony/validator/blob/0f74ad1cab71f5899dca8233db774a50f8656de0/Constraints/Regex.php#L88
     */
    protected function convertRegexToHtmlRegex(string $pattern): ?string
    {
        // Quit if delimiters not at very beginning/end (e.g. when options are passed)
        if ($pattern[0] !== $pattern[\strlen($pattern) - 1]) {
            return null;
        }

        $delimiter = $pattern[0];

        // Unescape the delimiter
        $pattern = str_replace('\\'.$delimiter, $delimiter, substr($pattern, 1, -1));

        // If the pattern contains an or statement, wrap the pattern in
        // .*(pattern).* and quit. Otherwise we'd need to parse the pattern
        if (str_contains($pattern, '|')) {
            return '.*('.$pattern.').*';
        }

        // Trim leading ^, otherwise prepend .*
        $pattern = '^' === $pattern[0] ? substr($pattern, 1) : '.*'.$pattern;

        // Trim trailing $, otherwise append .*
        $pattern = '$' === $pattern[\strlen($pattern) - 1] ? substr($pattern, 0, -1) : $pattern.'.*';

        return $pattern;
    }
}
