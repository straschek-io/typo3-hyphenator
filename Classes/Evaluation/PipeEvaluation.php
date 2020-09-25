<?php

declare(strict_types=1);
namespace StraschekIo\Hyphenator\Evaluation;

/**
 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/inputDefault.html?highlight=eval#eval
 */
class PipeEvaluation
{
    /**
     * @return string JavaScript code for client side validation/evaluation
     */
    public function returnFieldJS()
    {
        return 'return value;';
    }

    /**
     * @param string $value The field value to be evaluated
     * @param string $is_in The "is_in" value of the field configuration from TCA
     * @param bool $set boolean defining if the value is written to the database or not
     * @return string Evaluated field value
     */
    public function evaluateFieldValue($value, $is_in, &$set)
    {
        return preg_replace('/[^a-zA-Z0-9_-| ]/', '', $value);
    }

    /**
     * @param array $parameters Array with key 'value' containing the field value from the database
     * @return string Evaluated field value
     */
    public function deevaluateFieldValue(array $parameters)
    {
        return $parameters['value'];
    }
}
