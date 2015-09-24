<?php

namespace VentureOakLabs\FaviconGeneratorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('VentureOakLabsFaviconGeneratorBundle:Default:index.html.twig', array('name' => $name));
    }
}
