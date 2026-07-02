<?php

namespace Akyos\Access\Acf\Fields;

use Extended\ACF\Fields\File;
use Extended\ACF\Fields\FlexibleContent;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Layout;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\URL;

class MediaAccess
{
    public static function make(string $label, string $id): FlexibleContent
    {
        return FlexibleContent::make($label, $id)
            ->maxLayouts(1)
            ->button('Choisir un type de média')
            ->layouts([
                Layout::make('Image', 'image')->fields([
                    Image::make('Image', 'file')->format('id'),
                ]),
                Layout::make('Vidéo', 'video')->fields([
                    File::make('Vidéo', 'file')->format('id')->acceptedFileTypes(['mp4', 'webm', 'ogg', 'mov']),
                ]),
                Layout::make('YouTube', 'youtube')->fields([
                    URL::make('URL YouTube', 'url'),
                ]),
            ]);
    }

    public static function repeater(string $label, string $id, int $max = 0): Repeater
    {
        $field = Repeater::make($label, $id)
            ->fields([
                self::make('Média', 'media'),
            ])
            ->layout('block');

        if ($max > 0) {
            $field->maxRows($max);
        }

        return $field;
    }
}
