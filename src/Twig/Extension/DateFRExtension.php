<?php

namespace App\Twig\Extension;

use Twig\TwigFilter;
use DateTimeInterface;
use IntlDateFormatter;
use Twig\TwigFunction;
use App\Twig\Runtime\DateFRRuntime;
use Twig\Extension\AbstractExtension;

class DateFRExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('format_date_fr', [$this, 'formatDateFr']),
            new TwigFilter('format_heure_fr', [$this, 'formatHeureFr']),
        ];
    }

    public function formatDateFr(DateTimeInterface $date): string
    {
        $formatter = new IntlDateFormatter(
            'fr_FR', // Locale
            IntlDateFormatter::FULL, // Date format (FULL, LONG, MEDIUM, SHORT)
            IntlDateFormatter::NONE // Time format
        );
        $formatter->setPattern('d MMMM yyyy'); // Modèle de formatage personnalisé
        return $formatter->format($date);
    }

    public function formatHeureFr(\DateTimeInterface $time, string $timezone = 'Africa/Abidjan'): string
    {
        $timeInTimezone = (clone $time)->setTimezone(new \DateTimeZone($timezone));
        return $timeInTimezone->format('H:i'); // Format 24 heures
    }
}
