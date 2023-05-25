<?php

namespace Application\ViewHelper;

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\AbstractHelper;

class DateFormat extends AbstractHelper
{
    const FORMAT_ISO_DATE = 'Y-m-d';
    const FORMAT_ISO_DATETIME = 'Y-m-d H:i:s';
    const FORMAT_TO = 'd.m.Y';
    const FORMAT_FROM = 'Y-m-d';

    public function __invoke() : DateFormat
    {
       return $this;
    }
    public function fromString(string $date, $fromFormat=self::FORMAT_FROM, $toFormat=self::FORMAT_TO) {
        $d = \DateTime::createFromFormat($fromFormat, $date);
        if ($d) {
            return $d->format($toFormat);
        }
        return $date;
    }

    public function fromDate(\DateTimeInterface $date, $toFormat=self::FORMAT_TO) {
        return $date->format($toFormat);
    }
}
