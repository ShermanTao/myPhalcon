<?php


namespace Sherman\Lib\Validators;

use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation;
use Phalcon\Validation\ValidatorInterface;

/**
 * Class DateCompareValidator
 * @package Sherman\Lib\Validators
 */
class DateCompareValidator extends Validator implements ValidatorInterface
{

    public function validate(Validation $validator, $attribute)
    {
        /*$validator->add('start_date', new DateCompareValidator([
            'compare' => $this->end_date,
            'message' => '开始时间不得早于或等于结束时间',
            'difference' => 3600,
        ]));;*/

        $startDate = $validator->getValue($attribute);
        $endDate = $this->getOption('compare');
        $difference = $this->getOption('difference');

        $flag = true;
        if (isset($difference) && intval($difference) > 0) {
            //两个日期的间隔不得大于$difference秒
            $startDate = strtotime($startDate);
            $endDate = strtotime($endDate);
            $flag = ($endDate - $startDate) >= $difference;
        } else {
            $flag = $startDate >= $endDate;
        }

        if ($flag) {
            $message = $this->getOption('message');
            if (!$message) {
                $message = '开始时间不得早于或等于结束时间';
            }

            $validator->appendMessage(new Message($message, $attribute, 'DateCompare'));
            return false;
        }

        return true;
    }
}