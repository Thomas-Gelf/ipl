<?php

namespace ipl\Translation;

trait TranslationHelper
{
    /** @var TranslatorInterface */
    private static $translator;

    /**
     * @param $string
     * @return string
     */
    public function translate($string)
    {
        if (self::$translator === null) {
            static::setNoTranslator();
        }

        return self::$translator->translate($string);
    }

    public static function setNoTranslator()
    {
        static::setTranslator(new NoTranslator());
    }

    /**
     * @param TranslatorInterface $translator
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        self::$translator = $translator;
    }
}
