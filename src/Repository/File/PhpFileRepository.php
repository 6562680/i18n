<?php

namespace Gzhegow\I18n\Repository\File;

use Gzhegow\I18n\Type\Type;
use Gzhegow\I18n\Pool\PoolItemInterface;
use Gzhegow\I18n\Exception\RuntimeException;
use Gzhegow\I18n\Repository\File\Struct\FileSourceInterface;


class PhpFileRepository extends AbstractFileRepository
{
    public function buildFileSource(string $lang, string $group) : FileSourceInterface
    {
        $_lang = Type::theLang($lang);
        $_group = Type::theGroup($group);

        $path = $this->langDir . '/' . $_lang . '/' . $_group . '.php';

        $fileSource = Type::theFileSource([
            'path'  => $path,
            //
            'lang'  => $_lang,
            'group' => $_group,
        ]);

        return $fileSource;
    }


    /**
     * @return array<string, PoolItemInterface>
     */
    public function loadItemsFromFile(FileSourceInterface $fileSource) : array
    {
        $poolItems = [];

        $fileSourceLang = $fileSource->getLang();
        $fileSourceGroup = $fileSource->getGroup();
        $fileSourceRealpath = $fileSource->getRealpath();

        $choicesArray = require $fileSourceRealpath;

        if (! is_array($choicesArray)) {
            throw new RuntimeException(
                [
                    'Invalid PHP array in file: ' . $fileSourceRealpath,
                    $choicesArray,
                ]
            );
        }

        foreach ( $choicesArray as $word => $poolItemChoices ) {
            $poolItemWord = Type::theWord($word);

            $poolItemPhrase = $poolItemChoices[ 0 ];

            $poolItemGroup = $poolItemWord->getGroup();

            if ($poolItemGroup !== $fileSourceGroup) {
                throw new RuntimeException(
                    'Stored `word` has group that is not match with `poolItem` group: '
                    . $poolItemGroup
                    . ' / ' . $fileSourceGroup
                );
            }

            $poolItem = Type::thePoolItem([
                'word'    => $poolItemWord,
                //
                'lang'    => $fileSourceLang,
                //
                'phrase'  => $poolItemPhrase,
                'choices' => $poolItemChoices,
            ]);

            $poolItems[ $word ] = $poolItem;
        }

        return $poolItems;
    }


    /**
     * @param FileSourceInterface     $fileSource
     * @param array<string, string[]> $choicesArray
     *
     * @return bool
     */
    public static function saveChoicesArrayToFile(FileSourceInterface $fileSource, array $choicesArray) : bool
    {
        $fileSourcePath = $fileSource->getValue();

        $content = implode(PHP_EOL, [
            '<?php /* This file is autogenerated. */',
            'return ' . var_export($choicesArray, 1) . ';',
        ]);

        $status = file_put_contents($fileSourcePath, $content);

        return $status;
    }
}
