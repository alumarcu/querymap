<?php

/*
 * The MIT License (MIT)
 * Copyright (c) 2016 Alexandru Marcu <alumarcu@gmail.com>/DMS Team @ eMAG IT Research
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace QueryMap\Contrib\MappingHelper;

use QueryMap\Exception\QueryMapException;

class CommonMappingHelper extends MappingHelper
{
    /**
     * WARNING! Changing these constant values will affect the syntax
     * of YML (or other external) files if you read from them.
     */

    /** Validations */
    const VALID_NOT_EMPTY = 'not-empty';
    const VALID_DATETIME_STRING = 'datetime-valid';

    /** Processing Actions (Transformations) */
    const TYPE_BOOL = 'bool';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_STRING = 'string';

    const TRANSFORM_TRIM = 'trim';
    const TRANSFORM_BIN_CHOICE = 'binary-choice';
    const TRANSFORM_NEGATIVE_TO_NULL = 'negative-to-null';
    const TRANSFORM_DATETIME = 'datetime-format';

/** Processing Params */
    // Given a date without time, set to time-min (00:00) or time-max (23:59)
    const SET_TIME_MIN = 'time-min';
    const SET_TIME_MAX = 'time-max';

    /**
     * Implements the default set of validations and
     * should also provide the hook for subclasses to extend this.
     *
     * @param $value        mixed         The value which to validate
     * @param $validation   mixed         The validation to be performed
     * @param $params       array|mixed   Parameters to use in a validation
     *
     * @return mixed
     */
    protected function performValidation($value, $validation, $params = [])
    {
        switch ($validation) {
            case static::VALID_NOT_EMPTY:
                return !empty($value);
            case static::VALID_DATETIME_STRING:
                return false !== strtotime($value);
        }

        // Throw error if an unhandled validation type was sent
        throw new QueryMapException("Unimplemented validation type: '{$validation}'");
    }

    /**
     * Implements the default set of processing actions and
     * should also provide the hook for subclasses to extend this.
     *
     * @param $value    mixed           The value which to process
     * @param $action   mixed           The processing action to be performed
     * @param $params  array|mixed      Parameters to use in a concrete action
     *
     * @return mixed
     */
    protected function processAction($value, $action, $params = [])
    {
        switch ($action) {
            case static::TYPE_BOOL:
            case static::TYPE_INT:
            case static::TYPE_FLOAT:
            case static::TYPE_STRING:
                settype($value, $action);

                return $value;
            /* ====================================================== */
            case static::TRANSFORM_BIN_CHOICE:
                $value = ($value > 0) ? true : false;
                if (!empty($params) && 2 === count($params)) {
                    $value = $value ? $params[0] : $params[1];
                }

                return $value;
            /* ====================================================== */
            case static::TRANSFORM_NEGATIVE_TO_NULL:
                $value = ($value < 0) ? null : $value;

                return $value;
            /* ====================================================== */
            case static::TRANSFORM_TRIM:
                $charList = " \t\n\r\0\x0B";
                if (!empty($params[0])) {
                    $charList .= $params[0];
                }

                return trim($value, $charList);
            /* ====================================================== */
            case static::TRANSFORM_DATETIME:
                $datetime = new \DateTime($value);
                if (static::SET_TIME_MIN === $params[0]) {
                    $datetime->setTime(0, 0, 0);
                } elseif (static::SET_TIME_MAX === $params[0]) {
                    $datetime->setTime(23, 59, 59);
                }

                return $datetime->format('Y-m-d H:i:s');
        }

        // Throw error if an unhandled transformation type was sent
        throw new QueryMapException("Unimplemented transformation type: '{$action}'");
    }
}
