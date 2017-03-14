<?php


namespace Sherman\Lib\Validators;

use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation;
use Phalcon\Validation\ValidatorInterface;

/**
 * Class NumberCompareValidator
 * @package Sherman\Lib\Validators
 */
class NumberCompareValidator extends Validator implements ValidatorInterface
{

    public function validate(Validation $validator, $attribute)
    {
        /*$validator->add('number1', new DateCompareValidator([
            'compare' => $this->number2,
            'message' => 'number1不得大于或等于number2',
            'difference' => 90,
        ]));;*/

        $number1 = $validator->getValue($attribute);
        $compareNum = $this->getOption('compare');
        $difference = $this->getOption('difference');
        $difference = isset($difference) ? $difference : 0;
        $order = $this->getOption('order');
        $order = isset($order) ? $order : 'egt';

        if ($order == 'lt') {
            if ($number1 - $compareNum < $difference) {
                $message = $this->getOption('message');
                if (!$message) {
                    $message = 'number1不得小于compareNum';
                }

                $validator->appendMessage(new Message($message, $attribute, 'NumberCompare'));
                return false;
            }
        } elseif ($order == 'egt') {
            if ($number1 - $compareNum >= $difference) {
                $message = $this->getOption('message');
                if (!$message) {
                    $message = 'number1不得大于或等于compareNum';
                }

                $validator->appendMessage(new Message($message, $attribute, 'NumberCompare'));
                return false;
            }
        }

        return true;
    }
}