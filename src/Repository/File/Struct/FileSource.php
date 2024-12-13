<?php

namespace Gzhegow\I18n\Repository\File\Struct;

use Gzhegow\Lib\Lib;
use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Exception\LogicException;


class FileSource implements FileSourceInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $realpath;

    /**
     * @var string
     */
    protected $lang;
    /**
     * @var string
     */
    protected $group;


    private function __construct()
    {
    }


    public static function from($from) : self
    {
        $instance = static::tryFrom($from);

        return $instance;
    }

    public static function tryFrom($from, \Throwable &$last = null) : ?self
    {
        $last = null;

        Lib::php_errors_start($b);

        $instance = null
            ?? static::tryFromInstance($from)
            ?? static::tryFromArray($from);

        $errors = Lib::php_errors_end($b);

        if (null === $instance) {
            foreach ( $errors as $error ) {
                $last = new LogicException($error, null, $last);
            }
        }

        return $instance;
    }


    public static function tryFromInstance($from) : ?self
    {
        if (! is_a($from, static::class)) {
            return Lib::php_error(
                [
                    'The `from` should be instance of: ' . static::class,
                    $from,
                ]
            );
        }

        return $from;
    }

    public static function tryFromArray($from) : ?self
    {
        if (! is_array($from)) {
            return Lib::php_error(
                [
                    'The `from` should be array',
                    $from,
                ]
            );
        }

        $path = $from[ 'path' ];
        $lang = $from[ 'lang' ];
        $group = $from[ 'group' ];

        if (null === ($_path = Lib::parse_path($path))) {
            return null;
        }

        $lang = Type::theLang($lang);
        $group = Type::theGroup($group);

        $realpath = null;
        if (is_file($_path)) {
            $realpath = realpath($_path);
        }

        $langString = $lang->getValue();
        $groupString = $group->getValue();

        $instance = new static();

        $instance->value = $_path;

        $instance->lang = $langString;
        $instance->group = $groupString;

        $instance->realpath = $realpath;

        return $instance;
    }


    public function getValue() : string
    {
        return $this->value;
    }


    public function getLang() : string
    {
        return $this->lang;
    }

    public function getGroup() : string
    {
        return $this->group;
    }


    public function hasRealpath() : ?string
    {
        return $this->realpath;
    }

    public function getRealpath() : string
    {
        return $this->realpath;
    }
}