<?php

namespace Gzhegow\I18n\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\I18nType;
use Gzhegow\I18n\Exception\LogicException;


class Word implements WordInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $group;


    private function __construct()
    {
    }


    /**
     * @return static
     */
    public static function from($from) // : static
    {
        $instance = static::tryFrom($from, $error);

        if (null === $instance) {
            throw $error;
        }

        return $instance;
    }

    /**
     * @return static|null
     */
    public static function tryFrom($from, \Throwable &$last = null) // : ?static
    {
        $last = null;

        Lib::php()->errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromString($from);

        $errors = Lib::php()->errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, $last);
            }
        }

        return $instance;
    }


    /**
     * @return static|null
     */
    public static function tryFromInstance($from) // : ?static
    {
        if (! is_a($from, static::class)) {
            return Lib::php()->error(
                [ 'The `from` should be instance of: ' . static::class, $from ]
            );
        }

        return $from;
    }

    /**
     * @return static|null
     */
    public static function tryFromString($from) // : ?static
    {
        if (null === ($string = Lib::parse()->string_not_empty($from))) {
            return Lib::php()->error(
                [ 'The `from` should be non-empty string', $from ]
            );
        }

        $regexPart = '[a-z][a-z0-9_-]*[a-z0-9]';

        if (! preg_match($regex = "/^{$regexPart}[.]{$regexPart}[.]{$regexPart}([_][\$]*)?$/", $string)) {
            return Lib::php()->error(
                [ 'The `from` should be string that match regex: ' . $regex, $from ]
            );
        }

        [ $group ] = explode('.', $string, 2);

        if (null === ($group = I18nType::parseGroup($group))) {
            return Lib::php()->error(
                [ 'The `from` should contain valid group', $from ]
            );
        }

        $groupString = $group->getValue();

        $instance = new static();

        $instance->value = $string;

        $instance->group = $groupString;

        return $instance;
    }


    public function __toString()
    {
        return $this->value;
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getGroup() : string
    {
        return $this->group;
    }
}
