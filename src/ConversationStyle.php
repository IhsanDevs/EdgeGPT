<?php

namespace Ihsandevs\EdgeGpt;

class ConversationStyle
{
    const CREATIVE = "h3relaxedimg";
    const BALANCED = "galileo";
    const PRECISE = "h3precise";

    public static function CREATIVE()
    {
        return new ConversationStyle(self::CREATIVE);
    }

    public static function BALANCED()
    {
        return new ConversationStyle(self::BALANCED);
    }

    public static function PRECISE()
    {
        return new ConversationStyle(self::PRECISE);
    }

    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}