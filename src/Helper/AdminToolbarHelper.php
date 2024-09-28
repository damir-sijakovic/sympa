<?php

namespace App\Helper;
use Twig\Environment;

class AdminToolbarHelper
{
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

	public function getUi($id): String
    {
		return $this->twig->render('admin-toolbar/main.html.twig', [
			"id" => $id,
			"hello" => "world"			
		]);
    }
	

};
