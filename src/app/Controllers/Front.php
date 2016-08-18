<?php

namespace ResponsiveMenu\Controllers;
use ResponsiveMenu\View\View;
use ResponsiveMenu\Services\OptionService;
use ResponsiveMenu\ViewModels\Menu as MenuViewModel;
use ResponsiveMenu\ViewModels\Button as ButtonViewModel;
use ResponsiveMenu\Factories\FrontDisplayFactory as DisplayFactory;
use ResponsiveMenu\ViewModels\Components\Button\Button as ButtonComponent;
use ResponsiveMenu\ViewModels\Components\ComponentFactory;

class Front  {

  public function __construct(OptionService $service, View $view) {
    $this->service = $service;
    $this->view = $view;
  }

	public function index() {
    # Get Latest Options
    $options = $this->service->all();

    # This needs refactoring - Martin Fowler HELP!
    $display_factory = new DisplayFactory();
    $display_factory->build($options);

    # Build Our Menu Display
    $menu = new MenuViewModel($options, new ComponentFactory);
    $html = new ButtonViewModel($options, new ButtonComponent);

    # Only render if we don't have shortcodes turned on
    if($options['shortcode'] == 'off'):
      $this->view->render('button', ['options' => $options, 'button' => $html->getHtml()]);
      $this->view->render('menu', ['options' => $options, 'menu' => $menu->getHtml()]);
    else:
      add_shortcode('responsive_menu', function($atts) use($options, $html, $menu) {

        if($atts)
          array_walk($atts, function($a, $b) use ($options) { $options[$b] = $a; });

        $html = $this->view->make('button', ['options' => $options, 'button' => $html->getHtml()]);

        return $html . $this->view->make('menu', ['options' => $options, 'menu' => $menu->getHtml()]);

      });
    endif;

	}

  public function preview() {
    return $this->view->render('preview');
  }

}
