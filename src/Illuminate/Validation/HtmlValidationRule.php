<?php

namespace Illuminate\Validation;

interface HtmlValidationRule
{
    /**
     * Convert the rule to an array of HTML attributes that can be applied
     * to a form field in order to provide frontend validation.
     *
     * @return array
     */
    public function toHtmlAttributes(): array;
}
