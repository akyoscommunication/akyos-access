<?php

namespace Akyos\Access\View\Blocks;

use Akyos\Access\Acf\Fields\ButtonAccess;
use Akyos\Access\Acf\Fields\TitleAccess;
use Akyos\Core\Classes\Block;
use Akyos\Core\Classes\GutenbergBlock;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Textarea;

class HeroAccess extends Block
{
	protected static function block(): GutenbergBlock
	{
		return (new GutenbergBlock())
			->setTitle('Entête de la page - Access')
			->setName('hero-access')
			->setDescription('Entête de la page')
			->setCategory('header');
	}

	protected static function fields(): array
	{
		return [
			Tab::make('Contenu'),
			TitleAccess::make('Titre', 'title'),
			Textarea::make('Description', 'description')->newLines('br'),
			ButtonAccess::make('Bouton', 'button'),

			Tab::make('Image'),
			Image::make('Image', 'image_background')->format('id'),
		];
	}

	public function render()
	{
		return view('blocks.hero-access');
	}
}
